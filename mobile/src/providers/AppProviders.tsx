import { PropsWithChildren } from 'react';
import { SafeAreaProvider } from 'react-native-safe-area-context';

import { env } from '@/src/config/env';
import { ConfigurationErrorScreen } from '@/src/components/feedback/ConfigurationErrorScreen';
import { OfflineBanner } from '@/src/components/feedback/OfflineBanner';
import { AuthBootstrap } from './AuthBootstrap';
import { QueryProvider } from './QueryProvider';
import { ThemeProvider } from '@/src/theme';

export function AppProviders({ children }: PropsWithChildren) {
  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <QueryProvider>
        {env.configError ? (
          <ConfigurationErrorScreen message={env.configError} />
        ) : (
          <AuthBootstrap>
            <OfflineBanner />
            {children}
          </AuthBootstrap>
        )}
        </QueryProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
