import { LegalScreen } from '@/src/components/legal/LegalScreen';
import { termsSections } from '@/src/legal/legalContent';

export default function TermsScreen() {
  return (
    <LegalScreen
      title="Terms and Conditions"
      subtitle="Rules for using the website and app, placing orders, paying through Stripe, and receiving delivery or pickup."
      sections={termsSections}
    />
  );
}
