import { Link } from 'expo-router';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors } from '@/src/theme';

export default function AdminDashboardScreen() {
  return (
    <AppScreen>
      <AppHeader title="Mobile Admin" subtitle="Feature-flagged admin shell for quick monitoring." eyebrow="Admin" />
      <AppCard>
        <AppText variant="title">Web dashboard remains primary</AppText>
        <AppText color={colors.text.secondary}>
          This mobile admin surface is intentionally minimal. Full restaurant administration stays in Laravel web.
        </AppText>
        <Link href="/(admin)/orders" asChild>
          <AppButton label="View Orders" />
        </Link>
      </AppCard>
    </AppScreen>
  );
}
