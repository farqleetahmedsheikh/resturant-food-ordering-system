import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';
import { Pressable, StyleSheet, View } from 'react-native';

import { normalizeApiError } from '@/src/api/api-error';
import { useAuthStore } from '@/src/auth/auth.store';
import { loginSchema, type LoginFormValues } from '@/src/auth/auth.validation';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { AppInput } from '@/src/components/forms/AppInput';
import { PasswordInput } from '@/src/components/forms/PasswordInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { colors, radius, shadows, spacing } from '@/src/theme';

const accessOptions = [
  'Customer ordering',
  'Rider deliveries',
  'Admin operations',
];

export default function LoginScreen() {
  const login = useAuthStore((state) => state.login);
  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  });

  async function onSubmit(values: LoginFormValues) {
    try {
      const session = await login(values);
      router.replace(getRoleRedirect(session.user, env.enableAdminMobile));
    } catch (error) {
      const normalized = normalizeApiError(error);
      Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
        form.setError(field as keyof LoginFormValues, { message: messages[0] });
      });
      form.setError('root', { message: normalized.message });
    }
  }

  return (
    <AppScreen keyboard contentStyle={styles.screen}>
      <View style={styles.topNav}>
        <Link href="/(public)" asChild>
          <Pressable style={({ pressed }) => [styles.navButton, pressed && styles.pressed]} accessibilityRole="link">
            <AppText variant="caption" color={colors.brand.primary}>
              Home
            </AppText>
          </Pressable>
        </Link>
        <Link href="/(auth)/register" asChild>
          <Pressable style={({ pressed }) => [styles.navButton, styles.navButtonAccent, pressed && styles.pressed]} accessibilityRole="link">
            <AppText variant="caption" color={colors.brand.primary}>
              Sign up
            </AppText>
          </Pressable>
        </Link>
      </View>

      <View style={styles.hero}>
        <RestaurantLogo name="Arcade Kebab House" initials="AK" size={70} inverse />
        <View style={styles.heroCopy}>
          <AppText variant="h1" color={colors.text.inverse}>
            Welcome back
          </AppText>
          <AppText color="#F8F1EB">
            Sign in once to order faster, manage deliveries, or open the admin workspace.
          </AppText>
        </View>
        <View style={styles.accessRow}>
          {accessOptions.map((option) => (
            <View key={option} style={styles.accessPill}>
              <AppText variant="caption" color="#F8F1EB">
                {option}
              </AppText>
            </View>
          ))}
        </View>
      </View>

      <AppCard style={styles.formCard}>
        <View style={styles.cardHeader}>
          <AppText variant="h2">Login</AppText>
          <AppText color={colors.text.secondary}>Use your registered email and password.</AppText>
        </View>
        <Controller
          control={form.control}
          name="email"
          render={({ field, fieldState }) => (
            <AppInput
              label="Email"
              value={field.value}
              onChangeText={field.onChange}
              onBlur={field.onBlur}
              keyboardType="email-address"
              autoComplete="email"
              textContentType="emailAddress"
              autoCapitalize="none"
              error={fieldState.error?.message}
            />
          )}
        />
        <Controller
          control={form.control}
          name="password"
          render={({ field, fieldState }) => (
            <PasswordInput
              label="Password"
              value={field.value}
              onChangeText={field.onChange}
              onBlur={field.onBlur}
              autoComplete="password"
              error={fieldState.error?.message}
            />
          )}
        />
        {form.formState.errors.root ? (
          <FeedbackMessage tone="error" message={form.formState.errors.root.message ?? 'Unable to login.'} />
        ) : null}
        <AppButton
          label="Login"
          loading={form.formState.isSubmitting}
          fullWidth
          onPress={form.handleSubmit(onSubmit)}
        />
        <View style={styles.authLinks}>
          <Link href="/(auth)/forgot-password">
            <AppText color={colors.brand.primary}>Forgot password?</AppText>
          </Link>
          <Link href="/(auth)/register">
            <AppText color={colors.text.secondary}>Create customer account</AppText>
          </Link>
        </View>
      </AppCard>
    </AppScreen>
  );
}

const styles = StyleSheet.create({
  screen: {
    alignSelf: 'center',
    width: '100%',
    maxWidth: 760,
  },
  topNav: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  navButton: {
    minHeight: 42,
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.lg,
  },
  navButtonAccent: {
    backgroundColor: colors.brand.soft,
  },
  hero: {
    gap: spacing.lg,
    borderRadius: 30,
    backgroundColor: colors.surface.dark,
    padding: spacing['2xl'],
    ...shadows.card,
  },
  heroCopy: {
    gap: spacing.sm,
  },
  accessRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  accessPill: {
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.16)',
    backgroundColor: 'rgba(255,255,255,0.08)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  formCard: {
    gap: spacing.lg,
  },
  cardHeader: {
    gap: spacing.xs,
  },
  authLinks: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
