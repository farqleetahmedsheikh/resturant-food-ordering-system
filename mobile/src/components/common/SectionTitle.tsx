import { StyleSheet, View } from 'react-native';

import { AppText } from './AppText';
import { colors, spacing } from '@/src/theme';

type SectionTitleProps = {
  title: string;
  subtitle?: string;
};

export function SectionTitle({ title, subtitle }: SectionTitleProps) {
  return (
    <View style={styles.wrapper}>
      <AppText variant="title">{title}</AppText>
      {subtitle ? <AppText color={colors.text.secondary}>{subtitle}</AppText> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: spacing.xs,
  },
});
