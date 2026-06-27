import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';

import { normalizeApiError } from '@/src/api/api-error';
import { useAuthStore } from '@/src/auth/auth.store';
import { registerSchema, type RegisterFormValues } from '@/src/auth/auth.validation';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { PasswordInput } from '@/src/components/forms/PasswordInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { colors, spacing } from '@/src/theme';

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
    <AppScreen keyboard>
      <AppHeader title="Create account" subtitle="Customer registration is connected to Laravel Sanctum." eyebrow="Customer" />
      <AppCard style={{ gap: spacing.lg }}>
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
              <AppCheckbox checked={field.value} onChange={field.onChange} label="I agree to continue with this demo customer account." />
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
        <Link href="/(auth)/login">
          <AppText color={colors.text.secondary}>Already have an account? Sign in</AppText>
        </Link>
      </AppCard>
    </AppScreen>
  );
}
