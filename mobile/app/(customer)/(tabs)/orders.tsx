import { useQuery } from '@tanstack/react-query';

import { getCustomerOrders } from '@/src/api/orders.api';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { OrderRow } from '@/src/components/orders/OrderRow';
import { queryKeys } from '@/src/constants/queryKeys';

export default function CustomerOrdersScreen() {
  const query = useQuery({
    queryKey: queryKeys.customerOrders,
    queryFn: getCustomerOrders,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading orders..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load customer orders." onRetry={() => void query.refetch()} />;
  }

  const orders = query.data ?? [];

  return (
    <AppScreen refreshing={query.isRefetching} onRefresh={() => void query.refetch()}>
      <AppHeader title="My orders" subtitle="Customer orders are loaded from the authenticated API." eyebrow="Customer" />
      {orders.length === 0 ? (
        <EmptyState title="No orders yet" message="Your orders will appear here after checkout." />
      ) : (
        orders.map((order) => <OrderRow key={order.id} order={order} href={`/(customer)/orders/${order.id}`} />)
      )}
    </AppScreen>
  );
}
