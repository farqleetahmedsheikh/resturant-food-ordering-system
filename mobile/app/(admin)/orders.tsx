import { useQuery } from '@tanstack/react-query';
import { router } from 'expo-router';
import type { Href } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';

import { getAdminOrders } from '@/src/api/orders.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { AppInput } from '@/src/components/forms/AppInput';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { Order } from '@/src/types/order';
import { formatDateTime } from '@/src/utils/date';

type WorkflowFilter = 'all' | 'queue' | 'kitchen' | 'delivery' | 'closed';
type PaymentFilter = '' | 'paid' | 'pending' | 'failed';

const workflowFilters: { label: string; value: WorkflowFilter }[] = [
  { label: 'All', value: 'all' },
  { label: 'Queue', value: 'queue' },
  { label: 'Kitchen', value: 'kitchen' },
  { label: 'Delivery', value: 'delivery' },
  { label: 'Closed', value: 'closed' },
];

const payments: { label: string; value: PaymentFilter }[] = [
  { label: 'Any pay', value: '' },
  { label: 'Paid', value: 'paid' },
  { label: 'Pending', value: 'pending' },
  { label: 'Failed', value: 'failed' },
];

export default function AdminOrdersScreen() {
  const [workflow, setWorkflow] = useState<WorkflowFilter>('all');
  const [paymentStatus, setPaymentStatus] = useState<PaymentFilter>('');
  const [search, setSearch] = useState('');
  const filters = useMemo(
    () => ({
      payment_status: paymentStatus || undefined,
      search: search.trim() || undefined,
      per_page: 50,
    }),
    [paymentStatus, search],
  );
  const query = useQuery({
    queryKey: queryKeys.adminOrders(filters),
    queryFn: () => getAdminOrders(filters),
    refetchInterval: 8000,
    refetchIntervalInBackground: true,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading orders..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load admin orders." onRetry={() => void query.refetch()} />;
  }

  const orders = query.data ?? [];
  const visibleOrders = orders.filter((order) => matchesWorkflow(order, workflow));
  const counts = getWorkflowCounts(orders);
  const activeCount = counts.queue + counts.kitchen + counts.delivery;

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title="Orders" subtitle="Live queue and workflow control." eyebrow="Admin" />

      <AppCard style={styles.controlCard}>
        <View style={styles.liveRow}>
          <View style={styles.liveLeft}>
            <View style={[styles.liveDot, query.isFetching && styles.liveDotSyncing]} />
            <AppText variant="caption" color={colors.brand.primary}>LIVE QUEUE</AppText>
          </View>
          <AppBadge label={query.isFetching ? 'Syncing' : '8s refresh'} tone={query.isFetching ? 'gold' : 'green'} />
        </View>
        <View style={styles.searchTop}>
          <View style={styles.searchIcon}>
            <AppIcon name={appIcons.orders} size={18} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title">{activeCount} active orders</AppText>
            <AppText color={colors.text.secondary}>
              {counts.queue} queue · {counts.kitchen} kitchen · {counts.delivery} delivery
            </AppText>
          </View>
        </View>
        <AppInput
          label="Search"
          placeholder="Order number, customer, phone"
          value={search}
          onChangeText={setSearch}
          autoCapitalize="none"
        />
      </AppCard>

      <ChipScroller
        items={workflowFilters.map((item) => ({ ...item, count: workflowCountFor(item.value, counts, orders.length) }))}
        value={workflow}
        onChange={setWorkflow}
      />
      <ChipScroller items={payments} value={paymentStatus} onChange={setPaymentStatus} />

      <View style={styles.listHeader}>
        <AppText variant="caption" color={colors.text.secondary}>
          {visibleOrders.length} shown
        </AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          Last sync {formatSyncTime(query.dataUpdatedAt)}
        </AppText>
      </View>

      {visibleOrders.length === 0 ? (
        <EmptyState title="No matching orders" message="Try a different status, payment state, or search term." />
      ) : (
        <View style={styles.orderList}>
          {visibleOrders.map((order) => (
            <AdminOrderTicket
              key={order.id}
              order={order}
              href={{ pathname: '/(admin)/orders/[id]', params: { id: String(order.id) } }}
            />
          ))}
        </View>
      )}
    </AppScreen>
  );
}

function ChipScroller<TValue extends string>({
  items,
  value,
  onChange,
}: {
  items: { label: string; value: TValue; count?: number }[];
  value: TValue;
  onChange: (value: TValue) => void;
}) {
  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipScroll}>
      {items.map((item) => {
        const active = item.value === value;

        return (
          <Pressable
            key={item.value || item.label}
            accessibilityRole="button"
            accessibilityState={{ selected: active }}
            onPress={() => onChange(item.value)}
            style={({ pressed }) => [styles.filterChip, active && styles.filterChipActive, pressed && styles.pressed]}
          >
            <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary}>
              {item.label}
            </AppText>
            {typeof item.count === 'number' ? (
              <View style={[styles.countBubble, active && styles.countBubbleActive]}>
                <AppText variant="caption" color={active ? colors.brand.primary : colors.text.secondary}>
                  {item.count}
                </AppText>
              </View>
            ) : null}
          </Pressable>
        );
      })}
    </ScrollView>
  );
}

