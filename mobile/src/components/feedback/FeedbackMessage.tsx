import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, radius, spacing } from '@/src/theme';

type FeedbackMessageProps = {
  message: string;
  tone?: 'success' | 'error' | 'warning' | 'info';
};

const tones = {
  success: { bg: colors.green.soft, fg: colors.semantic.success },
  error: { bg: colors.brand.soft, fg: colors.semantic.danger },
  warning: { bg: colors.gold.soft, fg: colors.semantic.warning },
  info: { bg: '#EFF6FF', fg: colors.semantic.info },
};

export function FeedbackMessage({ message, tone = 'info' }: FeedbackMessageProps) {
  return (
    <View style={[styles.wrapper, { backgroundColor: tones[tone].bg }]}>
      <AppText color={tones[tone].fg}>{message}</AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    borderRadius: radius.lg,
    padding: spacing.md,
  },
});
