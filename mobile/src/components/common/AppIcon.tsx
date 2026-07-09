import { SymbolView } from 'expo-symbols';
import type { AndroidSymbol, SFSymbol } from 'expo-symbols';
import { StyleSheet, Text, View } from 'react-native';
import type { ColorValue } from 'react-native';

import { colors } from '@/src/theme';

export type AppIconName = {
  ios: SFSymbol;
  android: AndroidSymbol;
  web: AndroidSymbol;
  fallback: string;
};

type AppIconProps = {
  name: AppIconName;
  size?: number;
  color?: ColorValue;
};

export const appIcons = {
  back: { ios: 'chevron.backward', android: 'arrow_back', web: 'arrow_back', fallback: '<' },
  cart: { ios: 'cart.fill', android: 'shopping_cart', web: 'shopping_cart', fallback: 'C' },
  chevronRight: { ios: 'chevron.right', android: 'chevron_right', web: 'chevron_right', fallback: '>' },
  close: { ios: 'xmark', android: 'close', web: 'close', fallback: 'X' },
  contact: { ios: 'phone.fill', android: 'contact_phone', web: 'contact_phone', fallback: 'P' },
  dashboard: { ios: 'square.grid.2x2.fill', android: 'dashboard', web: 'dashboard', fallback: 'D' },
  home: { ios: 'house.fill', android: 'home', web: 'home', fallback: 'H' },
  login: { ios: 'rectangle.portrait.and.arrow.right', android: 'login', web: 'login', fallback: 'L' },
  logout: { ios: 'rectangle.portrait.and.arrow.right', android: 'logout', web: 'logout', fallback: 'L' },
  menu: { ios: 'line.3.horizontal', android: 'menu', web: 'menu', fallback: 'M' },
  orders: { ios: 'receipt.fill', android: 'receipt_long', web: 'receipt_long', fallback: 'O' },
  profile: { ios: 'person.fill', android: 'person', web: 'person', fallback: 'P' },
  riders: { ios: 'person.2.fill', android: 'group', web: 'group', fallback: 'R' },
  signup: { ios: 'person.badge.plus', android: 'person_add', web: 'person_add', fallback: 'A' },
  delivery: { ios: 'scooter', android: 'motorcycle', web: 'motorcycle', fallback: 'D' },
  history: { ios: 'clock.fill', android: 'history', web: 'history', fallback: 'H' },
} satisfies Record<string, AppIconName>;

export function AppIcon({ name, size = 22, color = colors.brand.primary }: AppIconProps) {
  return (
    <SymbolView
      name={{ ios: name.ios, android: name.android, web: name.web }}
      size={size}
      tintColor={color}
      fallback={
        <View style={[styles.fallback, { width: size, height: size }]}>
          <Text style={[styles.fallbackText, { color }]}>
            {name.fallback}
          </Text>
        </View>
      }
    />
  );
}

const styles = StyleSheet.create({
  fallback: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  fallbackText: {
    fontSize: 12,
    fontWeight: '800',
    lineHeight: 16,
  },
});
