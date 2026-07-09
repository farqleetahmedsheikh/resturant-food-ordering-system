import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams } from 'expo-router';
import { Linking, StyleSheet, View } from 'react-native';

import { getAdminRider } from '@/src/api/admin.api';
import { normalizeApiError } from '@/src/api/api-error';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderRow } from '@/src/components/orders/OrderRow';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import { formatDateTime } from '@/src/utils/date';

export default function AdminRiderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const query = useQuery({
    queryKey: queryKeys.adminRider(id),
    queryFn: () => getAdminRider(id),
    enabled: Boolean(id),
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading rider..." />;
  }

  if (query.isError || !query.data) {
    return <ErrorState message={normalizeApiError(query.error).message} onRetry={() => void query.refetch()} />;
  }

  const { rider, active_orders: activeOrders, delivery_history: deliveryHistory } = query.data;
  const hasCoordinates = rider.last_known_latitude !== null && rider.last_known_longitude !== null;

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title={rider.name} subtitle="Activity and assigned deliveries." eyebrow="Admin rider" />

      <AppCard style={styles.heroCard}>
        <View style={styles.top}>
          <View style={styles.avatar}>
            <AppText variant="title" color={colors.brand.primary}>{rider.name.slice(0, 1).toUpperCase()}</AppText>
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={1}>{rider.email}</AppText>
            <AppText color={colors.text.secondary} numberOfLines={1}>{rider.phone ?? 'No phone added'}</AppText>
          </View>
          <StatusBadge status={rider.is_active ? 'active' : 'inactive'} />
        </View>
        <View style={styles.statRow}>
          <Detail label="Assigned" value={String(rider.assigned_orders_count ?? activeOrders.length)} />
          <Detail label="Delivered" value={String(rider.delivered_orders_count ?? deliveryHistory.length)} />
        </View>
        <View style={styles.locationPanel}>
          <View style={styles.locationIcon}>
            <AppIcon name={appIcons.delivery} size={18} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title">Last location</AppText>
            <AppText color={colors.text.secondary}>{formatDateTime(rider.last_location_updated_at)}</AppText>
            {hasCoordinates ? (
              <AppText variant="caption" color={colors.text.secondary}>
                {rider.last_known_latitude?.toFixed(5)}, {rider.last_known_longitude?.toFixed(5)}
              </AppText>
            ) : null}
          </View>
        </View>
        <View style={styles.actionRow}>
          {rider.phone ? (
            <AppButton label="Call rider" variant="outline" onPress={() => void Linking.openURL(`tel:${rider.phone}`)} />
          ) : null}
          {hasCoordinates ? (
            <AppButton
              label="Open map"
              variant="secondary"
              onPress={() =>
                void Linking.openURL(
                  `https://www.google.com/maps/search/?api=1&query=${rider.last_known_latitude},${rider.last_known_longitude}`,
                )
              }
            />
          ) : null}
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Active deliveries" subtitle={`${activeOrders.length} currently assigned`} />
        {activeOrders.length === 0 ? (
          <EmptyState title="No active work" message="This rider has no active delivery assignments." />
        ) : (
          activeOrders.map((order) => (
            <OrderRow
              key={order.id}
              order={order}
              href={{ pathname: '/(admin)/orders/[id]', params: { id: String(order.id) } }}
              compact
            />
          ))
        )}
      </AppCard>

      <AppCard style={styles.card}>
        <SectionTitle title="Delivery history" subtitle={`${deliveryHistory.length} recent records`} />
        {deliveryHistory.length === 0 ? (
          <EmptyState title="No completed deliveries" message="Completed delivery records will appear here." />
        ) : (
          deliveryHistory.map((order) => (
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

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  heroCard: {
    gap: spacing.md,
    padding: spacing.md,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  top: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  avatar: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  statRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  locationPanel: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  locationIcon: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  actionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  detail: {
    flex: 1,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
    gap: spacing.xs,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
});
