import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { View } from 'react-native';
import { z } from 'zod';

import { normalizeApiError } from '@/src/api/api-error';
import { requestPasswordOtp, resetPasswordWithOtp, verifyPasswordOtp } from '@/src/api/auth.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppInput } from '@/src/components/forms/AppInput';
import { PasswordInput } from '@/src/components/forms/PasswordInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors, spacing } from '@/src/theme';
import { emailSchema, passwordSchema } from '@/src/utils/validation';

const schema = z
  .object({
    email: emailSchema,
    otp: z.string().trim().regex(/^\d{6}$/, 'Enter the 6 digit OTP.'),
    password: passwordSchema,
    password_confirmation: z.string().min(8, 'Confirm your password.'),
  })
  .refine((value) => value.password === value.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Passwords do not match.',
  });

type ForgotPasswordForm = z.infer<typeof schema>;
type Step = 'request' | 'verify' | 'reset' | 'done';

export default function ForgotPasswordScreen() {
  const [step, setStep] = useState<Step>('request');
  const [status, setStatus] = useState<string | null>(null);
  const form = useForm<ForgotPasswordForm>({
    resolver: zodResolver(schema),
    defaultValues: {
      email: '',
      otp: '',
      password: '',
      password_confirmation: '',
    },
  });

  async function requestOtp() {
    const valid = await form.trigger('email');

    if (!valid) {
      return;
    }

    try {
      await requestPasswordOtp(form.getValues('email'));
      setStatus('If this email exists, an OTP has been sent.');
      setStep('verify');
    } catch (error) {
      applyApiError(error);
    }
  }

  async function verifyOtp() {
    const valid = await form.trigger(['email', 'otp']);

    if (!valid) {
      return;
    }

    try {
      await verifyPasswordOtp(form.getValues('email'), form.getValues('otp'));
      setStatus('OTP verified. Create your new password.');
      setStep('reset');
    } catch (error) {
      applyApiError(error);
    }
  }

  async function resetPassword(values: ForgotPasswordForm) {
    try {
      await resetPasswordWithOtp(values);
      setStatus('Password reset successfully. Please login with your new password.');
      setStep('done');
    } catch (error) {
      applyApiError(error);
    }
  }

  function applyApiError(error: unknown) {
    const normalized = normalizeApiError(error);
    Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
      form.setError(field as keyof ForgotPasswordForm, { message: messages[0] });
    });
    form.setError('root', { message: normalized.message });
  }

  return (
    <AppScreen keyboard>
      <AppHeader title="Reset password" subtitle="Use the OTP sent to your account email." eyebrow="Account help" />
      <AppCard style={{ gap: spacing.lg }}>
        {status ? <FeedbackMessage tone={step === 'done' ? 'success' : 'info'} message={status} /> : null}
        {form.formState.errors.root ? (
          <FeedbackMessage tone="error" message={form.formState.errors.root.message ?? 'Unable to reset password.'} />
        ) : null}

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
              editable={step === 'request'}
              error={fieldState.error?.message}
            />
          )}
        />

        {step !== 'request' && step !== 'done' ? (
          <Controller
            control={form.control}
            name="otp"
            render={({ field, fieldState }) => (
              <AppInput
                label="OTP"
                value={field.value}
                onChangeText={field.onChange}
                onBlur={field.onBlur}
                keyboardType="number-pad"
                maxLength={6}
                error={fieldState.error?.message}
              />
            )}
          />
        ) : null}

        {step === 'reset' ? (
          <View style={{ gap: spacing.lg }}>
            <Controller
              control={form.control}
              name="password"
              render={({ field, fieldState }) => (
                <PasswordInput
                  label="New password"
                  value={field.value}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
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
                  onBlur={field.onBlur}
                  error={fieldState.error?.message}
                />
              )}
            />
          </View>
        ) : null}

        {step === 'request' ? (
          <AppButton label="Send OTP" loading={form.formState.isSubmitting} fullWidth onPress={() => void requestOtp()} />
        ) : null}
        {step === 'verify' ? (
          <View style={{ gap: spacing.md }}>
            <AppButton label="Verify OTP" loading={form.formState.isSubmitting} fullWidth onPress={() => void verifyOtp()} />
            <AppButton label="Resend OTP" variant="outline" fullWidth onPress={() => void requestOtp()} />
          </View>
        ) : null}
        {step === 'reset' ? (
          <AppButton
            label="Reset password"
            loading={form.formState.isSubmitting}
            fullWidth
            onPress={form.handleSubmit(resetPassword)}
          />
        ) : null}
        {step === 'done' ? (
          <AppButton label="Back to login" fullWidth onPress={() => router.replace('/(auth)/login')} />
        ) : null}

        <Link href="/(auth)/login">
          <AppText color={colors.text.secondary}>Back to login</AppText>
        </Link>
      </AppCard>
    </AppScreen>
  );
}
