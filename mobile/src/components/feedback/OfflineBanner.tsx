import * as Network from 'expo-network';
import { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

export function OfflineBanner() {
  const [offline, setOffline] = useState(false);

  useEffect(() => {
    let mounted = true;

    async function check() {
      const state = await Network.getNetworkStateAsync();

      if (mounted) {
        setOffline(state.isConnected === false || state.isInternetReachable === false);
      }
    }

    void check();
    const interval = setInterval(check, 15000);

    return () => {
      mounted = false;
      clearInterval(interval);
    };
  }, []);

  if (!offline) {
    return null;
  }

  return (
    <View style={styles.banner}>
      <AppText variant="caption" color={colors.text.inverse}>
        You appear to be offline. Some information may not refresh.
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    backgroundColor: colors.semantic.danger,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
  },
});
