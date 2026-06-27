import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';

import { normalizeApiError } from '@/src/api/api-error';
import { useAuthStore } from '@/src/auth/auth.store';
import { loginSchema, type LoginFormValues } from '@/src/auth/auth.validation';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppInput } from '@/src/components/forms/AppInput';
import { PasswordInput } from '@/src/components/forms/PasswordInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { colors, spacing } from '@/src/theme';

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
    <AppScreen keyboard>
      <AppHeader title="Login" subtitle="Use your customer, rider, or admin account." eyebrow="Secure access" />
      <AppCard style={{ gap: spacing.lg }}>
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
        <Link href="/(auth)/forgot-password">
          <AppText color={colors.brand.primary}>Forgot password?</AppText>
        </Link>
        <Link href="/(auth)/register">
          <AppText color={colors.text.secondary}>Create a customer account</AppText>
        </Link>
      </AppCard>
    </AppScreen>
  );
}
