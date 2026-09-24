import { Link, router, usePathname } from 'expo-router';
import type { Href } from 'expo-router';
import { Modal, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { useState } from 'react';

import { useAuthStore } from '@/src/auth/auth.store';
import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { env } from '@/src/config/env';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, shadows, spacing } from '@/src/theme';

type AppNavigationHeaderProps = {
  name?: string | null;
  subtitle?: string;
  logoUrl?: string | null;
  initials?: string | null;
  showBack?: boolean;
};

type DrawerLink = {
  label: string;
  href: Href;
  icon: AppIconName;
  note?: string;
  activePaths?: string[];
};

export function AppNavigationHeader({
  name = 'Arcade Kebab House',
  subtitle = 'Fresh kebabs in AUD',
  logoUrl,
  initials,
  showBack = true,
}: AppNavigationHeaderProps) {
  const [open, setOpen] = useState(false);
  const pathname = usePathname();
  const user = useAuthStore((state) => state.session?.user ?? null);
  const logout = useAuthStore((state) => state.logout);
  const cartCount = useCartStore((state) => state.getItemCount());
  const canGoBack = showBack && router.canGoBack();
  const cartHref = getCartHref(user?.role);
  const links = getDrawerLinks(user?.role, cartCount);

  async function handleLogout() {
    setOpen(false);
    await logout();
    router.replace('/(public)');
  }

  return (
    <>
      <View style={styles.header}>
        <View style={styles.leftControls}>
          {canGoBack ? (
            <IconButton
              label="Go back"
              icon={appIcons.back}
              onPress={() => router.back()}
            />
          ) : null}
          <IconButton
            label="Open menu"
            icon={appIcons.menu}
            onPress={() => setOpen(true)}
          />
        </View>

        <View style={styles.brand}>
          <RestaurantLogo name={name} logoUrl={logoUrl} initials={initials} size={40} />
          <View style={styles.brandCopy}>
            <AppText variant="title" numberOfLines={1}>
              {name}
            </AppText>
            <AppText variant="caption" color={colors.text.secondary} numberOfLines={1}>
              {subtitle}
            </AppText>
          </View>
        </View>

        <Link href={cartHref} asChild>
          <Pressable
            accessibilityRole="button"
            accessibilityLabel={cartCount > 0 ? `View cart with ${cartCount} items` : 'Open cart'}
            style={({ pressed }) => [styles.headerCartButton, pressed && styles.pressed]}
          >
            <AppIcon name={appIcons.cart} size={22} />
            {cartCount > 0 ? (
              <View style={styles.cartCount}>
                <AppText variant="caption" color={colors.text.inverse}>
                  {cartCount}
                </AppText>
              </View>
            ) : null}
          </Pressable>
        </Link>
      </View>

      <Modal visible={open} transparent animationType="fade" onRequestClose={() => setOpen(false)}>
        <View style={styles.modalRoot}>
          <Pressable
            accessibilityRole="button"
            accessibilityLabel="Close navigation menu"
            style={styles.scrim}
            onPress={() => setOpen(false)}
          />

          <View style={styles.drawer}>
            <View style={styles.drawerTop}>
              <View style={styles.drawerIdentity}>
                <RestaurantLogo name={name} logoUrl={logoUrl} initials={initials} size={52} />
                <View style={styles.drawerTitle}>
                  <AppText variant="title" numberOfLines={1}>
                    {name}
                  </AppText>
                  <AppText color={colors.text.secondary} numberOfLines={1}>
                    {user ? `${user.name} - ${user.role}` : 'Order fresh kebabs faster'}
                  </AppText>
                </View>
              </View>
              <IconButton
                label="Close menu"
                icon={appIcons.close}
                onPress={() => setOpen(false)}
              />
            </View>

            <ScrollView
              style={styles.drawerScroll}
              contentContainerStyle={styles.drawerList}
              showsVerticalScrollIndicator={false}
            >
              {links.map((item) => (
                <DrawerLinkRow
                  key={item.label}
                  item={item}
                  active={isDrawerLinkActive(pathname, item)}
                  onPress={() => setOpen(false)}
                />
              ))}
            </ScrollView>

            <View style={styles.drawerFooter}>
              {user ? (
                <Pressable
                  accessibilityRole="button"
                  onPress={() => void handleLogout()}
                  style={({ pressed }) => [styles.logoutButton, pressed && styles.pressed]}
                >
                  <AppIcon name={appIcons.logout} size={22} />
                  <AppText variant="caption" color={colors.brand.primary}>
                    Logout
                  </AppText>
                </Pressable>
              ) : (
                <View style={styles.guestFooterActions}>
                  <Link href="/(auth)/login" asChild>
                    <Pressable
                      accessibilityRole="link"
                      onPress={() => setOpen(false)}
                      style={({ pressed }) => [styles.footerButton, styles.footerButtonPrimary, pressed && styles.pressed]}
                    >
                      <AppIcon name={appIcons.login} size={20} color={colors.text.inverse} />
                      <AppText variant="caption" color={colors.text.inverse}>
                        Login
                      </AppText>
                    </Pressable>
                  </Link>
                  <Link href="/(auth)/register" asChild>
                    <Pressable
                      accessibilityRole="link"
                      onPress={() => setOpen(false)}
                      style={({ pressed }) => [styles.footerButton, pressed && styles.pressed]}
                    >
                      <AppIcon name={appIcons.signup} size={20} />
                      <AppText variant="caption" color={colors.brand.primary}>
                        Sign up
                      </AppText>
                    </Pressable>
                  </Link>
                </View>
              )}
            </View>
          </View>
        </View>
      </Modal>
    </>
  );
}

