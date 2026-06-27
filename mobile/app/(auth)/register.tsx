import { zodResolver } from '@hookform/resolvers/zod';
import { router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';
import { z } from 'zod';

import { normalizeApiError } from '@/src/api/api-error';
import { useAuthStore } from '@/src/auth/auth.store';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { colors, spacing } from '@/src/theme';
import { emailSchema, passwordSchema } from '@/src/utils/validation';

const schema = z
  .object({
    name: z.string().trim().min(2, 'Enter your full name.'),
    email: emailSchema,
    phone: z.string().trim().optional(),
    password: passwordSchema,
    password_confirmation: z.string().min(8, 'Confirm your password.'),
    accepted: z.boolean().refine((value) => value, 'Please acknowledge the account terms placeholder.'),
  })
  .refine((value) => value.password === value.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Passwords do not match.',
  });

type RegisterForm = z.infer<typeof schema>;

export default function RegisterScreen() {
  const register = useAuthStore((state) => state.register);
  const form = useForm<RegisterForm>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      accepted: false,
    },
  });

  async function onSubmit(values: RegisterForm) {
    try {
      const session = await register(values);
      router.replace(getRoleRedirect(session.user, env.enableAdminMobile));
    } catch (error) {
      const normalized = normalizeApiError(error);
      Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
        form.setError(field as keyof RegisterForm, { message: messages[0] });
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
            <AppInput
              label="Password"
              value={field.value}
              onChangeText={field.onChange}
              secureTextEntry
              secureToggle
              error={fieldState.error?.message}
            />
          )}
        />
        <Controller
          control={form.control}
          name="password_confirmation"
          render={({ field, fieldState }) => (
            <AppInput
              label="Confirm password"
              value={field.value}
              onChangeText={field.onChange}
              secureTextEntry
              secureToggle
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
          <AppText color={colors.semantic.danger}>{form.formState.errors.root.message}</AppText>
        ) : null}
        <AppButton
          label="Create Account"
          loading={form.formState.isSubmitting}
          fullWidth
          onPress={form.handleSubmit(onSubmit)}
        />
      </AppCard>
    </AppScreen>
  );
}
