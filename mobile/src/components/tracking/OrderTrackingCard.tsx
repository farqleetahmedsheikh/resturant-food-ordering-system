import { Linking, StyleSheet, View } from 'react-native';

import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { colors, radius, spacing } from '@/src/theme';
import type { Order } from '@/src/types/order';
import { formatDateTime } from '@/src/utils/date';

type OrderTrackingCardProps = {
  order: Order;
  mode: 'customer' | 'rider';
  refreshedAt?: number;
  refreshing?: boolean;
  sharingLocation?: boolean;
  onRefresh?: () => void;
  onShareLocation?: () => void;
};

type StepState = 'complete' | 'active' | 'upcoming' | 'problem';

const trackingSteps = [
  {
    label: 'Order received',
    customer: 'Payment and order details are being confirmed.',
    rider: 'Restaurant has the order in the workflow.',
  },
  {
    label: 'Kitchen prep',
    customer: 'The team is preparing your food.',
    rider: 'Order is being prepared for pickup.',
  },
  {
    label: 'Rider assigned',
    customer: 'A rider has been assigned to your delivery.',
    rider: 'This delivery is assigned to you.',
  },
  {
    label: 'On the way',
    customer: 'Your order is heading to the delivery address.',
    rider: 'Share location and continue to the customer.',
  },
  {
    label: 'Delivered',
    customer: 'Your order has been delivered.',
    rider: 'Delivery has been completed.',
  },
] as const;

export function shouldPollOrderTracking(order?: Order | null): boolean {
  if (!order) {
    return true;
  }

  const orderStatus = normalize(order.order_status);
  const deliveryStatus = normalize(order.delivery?.status);

  return !['delivered', 'cancelled'].includes(orderStatus)
    && !['delivered', 'failed', 'cancelled'].includes(deliveryStatus);
}

export function OrderTrackingCard({
  order,
  mode,
  refreshedAt,
  refreshing = false,
  sharingLocation = false,
  onRefresh,
  onShareLocation,
}: OrderTrackingCardProps) {
  const stage = trackingStage(order);
  const isProblem = isProblemOrder(order);
  const current = currentTrackingCopy(order, mode);
  const riderLocation = getRiderLocation(order);
  const lastUpdated = refreshedAt ? new Date(refreshedAt).toISOString() : order.updated_at ?? order.created_at;

  return (
    <AppCard style={[styles.card, isProblem && styles.problemCard]}>
      <View style={styles.headerRow}>
        <View style={styles.flex}>
          <View style={styles.liveRow}>
            <AppBadge label={shouldPollOrderTracking(order) ? 'Live tracking' : 'Tracking complete'} tone={isProblem ? 'danger' : 'green'} />
            {refreshing ? <AppBadge label="Refreshing" tone="gold" /> : null}
          </View>
          <AppText variant="h2">{current.title}</AppText>
          <AppText color={colors.text.secondary}>{current.description}</AppText>
        </View>
      </View>

      <View style={styles.statusRow}>
        <StatusBadge status={order.order_status_label ?? order.order_status} />
        <StatusBadge status={order.payment_status} />
        {order.delivery?.status ? <StatusBadge status={order.delivery.status_label ?? order.delivery.status} /> : null}
      </View>

      <View style={styles.timeline}>
        {trackingSteps.map((step, index) => (
          <TrackingStepRow
            key={step.label}
            label={step.label}
            description={step[mode]}
            state={stepState(index, stage, isProblem)}
            isLast={index === trackingSteps.length - 1}
          />
        ))}
      </View>

      <View style={styles.detailGrid}>
        <TrackingDetail label="Order number" value={order.order_number} />
        <TrackingDetail label="Last updated" value={formatDateTime(lastUpdated)} />
        {mode === 'customer' ? (
          <TrackingDetail label="Rider" value={order.rider?.name ?? 'Assigned when your order is ready'} />
        ) : (
          <TrackingDetail label="Customer" value={order.customer?.name ?? 'Guest customer'} />
        )}
        <TrackingDetail label="Delivery address" value={order.delivery_address ?? 'Not available'} />
      </View>

      {mode === 'customer' ? (
        <View style={styles.locationBox}>
          <View style={styles.flex}>
            <AppText variant="title">Rider location</AppText>
            <AppText color={colors.text.secondary}>
              {riderLocation
                ? `Last shared ${formatDateTime(order.rider?.last_location_updated_at)}`
                : 'The rider location appears here after the rider shares it.'}
            </AppText>
            {riderLocation ? (
              <AppText variant="caption" color={colors.text.secondary}>
                {riderLocation.latitude.toFixed(5)}, {riderLocation.longitude.toFixed(5)}
              </AppText>
            ) : null}
          </View>
          {riderLocation ? (
            <AppButton
              label="Open map"
              variant="secondary"
              onPress={() => void openCoordinates(riderLocation.latitude, riderLocation.longitude)}
            />
          ) : null}
        </View>
      ) : null}

      <View style={styles.actions}>
        {onRefresh ? (
          <AppButton
            label={refreshing ? 'Refreshing...' : 'Refresh'}
            variant="outline"
            loading={refreshing}
            onPress={onRefresh}
          />
        ) : null}
        {mode === 'rider' && onShareLocation ? (
          <AppButton
            label="Share location"
            variant="secondary"
            loading={sharingLocation}
            onPress={onShareLocation}
          />
        ) : null}
      </View>
    </AppCard>
  );
}

