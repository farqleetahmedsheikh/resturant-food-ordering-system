import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

type AppHeaderProps = {
  title: string;
  subtitle?: string;
  eyebrow?: string;
};

export function AppHeader({ title, subtitle, eyebrow }: AppHeaderProps) {
  return (
    <View style={styles.wrapper}>
      {eyebrow ? (
        <AppText variant="caption" color={colors.brand.primary}>
          {eyebrow.toUpperCase()}
        </AppText>
      ) : null}
      <AppText variant="h1">{title}</AppText>
      {subtitle ? <AppText color={colors.text.secondary}>{subtitle}</AppText> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: spacing.sm,
  },
});
