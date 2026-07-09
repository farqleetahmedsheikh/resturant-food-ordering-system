import { useQuery } from '@tanstack/react-query';
import { View } from 'react-native';

import { getRiderDeliveries } from '@/src/api/deliveries.api';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { DeliveryRow } from '@/src/components/deliveries/DeliveryRow';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';

export default function RiderAssignedScreen() {
  const query = useQuery({
    queryKey: queryKeys.riderDeliveries,
    queryFn: getRiderDeliveries,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading deliveries..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load assigned deliveries." onRetry={() => void query.refetch()} />;
  }

  const deliveries = query.data ?? [];

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title="Assigned" subtitle="Active delivery queue." eyebrow="Rider" />
      <AppCard style={styles.summaryCard}>
        <View style={styles.summaryIcon}>
          <AppIcon name={appIcons.delivery} size={20} />
        </View>
        <View style={styles.flex}>
          <AppText variant="title">{deliveries.length} active</AppText>
          <AppText color={colors.text.secondary}>Tap an order to update pickup and delivery progress.</AppText>
        </View>
      </AppCard>
      {deliveries.length === 0 ? (
        <EmptyState title="No active deliveries" message="Assigned orders will appear here." />
      ) : (
        <View style={styles.list}>
          {deliveries.map((delivery) => (
            <DeliveryRow
              key={delivery.id}
              delivery={delivery}
              href={{ pathname: '/(rider)/deliveries/[id]', params: { id: String(delivery.id) } }}
              compact
            />
          ))}
        </View>
      )}
    </AppScreen>
  );
}

const styles = {
  screen: {
    gap: spacing.sm,
  },
  summaryCard: {
    minHeight: 76,
    flexDirection: 'row' as const,
    alignItems: 'center' as const,
    gap: spacing.sm,
    padding: spacing.md,
  },
  summaryIcon: {
    width: 42,
    height: 42,
    alignItems: 'center' as const,
    justifyContent: 'center' as const,
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  list: {
    gap: spacing.sm,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
};
