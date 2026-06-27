import { useQuery } from '@tanstack/react-query';

import { getAdminOrders } from '@/src/api/orders.api';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderRow } from '@/src/components/orders/OrderRow';
import { queryKeys } from '@/src/constants/queryKeys';

export default function AdminOrdersScreen() {
  const query = useQuery({
    queryKey: queryKeys.adminOrders,
    queryFn: getAdminOrders,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading orders..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load admin orders." onRetry={() => void query.refetch()} />;
  }

  const orders = query.data ?? [];

  return (
    <AppScreen>
      <AppHeader title="Orders" subtitle="Read-only mobile overview for admins." eyebrow="Admin" />
      {orders.length === 0 ? (
        <EmptyState title="No orders" message="Orders will appear here when available." />
      ) : (
        orders.map((order) => <OrderRow key={order.id} order={order} />)
      )}
    </AppScreen>
  );
}
