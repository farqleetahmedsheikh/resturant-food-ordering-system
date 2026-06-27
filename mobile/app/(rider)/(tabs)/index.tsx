import { useQuery } from '@tanstack/react-query';

import { getRiderDashboard } from '@/src/api/deliveries.api';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors } from '@/src/theme';

export default function RiderDashboardScreen() {
  const query = useQuery({
    queryKey: queryKeys.riderDashboard,
    queryFn: getRiderDashboard,
  });

  if (query.isLoading) {
    return <LoadingScreen label="Loading rider dashboard..." />;
  }

  if (query.isError) {
    return <ErrorState message="Unable to load rider dashboard." onRetry={() => void query.refetch()} />;
  }

  return (
    <AppScreen>
      <AppHeader title="Rider dashboard" subtitle="Assigned delivery workflow is connected to Laravel." eyebrow="Rider" />
      <AppCard>
        <AppText variant="title">Delivery summary</AppText>
        <AppText color={colors.text.secondary}>
          Dashboard payload received. Detailed stat cards can be shaped in the next feature phase.
        </AppText>
      </AppCard>
    </AppScreen>
  );
}
