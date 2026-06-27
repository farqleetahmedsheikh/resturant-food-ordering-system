import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

import { useRoleGuards } from '@/src/auth/auth.guard';
import { AppProviders } from '@/src/providers/AppProviders';

function RootNavigator() {
  const guards = useRoleGuards();

  return (
    <>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="index" />
        <Stack.Protected guard={guards.guest}>
          <Stack.Screen name="(public)" />
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
  return (
    <AppProviders>
      <RootNavigator />
    </AppProviders>
  );
}