function IconButton({
  label,
  icon,
  onPress,
}: {
  label: string;
  icon: AppIconName;
  onPress: () => void;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={label}
      onPress={onPress}
      style={({ pressed }) => [styles.iconButton, pressed && styles.pressed]}
    >
      <AppIcon name={icon} size={22} />
    </Pressable>
  );
}

function DrawerLinkRow({
  item,
  active,
  onPress,
}: {
  item: DrawerLink;
  active: boolean;
  onPress: () => void;
}) {
  function handlePress() {
    onPress();
    router.push(item.href);
  }

  return (
    <Pressable
      accessibilityRole="link"
      accessibilityLabel={item.label}
      onPress={handlePress}
      style={({ pressed }) => [
        styles.drawerLink,
        active && styles.drawerLinkActive,
        pressed && styles.pressed,
      ]}
    >
      <View style={[styles.drawerIconShell, active && styles.drawerIconShellActive]}>
        <AppIcon
          name={item.icon}
          size={21}
          color={active ? colors.text.inverse : colors.brand.primary}
        />
      </View>
      <AppText variant="body" numberOfLines={1} style={styles.drawerLabel}>
        {item.label}
      </AppText>
      {item.note ? (
        <View style={[styles.drawerMetaPill, active && styles.drawerMetaPillActive]}>
          <AppText
            variant="caption"
            color={active ? colors.brand.primary : colors.text.secondary}
            numberOfLines={1}
          >
            {item.note}
          </AppText>
        </View>
      ) : null}
      <AppIcon
        name={appIcons.chevronRight}
        size={18}
        color={active ? colors.brand.primary : colors.text.muted}
      />
    </Pressable>
  );
}

function getCartHref(role: string | undefined): Href {
  return (role === 'customer' ? '/(customer)/(tabs)/cart' : '/(auth)/login') as Href;
}

