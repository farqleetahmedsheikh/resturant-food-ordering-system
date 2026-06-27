import { Text, TextProps, StyleSheet } from 'react-native';

import { colors } from '@/src/theme';

type AppTextProps = TextProps & {
  variant?: 'h1' | 'h2' | 'title' | 'body' | 'caption';
  color?: string;
};

export function AppText({ variant = 'body', color = colors.text.primary, style, ...props }: AppTextProps) {
  return <Text {...props} style={[styles.base, styles[variant], { color }, style]} />;
}

const styles = StyleSheet.create({
  base: {
    includeFontPadding: false,
  },
  h1: {
    fontSize: 32,
    lineHeight: 38,
    fontWeight: '800',
  },
  h2: {
    fontSize: 24,
    lineHeight: 30,
    fontWeight: '800',
  },
  title: {
    fontSize: 18,
    lineHeight: 24,
    fontWeight: '800',
  },
  body: {
    fontSize: 15,
    lineHeight: 22,
    fontWeight: '500',
  },
  caption: {
    fontSize: 12,
    lineHeight: 17,
    fontWeight: '700',
  },
});
