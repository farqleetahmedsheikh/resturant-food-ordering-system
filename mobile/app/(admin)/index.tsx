import { Link } from 'expo-router';
import { Linking, StyleSheet } from 'react-native';

import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { useAuthStore } from '@/src/auth/auth.store';
import { env } from '@/src/config/env';
import { colors } from '@/src/theme';

export default function AdminDashboardScreen() {
  const logout = useAuthStore((state) => state.logout);

  if (!env.enableAdminMobile) {
    return (
      <AppScreen>
        <AppHeader title="Mobile admin is not enabled yet" subtitle="Use the web dashboard for full administration." eyebrow="Admin" />
        <AppCard style={styles.card}>
          <AppText color={colors.text.secondary}>
            Admin data is not exposed in this mobile phase. Enable EXPO_PUBLIC_ENABLE_ADMIN_MOBILE only when a dedicated mobile admin workflow is ready.
          </AppText>
          {env.webAdminUrl ? (
            <AppButton
              label="Open web dashboard"
              onPress={() => void Linking.openURL(env.webAdminUrl as string)}
            />
          ) : null}
          <AppButton label="Logout" variant="outline" onPress={() => void logout()} />
        </AppCard>
      </AppScreen>
    );
  }

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

const styles = StyleSheet.create({
  card: {
    gap: 16,
  },
});