function TrackingStepRow({
  label,
  description,
  state,
  isLast,
}: {
  label: string;
  description: string;
  state: StepState;
  isLast: boolean;
}) {
  return (
    <View style={styles.stepRow}>
      <View style={styles.stepRail}>
        <View style={[styles.stepDot, styles[`${state}Dot`]]} />
        {!isLast ? <View style={[styles.stepLine, state === 'upcoming' && styles.upcomingLine]} /> : null}
      </View>
      <View style={styles.stepCopy}>
        <AppText variant="title" color={state === 'upcoming' ? colors.text.secondary : colors.text.primary}>
          {label}
        </AppText>
        <AppText color={colors.text.secondary}>{description}</AppText>
      </View>
    </View>
  );
}

function TrackingDetail({ label, value }: { label: string; value: string }) {
  return (
    <View style={styles.detail}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      <AppText>{value}</AppText>
    </View>
  );
}

function currentTrackingCopy(order: Order, mode: 'customer' | 'rider'): { title: string; description: string } {
  const orderStatus = normalize(order.order_status);
  const deliveryStatus = normalize(order.delivery?.status);

  if (deliveryStatus === 'failed' || orderStatus === 'cancelled') {
    return {
      title: mode === 'customer' ? 'Delivery needs attention' : 'Delivery flagged',
      description: mode === 'customer'
        ? 'The restaurant will review this order and contact you if needed.'
        : 'The restaurant can see this issue. Add a clear failure reason before closing it.',
    };
  }

  if (orderStatus === 'delivered' || deliveryStatus === 'delivered') {
    return {
      title: 'Delivered',
      description: mode === 'customer' ? 'Thanks for ordering from Arcade Kebab House.' : 'This delivery is complete.',
    };
  }

  if (orderStatus === 'out_for_delivery' || deliveryStatus === 'out_for_delivery') {
    return {
      title: mode === 'customer' ? 'On the way' : 'You are out for delivery',
      description: mode === 'customer'
        ? 'Your order is moving toward the delivery address.'
        : 'Keep the customer updated by sharing your latest location.',
    };
  }

  if (deliveryStatus === 'picked_up') {
    return {
      title: mode === 'customer' ? 'Picked up' : 'Order picked up',
      description: mode === 'customer'
        ? 'The rider has collected your order from Arcade Kebab House.'
        : 'Start delivery when you leave the pickup point.',
    };
  }

  if (['assigned', 'accepted'].includes(deliveryStatus) || orderStatus === 'assigned_to_rider') {
    return {
      title: mode === 'customer' ? 'Rider assigned' : 'Assigned delivery',
      description: mode === 'customer'
        ? 'A rider is handling your delivery.'
        : 'Accept and progress the delivery when ready.',
    };
  }

  if (['accepted', 'preparing', 'ready'].includes(orderStatus)) {
    return {
      title: mode === 'customer' ? 'Kitchen is preparing it' : 'Pickup coming up',
      description: mode === 'customer'
        ? 'The restaurant is preparing your order.'
        : 'Watch for rider assignment and pickup readiness.',
    };
  }

  if (order.payment_status !== 'paid' || orderStatus === 'pending_payment') {
    return {
      title: 'Waiting for payment',
      description: 'Stripe payment confirmation is required before the order moves to the kitchen.',
    };
  }

  return {
    title: mode === 'customer' ? 'Order received' : 'Order in queue',
    description: mode === 'customer'
      ? 'Arcade Kebab House has your order and will start preparing it soon.'
      : 'This order is waiting for the next restaurant action.',
  };
}

