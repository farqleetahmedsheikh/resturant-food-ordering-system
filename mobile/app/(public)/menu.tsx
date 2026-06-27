import { useQuery } from '@tanstack/react-query';

import { getMenuItems } from '@/src/api/menu.api';
import { MenuItemCard } from '@/src/components/menu/MenuItemCard';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';

export default function PublicMenuScreen() {
  const menuQuery = useQuery({
    queryKey: queryKeys.menuItems(),
    queryFn: () => getMenuItems(),
  });

  if (menuQuery.isLoading) {
    return <LoadingScreen label="Loading menu..." />;
  }

  if (menuQuery.isError) {
    return <ErrorState message="Unable to load menu items." onRetry={() => void menuQuery.refetch()} />;
  }

  const items = menuQuery.data?.items ?? [];

  return (
    <AppScreen
      contentStyle={{ gap: 16 }}
      scroll
    >
      <AppHeader title="Menu" subtitle="Browse available items from the Laravel API." eyebrow="Public" />
      {items.length === 0 ? (
        <EmptyState title="No items yet" message="Menu items will appear here once they are available." />
      ) : (
        items.map((item) => <MenuItemCard key={item.id} item={item} />)
      )}
    </AppScreen>
  );
}
