import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { getMenuItems } from '@/src/api/menu.api';
import { getCustomerOrders } from '@/src/api/orders.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { useAuthStore } from '@/src/auth/auth.store';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, spacing } from '@/src/theme';
import { formatDate } from '@/src/utils/date';
import { getRestaurantAvailability } from '@/src/utils/restaurant';
import type { Href } from 'expo-router';

export default function CustomerDashboardScreen() {
  const user = useAuthStore((state) => state.session?.user);
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const ordersQuery = useQuery({
    queryKey: queryKeys.customerOrders,
    queryFn: getCustomerOrders,
  });
  const featuredQuery = useQuery({
    queryKey: queryKeys.menuItems({ featured: true, per_page: 3 }),
    queryFn: () => getMenuItems({ featured: true, per_page: 3 }),
  });

  const availability = getRestaurantAvailability(restaurantQuery.data);
  const latestOrder = ordersQuery.data?.[0];
  const featuredItems = featuredQuery.data?.items ?? [];
  const isLoading = restaurantQuery.isLoading || ordersQuery.isLoading;
  const isError = restaurantQuery.isError || ordersQuery.isError;

  function refresh() {
    void restaurantQuery.refetch();
    void ordersQuery.refetch();
    void featuredQuery.refetch();
  }

  return (
    <AppScreen
      refreshing={restaurantQuery.isRefetching || ordersQuery.isRefetching || featuredQuery.isRefetching}
      onRefresh={refresh}
    >
      <AppHeader title={`Hi, ${user?.name ?? 'customer'}`} subtitle="Your Arcade Kebab House dashboard." eyebrow="Customer" />

      {isLoading ? <LoadingState label="Loading dashboard..." /> : null}
      {isError ? <ErrorState message="Unable to load dashboard data." onRetry={refresh} /> : null}

      {!isLoading && !isError ? (
        <>
          <AppCard style={styles.heroCard}>
            <View style={styles.statusRow}>
              <AppBadge label={availability.label} tone={availability.isOpenForOrders ? 'green' : 'gold'} />
              <AppText variant="caption" color={colors.text.secondary}>
                {availability.timezone ?? 'Restaurant timezone'}
              </AppText>
            </View>
            <AppText variant="title">Ready for your next order?</AppText>
            <AppText color={colors.text.secondary}>
              {availability.isOpenForOrders
                ? 'Browse the live menu and add your favorites to cart.'
                : availability.reason ?? 'Ordering is paused, but you can still browse the menu.'}
            </AppText>
          </AppCard>

          <View style={styles.quickGrid}>
            <QuickAction href="/(customer)/(tabs)/menu" label="Browse Menu" />
            <QuickAction href="/(customer)/(tabs)/cart" label="View Cart" />
            <QuickAction href="/(customer)/(tabs)/orders" label="My Orders" />
            <QuickAction href="/(customer)/(tabs)/profile" label="Profile" />
          </View>

          <View style={styles.section}>
            <SectionTitle title="Latest order" subtitle="Loaded from your authenticated order list." />
            {latestOrder ? (
              <AppCard style={styles.orderCard}>
                <View style={styles.statusRow}>
                  <View style={styles.flex}>
                    <AppText variant="title">{latestOrder.order_number}</AppText>
                    <AppText color={colors.text.secondary}>{formatDate(latestOrder.created_at)}</AppText>
                  </View>
                  <StatusBadge status={latestOrder.order_status_label ?? latestOrder.order_status} />
                </View>
                <PriceText amount={latestOrder.total} />
                <Link href={`/(customer)/orders/${latestOrder.id}`} asChild>
                  <AppButton label="View details" variant="outline" />
                </Link>
              </AppCard>
            ) : (
              <AppCard>
                <AppText color={colors.text.secondary}>Your first order will appear here after checkout.</AppText>
              </AppCard>
            )}
          </View>

          <View style={styles.section}>
            <SectionTitle title="Popular picks" subtitle="Featured items from the menu." />
            {featuredItems.length === 0 ? (
              <AppCard>
                <AppText color={colors.text.secondary}>No featured items are configured yet.</AppText>
              </AppCard>
            ) : (
              featuredItems.map((item) => (
                <AppCard key={item.id} style={styles.featuredRow}>
                  <View style={styles.flex}>
                    <AppText variant="title" numberOfLines={1}>
                      {item.name}
                    </AppText>
                    <AppText color={colors.text.secondary} numberOfLines={2}>
                      {item.description ?? item.category?.name ?? 'Menu item'}
                    </AppText>
                  </View>
                  <PriceText amount={item.price} />
                </AppCard>
              ))
            )}
          </View>
        </>
      ) : null}
    </AppScreen>
  );
}

function QuickAction({ href, label }: { href: Href; label: string }) {
  return (
    <Link href={href} asChild>
      <AppButton label={label} variant="secondary" fullWidth />
    </Link>
  );
}

const styles = StyleSheet.create({
  heroCard: {
    gap: spacing.md,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  quickGrid: {
    gap: spacing.md,
  },
  section: {
    gap: spacing.md,
  },
  orderCard: {
    gap: spacing.md,
  },
  featuredRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
  },
});
