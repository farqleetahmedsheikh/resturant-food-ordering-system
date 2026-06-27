import * as SplashScreen from 'expo-splash-screen';
import { PropsWithChildren, useEffect } from 'react';

import { configureApiClient } from '@/src/api/client';
import { useAuthStore } from '@/src/auth/auth.store';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';

void SplashScreen.preventAutoHideAsync();

export function AuthBootstrap({ children }: PropsWithChildren) {
  const status = useAuthStore((state) => state.status);
  const restore = useAuthStore((state) => state.restore);

  useEffect(() => {
    configureApiClient();

    restore().finally(() => {
      void SplashScreen.hideAsync();
    });
  }, [restore]);

  if (status === 'loading') {
    return <LoadingScreen label="Preparing Arcade Kebab House..." />;
  }

  return children;
}
