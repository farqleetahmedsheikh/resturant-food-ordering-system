import { PropsWithChildren, ReactNode } from 'react';
import { KeyboardAvoidingView, Platform, RefreshControl, ScrollView, StyleSheet } from 'react-native';
import type { StyleProp, ViewStyle } from 'react-native';
import { useSegments } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useAuthStore } from '@/src/auth/auth.store';
import { AdminQuickActionBar } from './AdminQuickActionBar';
import { AppNavigationHeader } from './AppNavigationHeader';
import { colors, spacing } from '@/src/theme';

type AppScreenProps = PropsWithChildren<{
  scroll?: boolean;
  keyboard?: boolean;
  contentStyle?: StyleProp<ViewStyle>;
  header?: ReactNode;
  footer?: ReactNode;
  refreshing?: boolean;
  onRefresh?: () => void;
}>;

export function AppScreen({
  children,
  scroll = true,
  keyboard = false,
  contentStyle,
  header,
  footer,
  refreshing,
  onRefresh,
}: AppScreenProps) {
  const segments = useSegments().map(String);
  const user = useAuthStore((state) => state.session?.user ?? null);
  const resolvedHeader = header === undefined ? <AppNavigationHeader /> : header;
  const resolvedFooter = footer ?? (user?.role === 'admin' && segments.includes('(admin)') ? <AdminQuickActionBar /> : null);
  const content = scroll ? (
    <ScrollView
      style={styles.flex}
      keyboardShouldPersistTaps="handled"
      contentContainerStyle={[styles.content, contentStyle]}
      refreshControl={
        onRefresh ? <RefreshControl refreshing={Boolean(refreshing)} onRefresh={onRefresh} /> : undefined
      }
      showsVerticalScrollIndicator={false}
    >
      {children}
    </ScrollView>
  ) : (
    children
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      {keyboard ? (
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.flex}
        >
          {resolvedHeader}
          {content}
          {resolvedFooter}
        </KeyboardAvoidingView>
      ) : (
        <>
          {resolvedHeader}
          {content}
          {resolvedFooter}
        </>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.surface.page,
  },
  flex: {
    flex: 1,
  },
  content: {
    gap: spacing.lg,
    padding: spacing.lg,
    paddingBottom: spacing['4xl'],
  },
});
