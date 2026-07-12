import { PropsWithChildren, useCallback, useEffect } from 'react';
import { StyleSheet, View } from 'react-native';

import { configureApiClient } from '@/src/api/client';
import { useAuthStore } from '@/src/auth/auth.store';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { colors } from '@/src/theme';

function startupErrorMessage(error: unknown): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return 'Unable to prepare Arcade Kebab House. Please try again.';
}

export function AuthBootstrap({ children }: PropsWithChildren) {
  const status = useAuthStore((state) => state.status);
  const error = useAuthStore((state) => state.error);
  const restore = useAuthStore((state) => state.restore);

  const restoreAuth = useCallback((showLoading = false) => {
    configureApiClient();

    if (showLoading) {
      useAuthStore.setState({ status: 'loading', session: null, error: null });
    }

    void restore().catch((restoreError) => {
      useAuthStore.setState({
        status: 'error',
        session: null,
        error: startupErrorMessage(restoreError),
      });
    });
  }, [restore]);

  useEffect(() => {
    restoreAuth();
  }, [restoreAuth]);

  if (status === 'loading') {
    return <LoadingScreen label="Preparing Arcade Kebab House..." />;
  }

  if (status === 'error') {
    return (
      <View style={styles.errorScreen}>
        <ErrorState
          message={error ?? 'Unable to prepare Arcade Kebab House.'}
          onRetry={() => restoreAuth(true)}
        />
      </View>
    );
  }

  return children;
}

const styles = StyleSheet.create({
  errorScreen: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: colors.surface.page,
  },
});
