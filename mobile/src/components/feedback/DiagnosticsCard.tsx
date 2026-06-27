import { useQuery } from '@tanstack/react-query';
import { StyleSheet, View } from 'react-native';

import { checkHealth } from '@/src/api/health.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, spacing } from '@/src/theme';

export function DiagnosticsCard() {
  const query = useQuery({
    queryKey: queryKeys.health,
    queryFn: checkHealth,
    enabled: __DEV__,
  });

  if (!__DEV__) {
    return null;
  }

  const result = query.data;

  return (
    <AppCard style={styles.card}>
      <AppText variant="title">Developer diagnostics</AppText>
      <AppText color={colors.text.secondary}>API URL: {result?.apiUrl ?? 'Not checked yet'}</AppText>
      <AppText color={result?.ok ? colors.green.dark : colors.semantic.warning}>
        Health: {query.isFetching ? 'Checking...' : result?.ok ? 'Connected' : 'Not connected'}
      </AppText>
      {result ? (
        <View style={styles.meta}>
          <AppText variant="caption" color={colors.text.secondary}>
            Response: {result.responseTimeMs}ms
          </AppText>
          <AppText variant="caption" color={colors.text.secondary}>
            App {result.appVersion} · Expo {result.expoVersion}
          </AppText>
        </View>
      ) : null}
      <AppButton label="Check API" variant="secondary" onPress={() => void query.refetch()} />
    </AppCard>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.md,
  },
  meta: {
    gap: spacing.xs,
  },
});
