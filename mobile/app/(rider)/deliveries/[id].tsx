import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import * as Location from 'expo-location';
import { useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { Linking, StyleSheet, View } from 'react-native';

import { normalizeApiError } from '@/src/api/api-error';
import {
  acceptRiderDelivery,
  getRiderDelivery,
  markRiderDeliveryDelivered,
  markRiderDeliveryOutForDelivery,
  markRiderDeliveryPickedUp,
  updateRiderLocation,
  updateRiderDeliveryStatus,
} from '@/src/api/deliveries.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ConfirmModal } from '@/src/components/common/ConfirmModal';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { AppInput } from '@/src/components/forms/AppInput';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderTrackingCard, shouldPollOrderTracking } from '@/src/components/tracking/OrderTrackingCard';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { Order, OrderItem } from '@/src/types/order';
import { formatCurrency } from '@/src/utils/currency';
import { formatDateTime } from '@/src/utils/date';

type RiderAction = {
  status: 'accepted' | 'picked_up' | 'out_for_delivery' | 'delivered' | 'failed';
  label: string;
  destructive?: boolean;
};

export default function RiderDeliveryDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const queryClient = useQueryClient();
  const [pendingAction, setPendingAction] = useState<RiderAction | null>(null);
  const [failureReason, setFailureReason] = useState('');
  const [message, setMessage] = useState<{ tone: 'success' | 'error'; text: string } | null>(null);

  const deliveryQuery = useQuery({
    queryKey: queryKeys.riderDelivery(id),
    queryFn: () => getRiderDelivery(id),
    enabled: Boolean(id),
    refetchInterval: (liveQuery) => (shouldPollOrderTracking(liveQuery.state.data) ? 8000 : false),
    refetchIntervalInBackground: true,
  });
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  const refreshDeliveryLists = async (order: Order) => {
    queryClient.setQueryData(queryKeys.riderDelivery(id), order);
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: queryKeys.riderDeliveries }),
      queryClient.invalidateQueries({ queryKey: queryKeys.riderHistory }),
      queryClient.invalidateQueries({ queryKey: queryKeys.riderDashboard }),
    ]);
  };

  const actionMutation = useMutation({
    mutationFn: async (action: RiderAction) => {
      if (action.status === 'accepted') {
        return acceptRiderDelivery(id);
      }

      if (action.status === 'picked_up') {
        return markRiderDeliveryPickedUp(id);
      }

      if (action.status === 'out_for_delivery') {
        return markRiderDeliveryOutForDelivery(id);
      }

      if (action.status === 'delivered') {
        return markRiderDeliveryDelivered(id);
      }

      return updateRiderDeliveryStatus(id, {
        status: 'failed',
        notes: failureReason.trim(),
      });
    },
    onSuccess: async (order) => {
      setMessage({ tone: 'success', text: 'Delivery updated.' });
      setPendingAction(null);
      setFailureReason('');
      await refreshDeliveryLists(order);
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  const locationMutation = useMutation({
    mutationFn: updateRiderLocation,
    onSuccess: async () => {
      setMessage({ tone: 'success', text: 'Location shared for live customer tracking.' });
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.riderDelivery(id) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.riderDashboard }),
      ]);
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
    },
  });

  const syncLocation = async () => {
    setMessage(null);
    const permission = await Location.requestForegroundPermissionsAsync();

    if (permission.status !== Location.PermissionStatus.GRANTED) {
      setMessage({ tone: 'error', text: 'Location permission is required to share your delivery position.' });
      return;
    }

    const location = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
    locationMutation.mutate({
      latitude: location.coords.latitude,
      longitude: location.coords.longitude,
    });
  };

  if (deliveryQuery.isLoading) {
    return <LoadingScreen label="Loading delivery..." />;
  }

  if (deliveryQuery.isError || !deliveryQuery.data) {
    return <ErrorState message={normalizeApiError(deliveryQuery.error).message} onRetry={() => void deliveryQuery.refetch()} />;
  }

  const order = deliveryQuery.data;
  const restaurant = restaurantQuery.data;
  const deliveryStatus = order.delivery?.status ?? 'assigned';
  const nextAction = getNextRiderAction(deliveryStatus, order.order_status);
  const canFail = !['delivered', 'cancelled'].includes(order.order_status) && !['delivered', 'failed'].includes(deliveryStatus);
  const hasAddress = Boolean(order.delivery_address);
  const mapQuery = order.delivery_latitude && order.delivery_longitude
    ? `${order.delivery_latitude},${order.delivery_longitude}`
    : encodeURIComponent(order.delivery_address ?? '');

  return (
    <AppScreen refreshing={deliveryQuery.isRefetching} onRefresh={() => void deliveryQuery.refetch()} contentStyle={styles.screen}>
      <AppHeader title={order.order_number} subtitle={formatDateTime(order.created_at)} eyebrow="Rider delivery" />

      {message ? <FeedbackMessage tone={message.tone} message={message.text} /> : null}

      <OrderTrackingCard
        order={order}
        mode="rider"
        refreshedAt={deliveryQuery.dataUpdatedAt}
        refreshing={deliveryQuery.isRefetching}
        sharingLocation={locationMutation.isPending}
        onRefresh={() => void deliveryQuery.refetch()}
        onShareLocation={() => void syncLocation()}
      />

      <AppCard style={styles.card}>
        <View style={styles.badges}>
          <StatusBadge status={order.delivery?.status_label ?? deliveryStatus} />
          <StatusBadge status={order.payment_status} />
          <StatusBadge status={order.order_status_label ?? order.order_status} />
        </View>
        <Detail label="Customer" value={order.customer?.name ?? 'Guest customer'} />
        <Detail label="Phone" value={order.customer?.phone ?? 'Not available'} />
        <Detail label="Address" value={order.delivery_address ?? 'Not available'} />
        {order.order_notes ? <Detail label="Delivery notes" value={order.order_notes} /> : null}
        <View style={styles.actionRow}>
          {order.customer?.phone ? (
            <AppButton label="Call customer" variant="outline" onPress={() => void Linking.openURL(`tel:${order.customer?.phone}`)} />
          ) : null}
          {hasAddress ? (
            <AppButton
              label="Open map"
              variant="secondary"
              onPress={() => void Linking.openURL(`https://www.google.com/maps/search/?api=1&query=${mapQuery}`)}
            />
          ) : null}
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Pickup" />
        <Detail label="Restaurant" value={restaurant?.name ?? 'Arcade Kebab House'} />
        <Detail label="Phone" value={restaurant?.phone ?? 'Not available'} />
        <Detail label="Address" value={restaurant?.formatted_address ?? restaurant?.address ?? 'Not available'} />
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Status action" subtitle="Actions follow the restaurant delivery workflow." />
        {nextAction ? (
          <AppButton label={nextAction.label} onPress={() => setPendingAction(nextAction)} />
        ) : (
          <AppText color={colors.text.secondary}>No more status actions are available for this delivery.</AppText>
        )}
        {canFail ? (
          <>
            <AppInput
              label="Failed delivery reason"
              placeholder="Reason required before marking failed"
              value={failureReason}
              onChangeText={setFailureReason}
              multiline
            />
            <AppButton
              label="Mark failed"
              variant="danger"
              disabled={failureReason.trim().length < 5}
              onPress={() => setPendingAction({ status: 'failed', label: 'Mark delivery failed', destructive: true })}
            />
          </>
        ) : null}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Items" subtitle={`${order.items?.length ?? 0} item line${order.items?.length === 1 ? '' : 's'}`} />
        {(order.items ?? []).map((item) => (
          <OrderItemRow key={item.id} item={item} />
        ))}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Totals" />
        <SummaryRow label="Subtotal" amount={order.subtotal ?? 0} />
        <SummaryRow label="Delivery fee" amount={order.delivery_fee ?? 0} />
        <View style={styles.divider} />
        <SummaryRow label="Total" amount={order.total} strong />
      </AppCard>

      <ConfirmModal
        visible={Boolean(pendingAction)}
        title={pendingAction?.label ?? 'Confirm'}
        message={confirmationMessage(pendingAction)}
        confirmLabel={pendingAction?.destructive ? 'Confirm' : 'Continue'}
        destructive={Boolean(pendingAction?.destructive)}
        loading={actionMutation.isPending}
        onCancel={() => setPendingAction(null)}
        onConfirm={() => {
          if (pendingAction) {
            actionMutation.mutate(pendingAction);
          }
        }}
      />
    </AppScreen>
  );
}

