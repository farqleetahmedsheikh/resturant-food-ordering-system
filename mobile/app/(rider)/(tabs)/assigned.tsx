import { useQuery } from '@tanstack/react-query';

import { getRiderDeliveries } from '@/src/api/deliveries.api';
import { DeliveryRow } from '@/src/components/deliveries/DeliveryRow';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';

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
    <AppScreen>
      <AppHeader title="Assigned deliveries" subtitle="Only deliveries assigned to this rider are returned by Laravel." eyebrow="Rider" />
      {deliveries.length === 0 ? (
        <EmptyState title="No active deliveries" message="Assigned orders will appear here." />
      ) : (
        deliveries.map((delivery) => <DeliveryRow key={delivery.id} delivery={delivery} />)
      )}
    </AppScreen>
  );
}
