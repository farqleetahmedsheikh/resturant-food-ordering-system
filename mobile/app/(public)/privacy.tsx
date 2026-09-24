import { LegalScreen } from '@/src/components/legal/LegalScreen';
import { privacySections } from '@/src/legal/legalContent';

export default function PrivacyScreen() {
  return (
    <LegalScreen
      title="Privacy Policy"
      subtitle="How Arcade Kebab House handles information for accounts, orders, Stripe payments, delivery, and support."
      sections={privacySections}
    />
  );
}
