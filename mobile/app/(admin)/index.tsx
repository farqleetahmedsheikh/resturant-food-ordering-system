import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { getAdminDashboard } from '@/src/api/admin.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderRow } from '@/src/components/orders/OrderRow';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';

export default function AdminDashboardScreen() {
  const query = useQuery({
    queryKey: queryKeys.adminDashboard,
    queryFn: getAdminDashboard,
    refetchInterval: 8000,
    refetchIntervalInBackground: true,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading admin dashboard..." />;
  }

  if (query.isError || !query.data) {
    return <ErrorState message="Unable to load the admin dashboard." onRetry={() => void query.refetch()} />;
  }

  const { cards, latest_orders: latestOrders } = query.data;
  const openLabel = cards.restaurant_is_open ? 'Open' : 'Closed';
  const activeOrderCount = cards.pending_orders + cards.preparing_orders + cards.assigned_deliveries + cards.out_for_delivery;
  const deliveryLoad = cards.assigned_deliveries + cards.out_for_delivery;
  const activeOrders = latestOrders.filter((order) => !['delivered', 'cancelled'].includes(order.order_status));
  const feedOrders = activeOrders.length > 0 ? activeOrders : latestOrders;
  const syncLabel = formatSyncTime(query.dataUpdatedAt);

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title="Live dashboard" subtitle="Auto-updating order control." eyebrow="Admin" />

      <AppCard style={styles.heroCard}>
        <View style={styles.liveStrip}>
          <View style={styles.liveLeft}>
            <View style={[styles.liveDot, query.isFetching && styles.liveDotSyncing]} />
            <AppText variant="caption" color={colors.brand.primary}>
              LIVE ORDERS
            </AppText>
          </View>
          <StatusBadge status={openLabel} />
        </View>
        <View style={styles.heroTop}>
          <View style={styles.heroIcon}>
            <AppIcon name={appIcons.dashboard} size={20} color={colors.text.inverse} />
          </View>
          <View style={styles.flex}>
            <AppText variant="h2">{activeOrderCount} active</AppText>
            <AppText color={colors.text.secondary} numberOfLines={2}>
              {cards.pending_orders} waiting · {cards.preparing_orders} in kitchen · {deliveryLoad} with riders
            </AppText>
          </View>
        </View>
        <View style={styles.liveMetaRow}>
          <LivePill label="Paid today" value={formatCurrency(cards.total_paid_revenue)} />
          <LivePill label={query.isFetching ? 'Syncing' : 'Last sync'} value={syncLabel} />
        </View>
        <View style={styles.quickGrid}>
          <Link href="/(admin)/orders" asChild>
            <AppButton label="Orders" style={styles.quickAction} />
          </Link>
          <Link href="/(admin)/riders" asChild>
            <AppButton label="Riders" variant="secondary" style={styles.quickAction} />
          </Link>
          <Pressable
            accessibilityRole="button"
            onPress={() => void query.refetch()}
            style={({ pressed }) => [styles.syncButton, pressed && styles.pressed]}
          >
            <AppText variant="caption" color={colors.brand.primary}>
              Sync
            </AppText>
          </Pressable>
        </View>
      </AppCard>

      <View style={styles.metricGrid}>
        <Metric label="Queue" value={cards.pending_orders} tone="gold" />
        <Metric label="Kitchen" value={cards.preparing_orders} tone="info" />
        <Metric label="Delivery" value={deliveryLoad} tone="green" />
        <Metric label="Riders" value={cards.total_riders} tone="neutral" />
      </View>

      <AppCard style={styles.card}>
        <View style={styles.sectionTop}>
          <View style={styles.flex}>
            <View style={styles.feedTitleRow}>
              <AppText variant="title">Live order feed</AppText>
              <AppBadge label="8s refresh" tone="green" />
            </View>
            <AppText color={colors.text.secondary}>
              {activeOrders.length > 0 ? 'Active orders are shown first.' : 'No active orders. Showing recent history.'}
            </AppText>
          </View>
          <Link href="/(admin)/orders" asChild>
            <AppButton label="View all" variant="outline" />
          </Link>
        </View>
        {feedOrders.length === 0 ? (
          <AppText color={colors.text.secondary}>No orders have arrived yet.</AppText>
        ) : (
          feedOrders.slice(0, 6).map((order) => (
            <OrderRow
              key={order.id}
              order={order}
              href={{ pathname: '/(admin)/orders/[id]', params: { id: String(order.id) } }}
              compact
            />
          ))
        )}
      </AppCard>
    </AppScreen>
  );
}

function Metric({
  label,
  value,
  tone,
}: {
  label: string;
  value: number | string;
  tone: 'brand' | 'gold' | 'green' | 'info' | 'neutral';
}) {
  return (
    <View style={styles.metricCard}>
      <AppBadge label={label} tone={tone} />
      <AppText variant="title">{value}</AppText>
    </View>
  );
}

function LivePill({ label, value }: { label: string; value: string }) {
  return (
    <View style={styles.livePill}>
      <AppText variant="caption" color={colors.text.secondary}>{label}</AppText>
      <AppText variant="caption" color={colors.text.primary} numberOfLines={1}>{value}</AppText>
    </View>
  );
}

function formatSyncTime(value: number): string {
  if (!value) {
    return 'Starting';
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
  heroCard: {
    gap: spacing.sm,
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
    padding: spacing.md,
  },
  liveStrip: {
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
    width: 9,
    height: 9,
    borderRadius: radius.pill,
    backgroundColor: colors.green.DEFAULT,
  },
  liveDotSyncing: {
    backgroundColor: colors.gold.DEFAULT,
  },
  heroTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  heroIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.primary,
  },
  liveMetaRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  livePill: {
    flex: 1,
    minHeight: 44,
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  quickGrid: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  quickAction: {
    flex: 1,
  },
  syncButton: {
    minHeight: 48,
    minWidth: 58,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  metricGrid: {
    flexDirection: 'row',
    gap: spacing.xs,
  },
  metricCard: {
    flex: 1,
    minHeight: 56,
    justifyContent: 'space-between',
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.sm,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  sectionTop: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  feedTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: spacing.sm,
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
