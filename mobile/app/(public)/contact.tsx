import { useQuery } from '@tanstack/react-query';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors } from '@/src/theme';

export default function ContactScreen() {
  const query = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  if (query.isLoading) {
    return <LoadingScreen />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load contact information." onRetry={() => void query.refetch()} />;
  }

  const restaurant = query.data;

  return (
    <AppScreen>
      <AppHeader title="Contact" subtitle="Restaurant details come directly from Laravel settings." eyebrow="Arcade" />
      <AppCard>
        <AppText variant="title">{restaurant?.name}</AppText>
        <AppText color={colors.text.secondary}>{restaurant?.phone ?? 'Phone not configured'}</AppText>
        <AppText color={colors.text.secondary}>{restaurant?.email ?? 'Email not configured'}</AppText>
        <AppText color={colors.text.secondary}>{restaurant?.formatted_address ?? restaurant?.address ?? 'Address not configured'}</AppText>
      </AppCard>
    </AppScreen>
  );
}
