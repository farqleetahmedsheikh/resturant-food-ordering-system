import { useQuery } from '@tanstack/react-query';
import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { getAdminRiders } from '@/src/api/admin.api';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { Rider } from '@/src/types/rider';
import { formatDateTime } from '@/src/utils/date';

export default function AdminRidersScreen() {
  const query = useQuery({
    queryKey: queryKeys.adminRiders,
    queryFn: getAdminRiders,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading riders..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load riders." onRetry={() => void query.refetch()} />;
  }

  const riders = query.data ?? [];
  const activeCount = riders.filter((rider) => rider.is_active).length;
  const activeWorkCount = riders.reduce((sum, rider) => sum + (rider.assigned_orders_count ?? 0), 0);
  const onlineLocationCount = riders.filter((rider) => Boolean(rider.last_location_updated_at)).length;

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title="Riders" subtitle="Dispatch roster and live rider status." eyebrow="Admin" />

      <AppCard style={styles.dispatchCard}>
        <View style={styles.dispatchTop}>
          <View style={styles.dispatchIcon}>
            <AppIcon name={appIcons.riders} size={20} color={colors.text.inverse} />
          </View>
          <View style={styles.flex}>
            <AppText variant="title">Dispatch roster</AppText>
            <AppText color={colors.text.secondary}>{activeCount}/{riders.length} riders available now</AppText>
          </View>
          <StatusBadge status={activeCount > 0 ? 'active' : 'inactive'} />
        </View>
        <View style={styles.summaryGrid}>
          <SummaryItem label="Available" value={activeCount} icon={appIcons.riders} />
          <SummaryItem label="On jobs" value={activeWorkCount} icon={appIcons.delivery} />
          <SummaryItem label="Located" value={onlineLocationCount} icon={appIcons.history} />
        </View>
      </AppCard>

      {riders.length === 0 ? (
        <EmptyState title="No riders" message="Create riders in the admin dashboard to assign deliveries." />
      ) : (
        <View style={styles.riderList}>
          {riders
            .slice()
            .sort(sortRidersForDispatch)
            .map((rider) => <RiderCard key={rider.id} rider={rider} />)}
        </View>
      )}
    </AppScreen>
  );
}

function SummaryItem({ label, value, icon }: { label: string; value: number; icon: AppIconName }) {
  return (
    <View style={styles.summaryItem}>
      <AppIcon name={icon} size={16} />
      <View style={styles.flex}>
        <AppText variant="title">{value}</AppText>
        <AppText variant="caption" color={colors.text.secondary}>{label}</AppText>
      </View>
    </View>
  );
}

function RiderCard({ rider }: { rider: Rider }) {
  const assigned = rider.assigned_orders_count ?? 0;
  const delivered = rider.delivered_orders_count ?? 0;
  const hasFreshLocation = hasRecentLocation(rider.last_location_updated_at);
  const locationText = rider.last_location_updated_at
    ? `${hasFreshLocation ? 'Live' : 'Last'} location ${formatLocationAge(rider.last_location_updated_at)}`
    : 'No location shared';

  return (
    <Pressable
      accessibilityRole="button"
      onPress={() => router.push({ pathname: '/(admin)/riders/[id]', params: { id: String(rider.id) } })}
    >
      <AppCard style={styles.riderCard}>
        <View style={styles.top}>
          <View style={styles.avatar}>
            <AppText variant="title" color={colors.brand.primary}>{rider.name.slice(0, 1).toUpperCase()}</AppText>
          </View>
          <View style={styles.flex}>
            <AppText variant="title" numberOfLines={1}>{rider.name}</AppText>
            <AppText color={colors.text.secondary} numberOfLines={1}>{rider.phone ?? rider.email}</AppText>
          </View>
          <StatusBadge status={rider.is_active ? 'active' : 'inactive'} />
        </View>
        <View style={styles.metaRow}>
          <RiderMetric label="Jobs" value={assigned} active={assigned > 0} />
          <RiderMetric label="Done" value={delivered} />
          <View style={[styles.locationPill, hasFreshLocation && styles.locationPillLive]}>
            <View style={[styles.locationDot, hasFreshLocation && styles.locationDotLive]} />
            <AppText variant="caption" color={hasFreshLocation ? colors.green.dark : colors.text.secondary} numberOfLines={1}>
              {locationText}
            </AppText>
          </View>
          <View style={styles.chevron}>
            <AppIcon name={appIcons.chevronRight} size={16} color={colors.brand.primary} />
          </View>
        </View>
      </AppCard>
    </Pressable>
  );
}

function RiderMetric({ label, value, active = false }: { label: string; value: number; active?: boolean }) {
  return (
    <View style={[styles.metricPill, active && styles.metricPillActive]}>
      <AppText variant="caption" color={active ? colors.brand.primary : colors.text.primary}>{value}</AppText>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
    </View>
  );
}

function sortRidersForDispatch(a: Rider, b: Rider): number {
  const activeDelta = Number(b.is_active) - Number(a.is_active);

  if (activeDelta !== 0) {
    return activeDelta;
  }

  return (b.assigned_orders_count ?? 0) - (a.assigned_orders_count ?? 0);
}

function hasRecentLocation(value: string | null | undefined): boolean {
  if (!value) {
    return false;
  }

  const timestamp = new Date(value).getTime();

  if (Number.isNaN(timestamp)) {
    return false;
  }

  return Date.now() - timestamp < 15 * 60 * 1000;
}

function formatLocationAge(value: string | null | undefined): string {
  if (!value) {
    return 'not available';
  }

  const timestamp = new Date(value).getTime();

  if (Number.isNaN(timestamp)) {
    return 'not available';
  }

  const minutes = Math.max(0, Math.round((Date.now() - timestamp) / 60000));

  if (minutes < 1) {
    return 'now';
  }

  if (minutes < 60) {
    return `${minutes}m ago`;
  }

  return formatDateTime(value);
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  dispatchCard: {
    gap: spacing.md,
    padding: spacing.md,
  },
  dispatchTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  dispatchIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.primary,
  },
  summaryGrid: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  summaryItem: {
    flex: 1,
    minHeight: 52,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    borderRadius: radius.md,
    backgroundColor: colors.surface.muted,
    padding: spacing.sm,
  },
  riderList: {
    gap: spacing.sm,
  },
  riderCard: {
    gap: spacing.sm,
    padding: spacing.sm,
    borderRadius: radius.lg,
  },
  top: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  avatar: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  metricPill: {
    minHeight: 34,
    minWidth: 52,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
  },
  metricPillActive: {
    backgroundColor: colors.brand.soft,
  },
  locationPill: {
    flex: 1,
    minHeight: 34,
    minWidth: 0,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
  },
  locationPillLive: {
    backgroundColor: colors.green.soft,
  },
  locationDot: {
    width: 7,
    height: 7,
    borderRadius: radius.pill,
    backgroundColor: colors.text.muted,
  },
  locationDotLive: {
    backgroundColor: colors.green.DEFAULT,
  },
  chevron: {
    width: 30,
    height: 30,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
});
