import { router, usePathname, useSegments } from 'expo-router';
import type { Href } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { colors, radius, spacing } from '@/src/theme';

type AdminQuickAction = {
  label: string;
  href: Href;
  icon: AppIconName;
  key: 'dashboard' | 'orders' | 'menu' | 'riders';
};

const actions: AdminQuickAction[] = [
  { label: 'Live', href: '/(admin)', icon: appIcons.dashboard, key: 'dashboard' },
  { label: 'Orders', href: '/(admin)/orders', icon: appIcons.orders, key: 'orders' },
  { label: 'Menu', href: '/(admin)/menu-items', icon: appIcons.menu, key: 'menu' },
  { label: 'Riders', href: '/(admin)/riders', icon: appIcons.riders, key: 'riders' },
];

export function AdminQuickActionBar() {
  const pathname = usePathname();
  const segments = useSegments();

  return (
    <View style={styles.bar}>
      {actions.map((action) => (
        <QuickAction
          key={action.key}
          action={action}
          active={isActiveAction(action, pathname, segments.map(String))}
        />
      ))}
    </View>
  );
}

function QuickAction({ action, active }: { action: AdminQuickAction; active: boolean }) {
  return (
    <Pressable
      accessibilityRole="link"
      accessibilityState={{ selected: active }}
      onPress={() => {
        if (!active) {
          router.replace(action.href);
        }
      }}
      style={({ pressed }) => [styles.action, active && styles.actionActive, pressed && styles.pressed]}
    >
      <View style={[styles.iconShell, active && styles.iconShellActive]}>
        <AppIcon name={action.icon} size={20} color={active ? colors.text.inverse : colors.brand.primary} />
      </View>
      <AppText variant="caption" color={active ? colors.brand.primary : colors.text.secondary} numberOfLines={1}>
        {action.label}
      </AppText>
    </Pressable>
  );
}

function isActiveAction(action: AdminQuickAction, pathname: string, segments: string[]): boolean {
  if (action.key === 'orders') {
    return pathname.includes('/orders') || segments.includes('orders');
  }

  if (action.key === 'riders') {
    return pathname.includes('/riders') || segments.includes('riders');
  }

  if (action.key === 'menu') {
    return pathname.includes('/menu-items') || segments.includes('menu-items');
  }

  return !segments.includes('orders')
    && !segments.includes('riders')
    && !segments.includes('menu-items')
    && !segments.includes('restaurant');
}

const styles = StyleSheet.create({
  bar: {
    minHeight: 72,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderTopWidth: 1,
    borderTopColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.sm,
    paddingBottom: spacing.sm,
    paddingTop: spacing.sm,
  },
  action: {
    flex: 1,
    minHeight: 52,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.lg,
  },
  actionActive: {
    backgroundColor: colors.brand.soft,
  },
  iconShell: {
    width: 28,
    height: 28,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    backgroundColor: colors.surface.card,
  },
  iconShellActive: {
    backgroundColor: colors.brand.primary,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
