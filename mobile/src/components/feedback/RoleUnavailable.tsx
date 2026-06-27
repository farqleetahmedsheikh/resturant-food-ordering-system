import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { useAuthStore } from '@/src/auth/auth.store';
import { colors } from '@/src/theme';

export function RoleUnavailable({
  title,
  message,
}: {
  title: string;
  message: string;
}) {
  const logout = useAuthStore((state) => state.logout);

  return (
    <AppScreen>
      <AppCard>
        <AppText variant="h2">{title}</AppText>
        <AppText color={colors.text.secondary}>{message}</AppText>
        <AppButton label="Sign out" variant="outline" onPress={() => void logout()} />
      </AppCard>
    </AppScreen>
  );
}
