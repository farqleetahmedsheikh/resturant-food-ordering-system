import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import * as Location from 'expo-location';
import { Link } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';

import { normalizeApiError } from '@/src/api/api-error';
import { getRiderDashboard, updateRiderLocation } from '@/src/api/deliveries.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { DeliveryRow } from '@/src/components/deliveries/DeliveryRow';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';

export default function RiderDashboardScreen() {
  const queryClient = useQueryClient();
  const [message, setMessage] = useState<{ tone: 'success' | 'error' | 'info'; text: string } | null>(null);
  const query = useQuery({
    queryKey: queryKeys.riderDashboard,
    queryFn: getRiderDashboard,
  });

  const locationMutation = useMutation({
    mutationFn: updateRiderLocation,
    onSuccess: async () => {
      setMessage({ tone: 'success', text: 'Location shared with the restaurant.' });
      await queryClient.invalidateQueries({ queryKey: queryKeys.riderDashboard });
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

  if (query.isLoading) {
    return <LoadingScreen label="Loading rider dashboard..." />;
  }

  if (query.isError || !query.data) {
    return <ErrorState message="Unable to load rider dashboard." onRetry={() => void query.refetch()} />;
  }

  const dashboard = query.data;

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()} contentStyle={styles.screen}>
      <AppHeader title="Rider" subtitle="Assigned delivery workflow." eyebrow="Arcade Kebab House" />

      {message ? <FeedbackMessage tone={message.tone} message={message.text} /> : null}

      <AppCard style={styles.heroCard}>
        <View style={styles.heroTop}>
          <View style={styles.heroIcon}>
            <AppIcon name={appIcons.delivery} size={20} color={colors.text.inverse} />
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>
              ACTIVE RUN
            </AppText>
            <AppText variant="h2">{dashboard.active_deliveries}</AppText>
            <AppText color={colors.text.secondary}>Deliveries currently assigned to you.</AppText>
          </View>
          <AppBadge label="AUD orders" tone="gold" />
        </View>
        <View style={styles.actionRow}>
          <Link href="/(rider)/(tabs)/assigned" asChild>
            <AppButton label="Assigned" style={styles.actionButton} />
          </Link>
          <AppButton
            label="Share location"
            variant="secondary"
            loading={locationMutation.isPending}
            onPress={() => void syncLocation()}
            style={styles.actionButton}
          />
        </View>
      </AppCard>

      <View style={styles.metricGrid}>
        <Metric label="Total assigned" value={dashboard.total_assigned_orders} />
        <Metric label="Delivered" value={dashboard.delivered_orders} />
        <Metric label="Failed" value={dashboard.failed_deliveries} danger />
      </View>

      <AppCard style={styles.card}>
        <View style={styles.sectionTop}>
          <View style={styles.flex}>
            <AppText variant="title">Latest deliveries</AppText>
            <AppText color={colors.text.secondary}>Tap a delivery to update its progress.</AppText>
          </View>
          <Link href="/(rider)/(tabs)/history" asChild>
            <AppButton label="History" variant="outline" />
          </Link>
        </View>
        {dashboard.latest_orders.length === 0 ? (
          <AppText color={colors.text.secondary}>No delivery assignments yet.</AppText>
        ) : (
          dashboard.latest_orders.map((order) => (
            <DeliveryRow
              key={order.id}
              delivery={order}
              href={{ pathname: '/(rider)/deliveries/[id]', params: { id: String(order.id) } }}
              compact
            />
          ))
        )}
      </AppCard>
    </AppScreen>
  );
}

function Metric({ label, value, danger = false }: { label: string; value: number; danger?: boolean }) {
  return (
    <View style={styles.metricCard}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      <AppText variant="title" color={danger ? colors.semantic.danger : colors.text.primary}>
        {value}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  heroCard: {
    gap: spacing.md,
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
    padding: spacing.md,
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
  actionRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  actionButton: {
    flex: 1,
  },
  metricGrid: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  metricCard: {
    flex: 1,
    minHeight: 68,
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
    gap: spacing.md,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
});
