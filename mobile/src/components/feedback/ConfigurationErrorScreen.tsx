import { StyleSheet, View } from 'react-native';

import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

type ConfigurationErrorScreenProps = {
  message: string;
};

export function ConfigurationErrorScreen({ message }: ConfigurationErrorScreenProps) {
  return (
    <View style={styles.wrapper}>
      <AppCard style={styles.card}>
        <AppText variant="caption" color={colors.brand.primary}>
          ARCADE KEBAB HOUSE
        </AppText>
        <AppText variant="h2">App setup needs attention</AppText>
        <AppText color={colors.text.secondary}>
          The mobile app could not start because its API connection is not configured correctly.
        </AppText>
        <View style={styles.messageBox}>
          <AppText variant="caption" color={colors.semantic.danger}>
            {message}
          </AppText>
        </View>
        <AppText variant="caption" color={colors.text.secondary}>
          Rebuild the app after setting EXPO_PUBLIC_API_URL to your Laravel API URL, for example
          http://arcadekebab.com/api/v1.
        </AppText>
      </AppCard>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: colors.surface.page,
    padding: spacing.lg,
  },
  card: {
    gap: spacing.md,
  },
  messageBox: {
    borderRadius: 14,
    backgroundColor: colors.brand.soft,
    padding: spacing.md,
  },
});
