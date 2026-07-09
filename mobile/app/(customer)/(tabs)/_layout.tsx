import { Tabs } from 'expo-router';

import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { colors } from '@/src/theme';

export default function CustomerTabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.brand.primary,
        tabBarInactiveTintColor: colors.text.muted,
        tabBarStyle: {
          height: 66,
          backgroundColor: colors.surface.card,
          borderTopColor: colors.border.light,
          paddingBottom: 8,
          paddingTop: 7,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '800',
        },
      }}
    >
      <Tabs.Screen name="index" options={{ title: 'Home', tabBarIcon: ({ color }) => <AppIcon name={appIcons.home} color={color} size={22} /> }} />
      <Tabs.Screen name="menu" options={{ title: 'Menu', tabBarIcon: ({ color }) => <AppIcon name={appIcons.menu} color={color} size={22} /> }} />
      <Tabs.Screen name="cart" options={{ title: 'Cart', tabBarIcon: ({ color }) => <AppIcon name={appIcons.cart} color={color} size={22} /> }} />
      <Tabs.Screen name="orders" options={{ title: 'Orders', tabBarIcon: ({ color }) => <AppIcon name={appIcons.orders} color={color} size={22} /> }} />
      <Tabs.Screen name="profile" options={{ title: 'Profile', tabBarIcon: ({ color }) => <AppIcon name={appIcons.profile} color={color} size={22} /> }} />
    </Tabs>
  );
}
