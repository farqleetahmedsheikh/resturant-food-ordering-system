import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { getMenuItems } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { DiagnosticsCard } from '@/src/components/feedback/DiagnosticsCard';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { MenuItemCard } from '@/src/components/menu/MenuItemCard';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, spacing } from '@/src/theme';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

export default function PublicHomeScreen() {
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const featuredQuery = useQuery({
    queryKey: queryKeys.menuItems({ featured: true, per_page: 4 }),
    queryFn: () => getMenuItems({ featured: true, per_page: 4 }),
  });

  if (restaurantQuery.isLoading) {
    return <LoadingScreen label="Loading restaurant..." />;
  }

  if (restaurantQuery.isError) {
    return <ErrorState message="Unable to load restaurant details." onRetry={() => void restaurantQuery.refetch()} />;
  }

  const restaurant = restaurantQuery.data;
  const availability = getRestaurantAvailability(restaurant);
  const featuredItems = featuredQuery.data?.items ?? [];

  return (
    <AppScreen>
      <View style={styles.hero}>
        <View style={styles.heroTop}>
          <RestaurantLogo
            name={restaurant?.name}
            logoUrl={restaurant?.logo_url}
            initials={restaurant?.initials}
            inverse
          />
          <AppBadge
            label={availability.label}
            tone={availability.isOpenForOrders ? 'green' : 'gold'}
          />
        </View>
        <View style={styles.heroCopy}>
          <AppText variant="h1" color={colors.text.inverse}>
            {restaurant?.name ?? 'Arcade Kebab House'}
          </AppText>
          <AppText color="#F8F1EB">
            {restaurant?.short_description ?? 'Fresh kebabs, burgers, sides, and drinks prepared for pickup and delivery.'}
          </AppText>
        </View>
        <View style={styles.actions}>
          <Link href="/(public)/menu" asChild>
            <AppButton label="Browse Menu" />
          </Link>
          <Link href="/(auth)/login" asChild>
            <AppButton label="Sign In" variant="outline" />
          </Link>
        </View>
      </View>

      <AppCard style={styles.infoCard}>
        <SectionTitle title="Ordering details" subtitle={`Timezone: ${availability.timezone ?? 'Configured by restaurant'}`} />
        <View style={styles.statRow}>
          <View style={styles.stat}>
            <AppText variant="caption" color={colors.text.secondary}>
              Delivery fee
            </AppText>
            <PriceText amount={restaurant?.delivery_fee ?? 0} />
          </View>
          <View style={styles.stat}>
            <AppText variant="caption" color={colors.text.secondary}>
              Minimum order
            </AppText>
            <PriceText amount={restaurant?.minimum_order_amount ?? 0} />
          </View>
        </View>
        {availability.reason ? <AppText color={colors.text.secondary}>{availability.reason}</AppText> : null}
        <AppText color={colors.text.secondary}>
          Hours: {restaurant?.opening_time && restaurant?.closing_time ? `${restaurant.opening_time} - ${restaurant.closing_time}` : 'Online ordering hours are managed by the restaurant.'}
        </AppText>
      </AppCard>

      <View style={styles.section}>
        <SectionTitle title="Popular picks" subtitle="Featured items from the live menu." />
        {featuredQuery.isLoading ? (
          <AppCard>
            <AppText color={colors.text.secondary}>Loading popular items...</AppText>
          </AppCard>
        ) : featuredQuery.isError ? (
          <ErrorState message="Unable to load popular items." onRetry={() => void featuredQuery.refetch()} />
        ) : featuredItems.length === 0 ? (
          <EmptyState title="No popular items yet" message="Featured menu items will appear here once configured." />
        ) : (
          featuredItems.map((item) => (
            <MenuItemCard key={item.id} item={item} detailHref={`/(public)/item/${item.id}`} />
          ))
        )}
      </View>

      <DiagnosticsCard />
    </AppScreen>
  );
}

const styles = StyleSheet.create({
  hero: {
    gap: spacing.lg,
    borderRadius: 28,
    backgroundColor: colors.surface.dark,
    padding: spacing['2xl'],
  },
  heroTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  heroCopy: {
    gap: spacing.sm,
  },
  actions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  infoCard: {
    gap: spacing.lg,
  },
  statRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  stat: {
    minWidth: 130,
    flex: 1,
    gap: spacing.xs,
  },
  section: {
    gap: spacing.md,
  },
});
