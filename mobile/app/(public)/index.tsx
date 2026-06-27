import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { DiagnosticsCard } from '@/src/components/feedback/DiagnosticsCard';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';

export default function PublicHomeScreen() {
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  if (restaurantQuery.isLoading) {
    return <LoadingScreen label="Loading restaurant..." />;
  }

  if (restaurantQuery.isError) {
    return <ErrorState message="Unable to load restaurant details." onRetry={() => void restaurantQuery.refetch()} />;
  }

  const restaurant = restaurantQuery.data;

  return (
    <AppScreen>
      <View style={styles.hero}>
        <AppBadge label={restaurant?.is_open ? 'Open now' : 'Ordering paused'} tone={restaurant?.is_open ? 'green' : 'gold'} />
        <AppText variant="h1" color={colors.text.inverse}>
          Arcade Kebab House
        </AppText>
        <AppText color="#F8F1EB">
          {restaurant?.short_description ?? 'Fresh kebabs, burgers, sides, and drinks prepared for fast pickup and delivery.'}
        </AppText>
        <View style={styles.actions}>
          <Link href="/(public)/menu" asChild>
            <AppButton label="View Menu" />
          </Link>
          <Link href="/(auth)/login" asChild>
            <AppButton label="Login" variant="outline" />
          </Link>
        </View>
      </View>

      <AppCard>
        <AppText variant="title">Delivery basics</AppText>
        <AppText color={colors.text.secondary}>
          Delivery fee {formatCurrency(restaurant?.delivery_fee ?? 0)} · Minimum order{' '}
          {formatCurrency(restaurant?.minimum_order_amount ?? 0)}
        </AppText>
        <AppText color={colors.text.secondary}>
          Timezone: {restaurant?.timezone ?? 'Provided by backend'}
        </AppText>
      </AppCard>

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
  actions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
});