function trackingStage(order: Order): number {
  const orderStatus = normalize(order.order_status);
  const deliveryStatus = normalize(order.delivery?.status);

  if (orderStatus === 'delivered' || deliveryStatus === 'delivered') {
    return 4;
  }

  if (orderStatus === 'out_for_delivery' || ['picked_up', 'out_for_delivery'].includes(deliveryStatus)) {
    return 3;
  }

  if (orderStatus === 'assigned_to_rider' || ['assigned', 'accepted'].includes(deliveryStatus)) {
    return 2;
  }

  if (['accepted', 'preparing', 'ready'].includes(orderStatus)) {
    return 1;
  }

  return 0;
}

function stepState(index: number, stage: number, isProblem: boolean): StepState {
  if (isProblem && index === stage) {
    return 'problem';
  }

  if (index < stage || stage === 4) {
    return 'complete';
  }

  if (index === stage) {
    return 'active';
  }

  return 'upcoming';
}

function isProblemOrder(order: Order): boolean {
  return normalize(order.order_status) === 'cancelled' || normalize(order.delivery?.status) === 'failed';
}

function getRiderLocation(order: Order): { latitude: number; longitude: number } | null {
  const latitude = order.rider?.last_known_latitude;
  const longitude = order.rider?.last_known_longitude;

  if (typeof latitude !== 'number' || typeof longitude !== 'number') {
    return null;
  }

  return { latitude, longitude };
}

function normalize(value: string | null | undefined): string {
  return (value ?? '').toLowerCase();
}

function openCoordinates(latitude: number, longitude: number): Promise<unknown> {
  return Linking.openURL(`https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`);
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.lg,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
  },
  problemCard: {
    borderColor: colors.semantic.danger,
    backgroundColor: colors.brand.soft,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.md,
  },
  liveRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.sm,
  },
  statusRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  timeline: {
    borderRadius: radius.xl,
    backgroundColor: colors.surface.muted,
    padding: spacing.lg,
    gap: spacing.sm,
  },
  stepRow: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  stepRail: {
    width: 18,
    alignItems: 'center',
  },
  stepDot: {
    width: 14,
    height: 14,
    borderRadius: radius.pill,
    borderWidth: 3,
    borderColor: colors.surface.card,
    marginTop: 3,
  },
  stepLine: {
    flex: 1,
    width: 2,
    minHeight: 40,
    backgroundColor: colors.brand.border,
    marginVertical: spacing.xs,
  },
  stepCopy: {
    flex: 1,
    gap: spacing.xs,
    paddingBottom: spacing.md,
  },
  completeDot: {
    backgroundColor: colors.green.DEFAULT,
  },
  activeDot: {
    backgroundColor: colors.brand.primary,
  },
  upcomingDot: {
    backgroundColor: colors.border.strong,
  },
  problemDot: {
    backgroundColor: colors.semantic.danger,
  },
  upcomingLine: {
    backgroundColor: colors.border.light,
  },
  detailGrid: {
    gap: spacing.md,
  },
  detail: {
    gap: spacing.xs,
  },
  locationBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.xl,
    backgroundColor: colors.gold.pale,
    padding: spacing.lg,
  },
  actions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
});
