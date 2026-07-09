import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';
import { Pressable, StyleSheet, View } from 'react-native';

import { normalizeApiError } from '@/src/api/api-error';
import { useAuthStore } from '@/src/auth/auth.store';
import { registerSchema, type RegisterFormValues } from '@/src/auth/auth.validation';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { PasswordInput } from '@/src/components/forms/PasswordInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { colors, radius, shadows, spacing } from '@/src/theme';

export default function RegisterScreen() {
  const register = useAuthStore((state) => state.register);
  const form = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      accepted: false,
    },
  });

  async function onSubmit(values: RegisterFormValues) {
    try {
      const session = await register(values);
      router.replace(getRoleRedirect(session.user, env.enableAdminMobile));
    } catch (error) {
      const normalized = normalizeApiError(error);
      Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
        form.setError(field as keyof RegisterFormValues, { message: messages[0] });
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
        <Link href="/(auth)/login" asChild>
          <Pressable style={({ pressed }) => [styles.navButton, styles.navButtonAccent, pressed && styles.pressed]} accessibilityRole="link">
            <AppText variant="caption" color={colors.brand.primary}>
              Login
            </AppText>
          </Pressable>
        </Link>
      </View>

      <View style={styles.hero}>
        <RestaurantLogo name="Arcade Kebab House" initials="AK" size={70} inverse />
        <View style={styles.heroCopy}>
          <AppText variant="h1" color={colors.text.inverse}>
            Create your account
          </AppText>
          <AppText color="#F8F1EB">
            Save your details, checkout faster, and keep your Arcade Kebab House orders together.
          </AppText>
        </View>
      </View>

      <AppCard style={styles.formCard}>
        <View style={styles.cardHeader}>
          <AppText variant="h2">Sign up</AppText>
          <AppText color={colors.text.secondary}>Customer accounts are for ordering and order history.</AppText>
        </View>
        {(['name', 'email', 'phone'] as const).map((name) => (
          <Controller
            key={name}
            control={form.control}
            name={name}
            render={({ field, fieldState }) => (
              <AppInput
                label={name === 'name' ? 'Full name' : name === 'email' ? 'Email' : 'Phone'}
                value={field.value}
                onChangeText={field.onChange}
                onBlur={field.onBlur}
                keyboardType={name === 'email' ? 'email-address' : name === 'phone' ? 'phone-pad' : 'default'}
                textContentType={name === 'email' ? 'emailAddress' : name === 'phone' ? 'telephoneNumber' : 'name'}
                autoCapitalize={name === 'email' ? 'none' : 'words'}
                error={fieldState.error?.message}
              />
            )}
          />
        ))}
        <Controller
          control={form.control}
          name="password"
          render={({ field, fieldState }) => (
            <PasswordInput
              label="Password"
              value={field.value}
              onChangeText={field.onChange}
              error={fieldState.error?.message}
            />
          )}
        />
        <Controller
          control={form.control}
          name="password_confirmation"
          render={({ field, fieldState }) => (
            <PasswordInput
              label="Confirm password"
              value={field.value}
              onChangeText={field.onChange}
              error={fieldState.error?.message}
            />
          )}
        />
        <Controller
          control={form.control}
          name="accepted"
          render={({ field, fieldState }) => (
            <>
              <AppCheckbox checked={field.value} onChange={field.onChange} label="I agree to create an Arcade Kebab House customer account." />
              {fieldState.error ? <AppText color={colors.semantic.danger}>{fieldState.error.message}</AppText> : null}
            </>
          )}
        />
        {form.formState.errors.root ? (
          <FeedbackMessage tone="error" message={form.formState.errors.root.message ?? 'Unable to register.'} />
        ) : null}
        <AppButton
          label="Create Account"
          loading={form.formState.isSubmitting}
          fullWidth
          onPress={form.handleSubmit(onSubmit)}
        />
        <View style={styles.authLinks}>
          <Link href="/(auth)/login">
            <AppText color={colors.text.secondary}>Already have an account? Sign in</AppText>
          </Link>
          <Link href="/(public)/menu">
            <AppText color={colors.brand.primary}>Browse menu first</AppText>
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
