import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppHeader } from './AppHeader';
import { AppScreen } from './AppScreen';
import { colors } from '@/src/theme';

type PlaceholderScreenProps = {
  title: string;
  subtitle: string;
  eyebrow?: string;
  actionLabel?: string;
  onAction?: () => void;
};

export function PlaceholderScreen({
  title,
  subtitle,
  eyebrow,
  actionLabel,
  onAction,
}: PlaceholderScreenProps) {
  return (
    <AppScreen>
      <AppHeader title={title} subtitle={subtitle} eyebrow={eyebrow} />
      <AppCard>
        <AppText variant="title">Foundation ready</AppText>
        <AppText color={colors.text.secondary}>
          This screen is wired into the role navigation and ready for the next feature phase.
        </AppText>
        {actionLabel && onAction ? <AppButton label={actionLabel} onPress={onAction} /> : null}
      </AppCard>
    </AppScreen>
  );
}
