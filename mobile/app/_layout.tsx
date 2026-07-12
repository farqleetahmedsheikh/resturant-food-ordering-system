import { Stack, type ErrorBoundaryProps } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { StatusBar } from 'expo-status-bar';
import { useEffect } from 'react';
import { StyleSheet, View } from 'react-native';

import { useRoleGuards } from '@/src/auth/auth.guard';
import { AppButton } from '@/src/components/common/AppButton';
import { AppText } from '@/src/components/common/AppText';
import { AppProviders } from '@/src/providers/AppProviders';
import { colors, spacing } from '@/src/theme';

void SplashScreen.preventAutoHideAsync().catch(() => undefined);

function hideNativeSplash() {
  void SplashScreen.hideAsync().catch(() => undefined);
}

function RootNavigator() {
  const guards = useRoleGuards();

  return (
    <>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="index" />
        <Stack.Screen name="(public)" />
        <Stack.Protected guard={guards.guest}>
          <Stack.Screen name="(auth)" />
        </Stack.Protected>
        <Stack.Protected guard={guards.customer}>
          <Stack.Screen name="(customer)" />
        </Stack.Protected>
        <Stack.Protected guard={guards.rider}>
          <Stack.Screen name="(rider)" />
        </Stack.Protected>
        <Stack.Protected guard={guards.admin}>
          <Stack.Screen name="(admin)" />
        </Stack.Protected>
        <Stack.Protected guard={guards.adminUnavailable}>
          <Stack.Screen name="admin-unavailable" />
        </Stack.Protected>
        <Stack.Protected guard={guards.unsupported}>
          <Stack.Screen name="unsupported-role" />
        </Stack.Protected>
        <Stack.Screen name="+not-found" />
      </Stack>
    </>
  );
}

export default function RootLayout() {
  useEffect(() => {
    hideNativeSplash();

    const fallback = setTimeout(hideNativeSplash, 2000);

    return () => {
      clearTimeout(fallback);
    };
  }, []);

  return (
    <AppProviders>
      <RootNavigator />
    </AppProviders>
  );
}

export function ErrorBoundary({ error, retry }: ErrorBoundaryProps) {
  useEffect(() => {
    hideNativeSplash();
  }, []);

  return (
    <View style={styles.errorScreen}>
      <StatusBar style="dark" />
      <AppText variant="caption" color={colors.brand.primary}>
        ARCADE KEBAB HOUSE
      </AppText>
      <AppText variant="h2">Unable to open the app</AppText>
      <AppText color={colors.text.secondary} style={styles.errorMessage}>
        {error.message || 'The app hit an unexpected startup error.'}
      </AppText>
      <AppButton label="Try Again" variant="secondary" onPress={() => void retry()} />
    </View>
  );
}

const styles = StyleSheet.create({
  errorScreen: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.md,
    backgroundColor: colors.surface.page,
    padding: spacing.xl,
  },
  errorMessage: {
    textAlign: 'center',
  },
});
