import { Tabs } from 'expo-router';

import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { colors, radius, spacing } from '@/src/theme';

export default function RiderTabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.brand.primary,
        tabBarInactiveTintColor: colors.text.muted,
        tabBarActiveBackgroundColor: colors.brand.soft,
        tabBarInactiveBackgroundColor: colors.surface.card,
        tabBarHideOnKeyboard: true,
        tabBarStyle: {
          height: 76,
          backgroundColor: colors.surface.card,
          borderTopColor: colors.border.light,
          borderTopWidth: 1,
          paddingHorizontal: spacing.sm,
          paddingBottom: 10,
          paddingTop: 8,
          elevation: 12,
          shadowColor: '#171310',
          shadowOffset: { width: 0, height: -8 },
          shadowOpacity: 0.08,
          shadowRadius: 18,
        },
        tabBarItemStyle: {
          minHeight: 56,
          borderRadius: radius.lg,
          paddingVertical: 4,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '800',
          marginTop: 1,
        },
        tabBarIconStyle: {
          marginTop: 2,
        },
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Run',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name={appIcons.delivery} color={color} size={focused ? 24 : 22} />
          ),
        }}
      />
      <Tabs.Screen
        name="assigned"
        options={{
          title: 'Assigned',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name={appIcons.orders} color={color} size={focused ? 24 : 22} />
          ),
        }}
      />
      <Tabs.Screen
        name="history"
        options={{
          title: 'History',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name={appIcons.history} color={color} size={focused ? 24 : 22} />
          ),
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profile',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name={appIcons.profile} color={color} size={focused ? 24 : 22} />
          ),
        }}
      />
    </Tabs>
  );
}
