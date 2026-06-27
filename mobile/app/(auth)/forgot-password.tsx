import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { colors } from '@/src/theme';

export default function ForgotPasswordScreen() {
  return (
    <AppScreen>
      <AppHeader title="Forgot password" subtitle="This foundation is ready for the Laravel OTP reset flow." eyebrow="Account help" />
      <AppCard>
        <AppText variant="title">Password reset placeholder</AppText>
        <AppText color={colors.text.secondary}>
          The web app already has OTP password reset screens. The mobile API endpoint can be connected in the next phase without changing the auth architecture.
        </AppText>
      </AppCard>
    </AppScreen>
  );
}
