import { PropsWithChildren, createContext, useContext } from 'react';

import { colors as lightColors } from './colors';

export type ThemeColors = {
  [K in keyof typeof lightColors]: { [P in keyof (typeof lightColors)[K]]: string };
};

type ThemeContextValue = { colors: ThemeColors };
const ThemeContext = createContext<ThemeContextValue | null>(null);

export function ThemeProvider({ children }: PropsWithChildren) {
  return <ThemeContext.Provider value={{ colors: lightColors }}>{children}</ThemeContext.Provider>;
}

export function useTheme(): ThemeContextValue {
  const context = useContext(ThemeContext);
  if (!context) throw new Error('useTheme must be used inside ThemeProvider');
  return context;
}