function AdminOrderTicket({ order, href }: { order: Order; href: Href }) {
  const stage = getOrderStage(order);

  return (
    <Pressable
      accessibilityRole="button"
      onPress={() => {
        router.push(href);
      }}
      style={({ pressed }) => pressed && styles.pressed}
    >
      <AppCard style={styles.ticket}>
        <View style={styles.ticketTop}>
          <View style={styles.ticketIcon}>
            <AppIcon name={appIcons.orders} size={18} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={1}>{order.order_number}</AppText>
            <AppText variant="caption" color={colors.text.secondary} numberOfLines={1}>
              {order.customer?.name ?? 'Guest customer'} · {formatDateTime(order.created_at)}
            </AppText>
          </View>
          <StatusBadge status={order.order_status_label ?? order.order_status} />
        </View>

        <View style={styles.stageRail}>
          {['Queue', 'Kitchen', 'Rider', 'Done'].map((label, index) => (
            <View key={label} style={styles.stageItem}>
              <View style={[styles.stageDot, index <= stage && styles.stageDotActive]} />
              <AppText variant="caption" color={index <= stage ? colors.brand.primary : colors.text.muted}>
                {label}
              </AppText>
            </View>
          ))}
        </View>

        <AppText color={colors.text.secondary} numberOfLines={1}>
          {order.delivery_address ?? 'No delivery address'}
        </AppText>

        <View style={styles.ticketFooter}>
          <View style={styles.metaRow}>
            <PriceText amount={order.total} />
            <View style={styles.dot} />
            <AppText variant="caption" color={paymentColor(order.payment_status)}>
              {order.payment_status.replaceAll('_', ' ')}
            </AppText>
          </View>
          <View style={styles.openPill}>
            <AppIcon name={appIcons.chevronRight} size={16} color={colors.brand.primary} />
          </View>
        </View>
      </AppCard>
    </Pressable>
  );
}

function getWorkflowCounts(orders: Order[]): Record<WorkflowFilter, number> {
  return {
    all: orders.length,
    queue: orders.filter((order) => matchesWorkflow(order, 'queue')).length,
    kitchen: orders.filter((order) => matchesWorkflow(order, 'kitchen')).length,
    delivery: orders.filter((order) => matchesWorkflow(order, 'delivery')).length,
    closed: orders.filter((order) => matchesWorkflow(order, 'closed')).length,
  };
}

function workflowCountFor(value: WorkflowFilter, counts: Record<WorkflowFilter, number>, total: number): number {
  return value === 'all' ? total : counts[value];
}

function matchesWorkflow(order: Order, workflow: WorkflowFilter): boolean {
  if (workflow === 'all') {
    return true;
  }

  if (workflow === 'queue') {
    return ['pending_payment', 'pending'].includes(order.order_status);
  }

  if (workflow === 'kitchen') {
    return ['accepted', 'preparing', 'ready'].includes(order.order_status);
  }

  if (workflow === 'delivery') {
    return ['assigned_to_rider', 'out_for_delivery'].includes(order.order_status);
  }

  return ['delivered', 'cancelled'].includes(order.order_status);
}

function getOrderStage(order: Order): number {
  if (['delivered', 'cancelled'].includes(order.order_status)) {
    return 3;
  }

  if (['assigned_to_rider', 'out_for_delivery'].includes(order.order_status)) {
    return 2;
  }

  if (['accepted', 'preparing', 'ready'].includes(order.order_status)) {
    return 1;
  }

  return 0;
}

function paymentColor(status: string): string {
  if (status === 'paid') {
    return colors.green.dark;
  }

  if (['failed', 'cancelled'].includes(status)) {
    return colors.semantic.danger;
  }

  return colors.gold.dark;
}

function formatSyncTime(value: number): string {
  if (!value) {
    return 'starting';
  }

  return new Intl.DateTimeFormat('en-AU', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  }).format(new Date(value));
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  controlCard: {
    gap: spacing.sm,
    padding: spacing.md,
  },
  liveRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
  },
  liveLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  liveDot: {
    width: 8,
    height: 8,
    borderRadius: radius.pill,
    backgroundColor: colors.green.DEFAULT,
  },
  liveDotSyncing: {
    backgroundColor: colors.gold.DEFAULT,
  },
  searchTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  searchIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  chipScroll: {
    flexDirection: 'row',
    gap: spacing.sm,
    paddingVertical: spacing.xs,
  },
  filterChip: {
    minHeight: 40,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  filterChipActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  countBubble: {
    minWidth: 22,
    minHeight: 22,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.xs,
  },
  countBubbleActive: {
    backgroundColor: colors.surface.card,
  },
  listHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.xs,
  },
  orderList: {
    gap: spacing.sm,
  },
  ticket: {
    gap: spacing.sm,
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  ticketTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  ticketIcon: {
    width: 34,
    height: 34,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  stageRail: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderRadius: radius.md,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
  },
  stageItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
  },
  stageDot: {
    width: 7,
    height: 7,
    borderRadius: radius.pill,
    backgroundColor: colors.border.strong,
  },
  stageDotActive: {
    backgroundColor: colors.brand.primary,
  },
  ticketFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
  },
  metaRow: {
    flex: 1,
    minWidth: 0,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  dot: {
    width: 4,
    height: 4,
    borderRadius: radius.pill,
    backgroundColor: colors.border.strong,
  },
  openPill: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