function getNextRiderAction(deliveryStatus: string, orderStatus: string): RiderAction | null {
  if (['delivered', 'cancelled'].includes(orderStatus)) {
    return null;
  }

  if (['pending', 'assigned', 'assigned_to_rider'].includes(deliveryStatus)) {
    return { status: 'accepted', label: 'Accept delivery' };
  }

  if (deliveryStatus === 'accepted') {
    return { status: 'picked_up', label: 'Mark picked up' };
  }

  if (deliveryStatus === 'picked_up') {
    return { status: 'out_for_delivery', label: 'Start delivery' };
  }

  if (deliveryStatus === 'out_for_delivery') {
    return { status: 'delivered', label: 'Mark delivered' };
  }

  return null;
}

function confirmationMessage(action: RiderAction | null): string {
  if (!action) {
    return '';
  }

  if (action.status === 'failed') {
    return 'Mark this delivery as failed? The restaurant will see the failure reason.';
  }

  if (action.status === 'delivered') {
    return 'Confirm the order has been delivered to the customer?';
  }

  return 'Update this delivery to the next workflow state?';
}

function Detail({ label, value }: { label: string; value: string }) {
  return (
    <View style={styles.detail}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      <AppText>{value}</AppText>
    </View>
  );
}

function OrderItemRow({ item }: { item: OrderItem }) {
  return (
    <View style={styles.itemRow}>
      <View style={styles.flex}>
        <AppText variant="title">
          {item.quantity} x {item.item_name}
        </AppText>
        {item.size_name ? <AppText color={colors.text.secondary}>{item.size_name}</AppText> : null}
        {item.addons && item.addons.length > 0 ? (
          <AppText color={colors.text.secondary}>{item.addons.map((addon) => addon.name).join(', ')}</AppText>
        ) : null}
        {item.item_notes ? <AppText color={colors.text.secondary}>Note: {item.item_notes}</AppText> : null}
      </View>
      <PriceText amount={item.total} />
    </View>
  );
}

function SummaryRow({ label, amount, strong = false }: { label: string; amount: number; strong?: boolean }) {
  return (
    <View style={styles.summaryRow}>
      <AppText variant={strong ? 'title' : 'body'}>{label}</AppText>
      <AppText variant={strong ? 'title' : 'body'} color={strong ? colors.gold.dark : colors.text.primary}>
        {formatCurrency(amount)}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  actionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  detail: {
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
    gap: spacing.xs,
  },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  summaryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
  divider: {
    height: 1,
    backgroundColor: colors.border.light,
  },
});
