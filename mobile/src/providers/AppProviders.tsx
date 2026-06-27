import { PropsWithChildren } from 'react';
import { SafeAreaProvider } from 'react-native-safe-area-context';

import { OfflineBanner } from '@/src/components/feedback/OfflineBanner';
import { AuthBootstrap } from './AuthBootstrap';
import { QueryProvider } from './QueryProvider';

export function AppProviders({ children }: PropsWithChildren) {
  return (
    <SafeAreaProvider>
      <QueryProvider>
        <AuthBootstrap>
          <OfflineBanner />
          {children}
        </AuthBootstrap>
      </QueryProvider>
    </SafeAreaProvider>
  );
}