function getDrawerLinks(role: string | undefined, cartCount: number): DrawerLink[] {
  const cartNote = cartCount > 0 ? `${cartCount} item${cartCount === 1 ? '' : 's'}` : undefined;

  if (role === 'customer') {
    return [
      { label: 'Home', href: '/(customer)/(tabs)', icon: appIcons.home, activePaths: ['/', '/customer'] },
      { label: 'Browse Menu', href: '/(customer)/(tabs)/menu', icon: appIcons.menu },
      { label: 'Cart', href: '/(customer)/(tabs)/cart', icon: appIcons.cart, note: cartNote },
      { label: 'Orders', href: '/(customer)/(tabs)/orders', icon: appIcons.orders },
      { label: 'Profile', href: '/(customer)/(tabs)/profile', icon: appIcons.profile },
      { label: 'Contact', href: '/(public)/contact', icon: appIcons.contact },
      { label: 'Privacy Policy', href: '/(public)/privacy', icon: appIcons.privacy },
      { label: 'Terms', href: '/(public)/terms', icon: appIcons.legal },
    ];
  }

  if (role === 'rider') {
    return [
      { label: 'Dashboard', href: '/(rider)/(tabs)', icon: appIcons.dashboard },
      { label: 'Assigned Orders', href: '/(rider)/(tabs)/assigned', icon: appIcons.delivery },
      { label: 'Delivery History', href: '/(rider)/(tabs)/history', icon: appIcons.history },
      { label: 'Profile', href: '/(rider)/(tabs)/profile', icon: appIcons.profile },
      { label: 'Contact', href: '/(public)/contact', icon: appIcons.contact },
      { label: 'Privacy Policy', href: '/(public)/privacy', icon: appIcons.privacy },
      { label: 'Terms', href: '/(public)/terms', icon: appIcons.legal },
    ];
  }

  if (role === 'admin') {
    return [
      { label: 'Dashboard', href: (env.enableAdminMobile ? '/(admin)' : '/admin-unavailable') as Href, icon: appIcons.dashboard },
      { label: 'Orders', href: '/(admin)/orders', icon: appIcons.orders },
      { label: 'Menu items', href: '/(admin)/menu-items', icon: appIcons.menu },
      { label: 'Restaurant', href: '/(admin)/restaurant', icon: appIcons.contact },
      { label: 'Riders', href: '/(admin)/riders', icon: appIcons.riders },
      { label: 'Contact', href: '/(public)/contact', icon: appIcons.contact },
      { label: 'Privacy Policy', href: '/(public)/privacy', icon: appIcons.privacy },
      { label: 'Terms', href: '/(public)/terms', icon: appIcons.legal },
    ];
  }

  return [
    { label: 'Home', href: '/(public)', icon: appIcons.home, activePaths: ['/'] },
    { label: 'Browse Menu', href: '/(public)/menu', icon: appIcons.menu, activePaths: ['/menu', '/item'] },
    { label: 'Cart', href: '/(auth)/login', icon: appIcons.cart, note: cartCount > 0 ? `${cartCount} item${cartCount === 1 ? '' : 's'}` : 'Login' },
    { label: 'Contact', href: '/(public)/contact', icon: appIcons.contact, activePaths: ['/contact'] },
    { label: 'Privacy Policy', href: '/(public)/privacy', icon: appIcons.privacy, activePaths: ['/privacy'] },
    { label: 'Terms', href: '/(public)/terms', icon: appIcons.legal, activePaths: ['/terms'] },
    { label: 'Orders', href: '/(auth)/login', icon: appIcons.orders, note: 'Login' },
    { label: 'Login', href: '/(auth)/login', icon: appIcons.login, activePaths: ['/login'] },
    { label: 'Sign up', href: '/(auth)/register', icon: appIcons.signup, activePaths: ['/register'] },
  ];
}

function isDrawerLinkActive(pathname: string, item: DrawerLink): boolean {
  if (item.activePaths) {
    return item.activePaths.some((path) => pathname === path || (path !== '/' && pathname.startsWith(path)));
  }

  return pathname === String(item.href);
}

const styles = StyleSheet.create({
  header: {
    minHeight: 68,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.border.light,
    backgroundColor: colors.surface.page,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  leftControls: {
    flexDirection: 'row',
    gap: spacing.xs,
  },
  iconButton: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
  },
  brand: {
    flex: 1,
    minWidth: 0,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  brandCopy: {
    flex: 1,
    minWidth: 0,
    gap: 3,
  },
  headerCartButton: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
  },
  cartCount: {
    minWidth: 20,
    height: 20,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'absolute',
    right: -2,
    top: -2,
    borderRadius: radius.pill,
    backgroundColor: colors.brand.primary,
    paddingHorizontal: 5,
  },
  modalRoot: {
    flex: 1,
    flexDirection: 'row',
  },
  scrim: {
    bottom: 0,
    left: 0,
    position: 'absolute',
    right: 0,
    top: 0,
    backgroundColor: 'rgba(23, 19, 16, 0.38)',
  },
  drawer: {
    width: '86%',
    maxWidth: 370,
    borderBottomRightRadius: 28,
    borderTopRightRadius: 28,
    backgroundColor: colors.surface.page,
    padding: spacing.lg,
    ...shadows.card,
  },
  drawerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
  },
  drawerIdentity: {
    flex: 1,
    minWidth: 0,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  drawerTitle: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  drawerScroll: {
    flex: 1,
    marginTop: spacing.lg,
  },
  drawerList: {
    gap: 2,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.sm,
  },
  drawerLink: {
    minHeight: 56,
    width: '100%',
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'stretch',
    flexWrap: 'nowrap',
    gap: spacing.sm,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  drawerLinkActive: {
    backgroundColor: colors.brand.soft,
  },
  drawerIconShell: {
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
    borderWidth: 1,
    borderColor: colors.brand.border,
  },
  drawerIconShellActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  drawerLabel: {
    flex: 1,
    minWidth: 0,
    fontWeight: '800',
  },
  drawerMetaPill: {
    maxWidth: 74,
    borderRadius: radius.pill,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
    paddingVertical: 3,
  },
  drawerMetaPillActive: {
    backgroundColor: colors.surface.card,
  },
  drawerFooter: {
    borderTopWidth: 1,
    borderTopColor: colors.border.light,
    paddingTop: spacing.lg,
  },
  logoutButton: {
    minHeight: 50,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.lg,
  },
  guestFooterActions: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  footerButton: {
    minHeight: 50,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.brand.border,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  footerButtonPrimary: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
