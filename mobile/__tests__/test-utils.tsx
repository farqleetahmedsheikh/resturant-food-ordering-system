import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render } from '@testing-library/react-native';
import type { ReactElement, PropsWithChildren } from 'react';
import { ThemeProvider } from '@/src/theme';

export function createTestQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
        gcTime: Infinity,
      },
      mutations: {
        retry: false,
      },
    },
  });
}

export function renderWithQueryClient(ui: ReactElement) {
  const queryClient = createTestQueryClient();

  function Wrapper({ children }: PropsWithChildren) {
    return (
      <ThemeProvider>
        <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
      </ThemeProvider>
    );
  }

  return {
    queryClient,
    ...render(ui, { wrapper: Wrapper }),
  };
}

export function renderWithTheme(ui: ReactElement) {
  return render(ui, { wrapper: ThemeProvider });
}
