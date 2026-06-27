import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams } from 'expo-router';

import { getMenuItem } from '@/src/api/menu.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { formatCurrency } from '@/src/utils/currency';
import { colors } from '@/src/theme';

export default function MenuItemDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const query = useQuery({
    queryKey: ['menu-item', id],
    queryFn: () => getMenuItem(id),
    enabled: Boolean(id),
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading item..." />;
  }

  if (query.isError || !query.data) {
    return <ErrorState message="Unable to load this item." onRetry={() => void query.refetch()} />;
  }

  const item = query.data;

  return (
    <AppScreen>
      <AppCard>
        <AppBadge label={item.category?.name ?? 'Menu item'} tone={item.is_featured ? 'gold' : 'neutral'} />
        <AppText variant="h1">{item.name}</AppText>
        <AppText color={colors.text.secondary}>{item.description ?? 'Freshly prepared for every order.'}</AppText>
        <AppText variant="h2" color={colors.gold.dark}>
          {formatCurrency(item.price)}
        </AppText>
        <AppText color={colors.text.secondary}>
          Sizes and add-ons are available through the Laravel API and ready for the next cart integration phase.
        </AppText>
        <AppButton label="Login to order" />
      </AppCard>
    </AppScreen>
  );
}
