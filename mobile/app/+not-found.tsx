import { Link } from 'expo-router';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors } from '@/src/theme';

export default function NotFoundScreen() {
  return (
    <AppScreen>
      <AppCard>
        <AppText variant="h2">Screen not found</AppText>
        <AppText color={colors.text.secondary}>This mobile route is not available.</AppText>
        <Link href="/(public)" asChild>
          <AppButton label="Back Home" />
        </Link>
      </AppCard>
    </AppScreen>
  );
}
