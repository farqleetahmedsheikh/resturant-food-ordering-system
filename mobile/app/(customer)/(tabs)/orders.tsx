import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { getCustomerOrders } from '@/src/api/orders.api';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderRow } from '@/src/components/orders/OrderRow';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, shadows, spacing } from '@/src/theme';

type OrderFilter = 'all' | 'active' | 'completed' | 'cancelled';

export default function CustomerOrdersScreen() {
  const [filter, setFilter] = useState<OrderFilter>('all');
  const query = useQuery({
    queryKey: queryKeys.customerOrders,
    queryFn: getCustomerOrders,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading orders..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load customer orders." onRetry={() => void query.refetch()} />;
  }

  const orders = query.data ?? [];
  const activeOrders = orders.filter((order) => !['delivered', 'cancelled'].includes(order.order_status));
  const completedOrders = orders.filter((order) => order.order_status === 'delivered');
  const cancelledOrders = orders.filter((order) => order.order_status === 'cancelled');
  const visibleOrders = orders.filter((order) => {
    if (filter === 'active') {
      return !['delivered', 'cancelled'].includes(order.order_status);
    }

    if (filter === 'completed') {
      return order.order_status === 'delivered';
    }

    if (filter === 'cancelled') {
      return order.order_status === 'cancelled';
    }

    return true;
  });

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <View style={styles.hero}>
        <View style={styles.heroTop}>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>
              CUSTOMER
            </AppText>
            <AppText variant="h2">My orders</AppText>
            <AppText color={colors.text.secondary}>Track live deliveries and review receipts.</AppText>
          </View>
          <View style={styles.livePill}>
            <AppIcon name={appIcons.delivery} size={16} color={colors.green.dark} />
            <AppText variant="caption" color={colors.green.dark}>
              {activeOrders.length} live
            </AppText>
          </View>
        </View>
        <View style={styles.summaryGrid}>
          <SummaryPill label="Total" value={orders.length} />
          <SummaryPill label="Active" value={activeOrders.length} />
          <SummaryPill label="Done" value={completedOrders.length} />
          <SummaryPill label="Cancel" value={cancelledOrders.length} />
        </View>
      </View>

      <View style={styles.filters}>
        <FilterChip label="All" count={orders.length} active={filter === 'all'} onPress={() => setFilter('all')} />
        <FilterChip label="Active" count={activeOrders.length} active={filter === 'active'} onPress={() => setFilter('active')} />
        <FilterChip label="Done" count={completedOrders.length} active={filter === 'completed'} onPress={() => setFilter('completed')} />
        <FilterChip label="Cancel" count={cancelledOrders.length} active={filter === 'cancelled'} onPress={() => setFilter('cancelled')} />
      </View>

      <View style={styles.listHeader}>
        <AppText variant="caption" color={colors.text.secondary}>
          {visibleOrders.length} order{visibleOrders.length === 1 ? '' : 's'} shown
        </AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          Pull to refresh
        </AppText>
      </View>

      {orders.length === 0 ? (
        <EmptyState title="No orders yet" message="Your orders will appear here after checkout." />
      ) : visibleOrders.length === 0 ? (
        <AppCard style={styles.emptyFilter}>
          <AppText variant="title">No matching orders</AppText>
          <AppText color={colors.text.secondary}>Try another filter to see more order history.</AppText>
        </AppCard>
      ) : (
        <View style={styles.orderList}>
          {visibleOrders.map((order) => (
            <OrderRow key={order.id} order={order} href={`/(customer)/orders/${order.id}`} compact />
          ))}
        </View>
      )}
    </AppScreen>
  );
}

function SummaryPill({ label, value }: { label: string; value: number }) {
  return (
    <View style={styles.summaryPill}>
      <AppText variant="caption" color={colors.text.secondary}>{label}</AppText>
      <AppText variant="title">{value}</AppText>
    </View>
  );
}

function FilterChip({ label, count, active, onPress }: { label: string; count: number; active: boolean; onPress: () => void }) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ selected: active }}
      onPress={onPress}
      style={({ pressed }) => [styles.filterChip, active && styles.filterChipActive, pressed && styles.pressed]}
    >
      <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary}>{label}</AppText>
      <View style={[styles.countBubble, active && styles.countBubbleActive]}>
        <AppText variant="caption" color={active ? colors.brand.primary : colors.text.secondary}>
          {count}
        </AppText>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  hero: {
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  heroTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  livePill: {
    minHeight: 34,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.green.soft,
    paddingHorizontal: spacing.md,
  },
  summaryGrid: {
    flexDirection: 'row',
    gap: spacing.xs,
  },
  summaryPill: {
    flex: 1,
    gap: 2,
    minHeight: 48,
    borderRadius: radius.md,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.sm,
  },
  filters: {
    flexDirection: 'row',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.xs,
  },
  filterChip: {
    flex: 1,
    minHeight: 40,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    paddingHorizontal: spacing.xs,
  },
  filterChipActive: {
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
  emptyFilter: {
    gap: spacing.sm,
    backgroundColor: colors.surface.muted,
  },
  flex: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
