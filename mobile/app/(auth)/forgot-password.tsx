import { zodResolver } from '@hookform/resolvers/zod';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Linking } from 'react-native';
import { z } from 'zod';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppInput } from '@/src/components/forms/AppInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, spacing } from '@/src/theme';
import { emailSchema } from '@/src/utils/validation';

const schema = z.object({
  email: emailSchema,
});

type ForgotPasswordForm = z.infer<typeof schema>;

export default function ForgotPasswordScreen() {
  const [submittedEmail, setSubmittedEmail] = useState<string | null>(null);
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const form = useForm<ForgotPasswordForm>({
    resolver: zodResolver(schema),
    defaultValues: {
      email: '',
    },
  });

  function onSubmit(values: ForgotPasswordForm) {
    setSubmittedEmail(values.email);
  }

  const supportEmail = restaurantQuery.data?.email;

  return (
    <AppScreen keyboard>
      <AppHeader title="Forgot password" subtitle="Mobile password reset API is not enabled yet." eyebrow="Account help" />
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
        <AppButton
          label="Check reset options"
          loading={form.formState.isSubmitting}
          fullWidth
          onPress={form.handleSubmit(onSubmit)}
        />
        {submittedEmail ? (
          <FeedbackMessage
            tone="warning"
            message={`Password reset for ${submittedEmail} is available on the web flow. Contact the restaurant if you need help.`}
          />
        ) : null}
        {supportEmail ? (
          <AppButton
            label="Email restaurant"
            variant="outline"
            fullWidth
            onPress={() => void Linking.openURL(`mailto:${supportEmail}`)}
          />
        ) : null}
        <Link href="/(auth)/login">
          <AppText color={colors.text.secondary}>Back to login</AppText>
        </Link>
      </AppCard>
    </AppScreen>
  );
}
