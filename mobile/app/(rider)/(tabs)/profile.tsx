import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { useAuthStore } from '@/src/auth/auth.store';
import { colors } from '@/src/theme';

export default function RiderProfileScreen() {
  const user = useAuthStore((state) => state.session?.user);
  const logout = useAuthStore((state) => state.logout);

  return (
    <AppScreen>
      <AppHeader title="Rider profile" subtitle="Delivery account details." eyebrow="Rider" />
      <AppCard>
        <AppText variant="title">{user?.name}</AppText>
        <AppText color={colors.text.secondary}>{user?.email}</AppText>
        <AppText color={colors.text.secondary}>{user?.phone ?? 'No phone added'}</AppText>
        <AppButton label="Logout" variant="outline" onPress={() => void logout()} />
      </AppCard>
    </AppScreen>
  );
}
