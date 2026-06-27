import { Link } from 'expo-router';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { useAuthStore } from '@/src/auth/auth.store';
import { colors } from '@/src/theme';

export default function CustomerDashboardScreen() {
  const user = useAuthStore((state) => state.session?.user);

  return (
    <AppScreen>
      <AppHeader title={`Hi, ${user?.name ?? 'customer'}`} subtitle="Your ordering dashboard is ready." eyebrow="Customer" />
      <AppCard>
        <AppText variant="title">Start an order</AppText>
        <AppText color={colors.text.secondary}>
          Browse the live menu from Laravel. Cart and checkout are intentionally left as the next integration phase.
        </AppText>
        <Link href="/(customer)/(tabs)/menu" asChild>
          <AppButton label="View Menu" />
        </Link>
      </AppCard>
    </AppScreen>
  );
}
