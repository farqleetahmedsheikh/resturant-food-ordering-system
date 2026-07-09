import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { Linking, StyleSheet, View } from 'react-native';

import { getAdminRiders } from '@/src/api/admin.api';
import { normalizeApiError } from '@/src/api/api-error';
import {
  assignAdminOrderRider,
  getAdminOrder,
  unassignAdminOrderRider,
  updateAdminOrderStatus,
} from '@/src/api/orders.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ConfirmModal } from '@/src/components/common/ConfirmModal';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { Order, OrderItem, OrderStatusHistory } from '@/src/types/order';
import type { Rider } from '@/src/types/rider';
import { formatCurrency } from '@/src/utils/currency';
import { formatDateTime } from '@/src/utils/date';

type PendingAction =
  | { type: 'status'; status: string; label: string; destructive?: boolean }
  | { type: 'assign'; rider: Rider; label: string }
  | { type: 'unassign'; label: string; destructive?: boolean };

export default function AdminOrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const queryClient = useQueryClient();
  const [pendingAction, setPendingAction] = useState<PendingAction | null>(null);
  const [message, setMessage] = useState<{ tone: 'success' | 'error'; text: string } | null>(null);

  const orderQuery = useQuery({
    queryKey: queryKeys.adminOrder(id),
    queryFn: () => getAdminOrder(id),
    enabled: Boolean(id),
  });
  const ridersQuery = useQuery({
    queryKey: queryKeys.adminRiders,
    queryFn: getAdminRiders,
  });

  const refreshOrderLists = async (order: Order) => {
    queryClient.setQueryData(queryKeys.adminOrder(id), order);
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: ['admin', 'orders'] }),
      queryClient.invalidateQueries({ queryKey: queryKeys.adminDashboard }),
      queryClient.invalidateQueries({ queryKey: queryKeys.adminRiders }),
    ]);
  };

  const statusMutation = useMutation({
    mutationFn: (status: string) =>
      updateAdminOrderStatus(id, {
        order_status: status,
        reason: status === 'cancelled' ? 'Cancelled from mobile admin' : 'Updated from mobile admin',
      }),
    onSuccess: async (order) => {
      setMessage({ tone: 'success', text: 'Order status updated.' });
      setPendingAction(null);
      await refreshOrderLists(order);
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  const assignMutation = useMutation({
    mutationFn: (rider: Rider) => assignAdminOrderRider(id, { rider_id: rider.id }),
    onSuccess: async (order) => {
      setMessage({ tone: 'success', text: 'Rider assigned.' });
      setPendingAction(null);
      await refreshOrderLists(order);
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  const unassignMutation = useMutation({
    mutationFn: () => unassignAdminOrderRider(id),
    onSuccess: async (order) => {
      setMessage({ tone: 'success', text: 'Rider unassigned.' });
      setPendingAction(null);
      await refreshOrderLists(order);
    },
    onError: (error) => {
      setMessage({ tone: 'error', text: normalizeApiError(error).message });
      setPendingAction(null);
    },
  });

  if (orderQuery.isLoading) {
    return <LoadingScreen label="Loading order..." />;
  }

  if (orderQuery.isError || !orderQuery.data) {
    return <ErrorState message={normalizeApiError(orderQuery.error).message} onRetry={() => void orderQuery.refetch()} />;
  }

  const order = orderQuery.data;
  const riders = (ridersQuery.data ?? []).filter((rider) => rider.is_active);
  const nextAction = getNextAdminAction(order);
  const isBusy = statusMutation.isPending || assignMutation.isPending || unassignMutation.isPending;
  const pendingDestructive = pendingAction ? 'destructive' in pendingAction && Boolean(pendingAction.destructive) : false;

  return (
    <AppScreen refreshing={orderQuery.isRefetching} onRefresh={() => void orderQuery.refetch()} contentStyle={styles.screen}>
      <AppHeader title={order.order_number} subtitle={formatDateTime(order.created_at)} eyebrow="Admin order" />

      {message ? <FeedbackMessage tone={message.tone} message={message.text} /> : null}

      <AppCard style={styles.card}>
        <View style={styles.badges}>
          <StatusBadge status={order.order_status_label ?? order.order_status} />
          <StatusBadge status={order.payment_status} />
          {order.delivery?.status ? <StatusBadge status={order.delivery.status_label ?? order.delivery.status} /> : null}
        </View>
        <Detail label="Customer" value={order.customer?.name ?? 'Guest customer'} />
        <Detail label="Phone" value={order.customer?.phone ?? 'Not available'} />
        <Detail label="Email" value={order.customer?.email ?? 'Not available'} />
        <Detail label="Address" value={order.delivery_address ?? 'Not available'} />
        {order.order_notes ? <Detail label="Customer notes" value={order.order_notes} /> : null}
        <View style={styles.actionRow}>
          {order.customer?.phone ? (
            <AppButton
              label="Call customer"
              variant="outline"
              onPress={() => void Linking.openURL(`tel:${order.customer?.phone}`)}
            />
          ) : null}
          {nextAction ? (
            <AppButton
              label={nextAction.label}
              onPress={() => setPendingAction({ type: 'status', ...nextAction })}
            />
          ) : null}
          {!['delivered', 'cancelled'].includes(order.order_status) ? (
            <AppButton
              label="Cancel order"
              variant="danger"
              onPress={() =>
                setPendingAction({
                  type: 'status',
                  status: 'cancelled',
                  label: 'Cancel order',
                  destructive: true,
                })
              }
            />
          ) : null}
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Rider assignment" />
        <Detail label="Assigned rider" value={order.rider?.name ?? 'No rider assigned'} />
        {order.rider && !['delivered', 'cancelled'].includes(order.order_status) ? (
          <AppButton
            label="Unassign rider"
            variant="outline"
            onPress={() => setPendingAction({ type: 'unassign', label: 'Unassign rider', destructive: true })}
          />
        ) : null}
        {ridersQuery.isError ? (
          <FeedbackMessage tone="error" message="Unable to load active riders." />
        ) : null}
        <View style={styles.riderGrid}>
          {riders.map((rider) => (
            <AppButton
              key={rider.id}
              label={order.rider?.id === rider.id ? `${rider.name} assigned` : rider.name}
              variant={order.rider?.id === rider.id ? 'secondary' : 'outline'}
              disabled={order.rider?.id === rider.id || ['delivered', 'cancelled'].includes(order.order_status)}
              onPress={() => setPendingAction({ type: 'assign', rider, label: `Assign ${rider.name}` })}
            />
          ))}
        </View>
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

      {order.status_history && order.status_history.length > 0 ? (
        <AppCard style={styles.card}>
          <SectionTitle title="Progress" />
          {order.status_history.map((history) => (
            <TimelineRow key={history.id} history={history} />
          ))}
        </AppCard>
      ) : null}

      <ConfirmModal
        visible={Boolean(pendingAction)}
        title={pendingAction?.label ?? 'Confirm'}
        message={confirmationMessage(pendingAction)}
        confirmLabel={pendingDestructive ? 'Confirm' : 'Continue'}
        destructive={pendingDestructive}
        loading={isBusy}
        onCancel={() => setPendingAction(null)}
        onConfirm={() => {
          if (!pendingAction) {
            return;
          }

          if (pendingAction.type === 'status') {
            statusMutation.mutate(pendingAction.status);
          }

          if (pendingAction.type === 'assign') {
            assignMutation.mutate(pendingAction.rider);
          }

          if (pendingAction.type === 'unassign') {
            unassignMutation.mutate();
          }
        }}
      />
    </AppScreen>
  );
}

function getNextAdminAction(order: Order): { status: string; label: string } | null {
  if (order.order_status === 'pending') {
    return { status: 'accepted', label: 'Accept order' };
  }

  if (order.order_status === 'accepted') {
    return { status: 'preparing', label: 'Start preparing' };
  }

  if (order.order_status === 'preparing') {
    return { status: 'ready', label: 'Mark ready' };
  }

  if (order.order_status === 'ready' && order.rider) {
    return { status: 'assigned_to_rider', label: 'Send to rider' };
  }

  if (order.order_status === 'assigned_to_rider') {
    return { status: 'out_for_delivery', label: 'Mark on road' };
  }

  if (order.order_status === 'out_for_delivery') {
    return { status: 'delivered', label: 'Mark delivered' };
  }

  return null;
}

function confirmationMessage(action: PendingAction | null): string {
  if (!action) {
    return '';
  }

  if (action.type === 'assign') {
    return `Assign this order to ${action.rider.name}?`;
  }

  if (action.type === 'unassign') {
    return 'Remove the rider from this order? The order will return to the ready queue.';
  }

  if (action.status === 'cancelled') {
    return 'Cancel this order? This cannot be treated as an active delivery after cancellation.';
  }

  return 'Move this order to the next workflow state?';
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

function TimelineRow({ history }: { history: OrderStatusHistory }) {
  return (
    <View style={styles.timelineRow}>
      <StatusBadge status={history.new_status} />
      <View style={styles.flex}>
        <AppText>{history.reason ?? 'Status updated'}</AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          {formatDateTime(history.created_at)}
        </AppText>
      </View>
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
  riderGrid: {
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
  timelineRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
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
