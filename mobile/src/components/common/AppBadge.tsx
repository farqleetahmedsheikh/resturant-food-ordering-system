import { StyleSheet, View } from 'react-native';

import { AppText } from './AppText';
import { colors, radius, spacing } from '@/src/theme';

type AppBadgeProps = {
  label: string;
  tone?: 'brand' | 'gold' | 'green' | 'danger' | 'info' | 'neutral';
};

const tones = {
  brand: { bg: colors.brand.soft, fg: colors.brand.primary },
  gold: { bg: colors.gold.soft, fg: colors.gold.dark },
  green: { bg: colors.green.soft, fg: colors.green.dark },
  danger: { bg: colors.brand.soft, fg: colors.semantic.danger },
  info: { bg: '#EFF6FF', fg: colors.semantic.info },
  neutral: { bg: colors.surface.muted, fg: colors.text.secondary },
};

export function AppBadge({ label, tone = 'neutral' }: AppBadgeProps) {
  return (
    <View style={[styles.badge, { backgroundColor: tones[tone].bg }]}>
      <AppText variant="caption" color={tones[tone].fg}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    borderRadius: radius.pill,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
  },
});
