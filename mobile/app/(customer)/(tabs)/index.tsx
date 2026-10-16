import { useQuery } from '@tanstack/react-query';
import { Link, router } from 'expo-router';
import { Pressable, StyleSheet, useWindowDimensions, View } from 'react-native';

import { getMenuItems } from '@/src/api/menu.api';
import { getCustomerOrders } from '@/src/api/orders.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { MenuItemCard } from '@/src/components/menu/MenuItemCard';
import { MenuHeroCarousel } from '@/src/components/menu/MenuHeroCarousel';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, spacing } from '@/src/theme';
import type { MenuItem } from '@/src/types/menu';
import { formatDate } from '@/src/utils/date';
import { getRestaurantAvailability } from '@/src/utils/restaurant';
import type { Href } from 'expo-router';
import { useState } from 'react';

type QuickAction = {
  href: Href;
  label: string;
  description: string;
  icon: AppIconName;
  primary?: boolean;
};

export default function CustomerHomeScreen() {
  const { width } = useWindowDimensions();
  const [message, setMessage] = useState<string | null>(null);
  const addItem = useCartStore((state) => state.addItem);
  const cartCount = useCartStore((state) => state.getItemCount());
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const ordersQuery = useQuery({
    queryKey: queryKeys.customerOrders,
    queryFn: getCustomerOrders,
  });
  const featuredQuery = useQuery({
    queryKey: queryKeys.menuItems({ featured: true, per_page: 4 }),
    queryFn: () => getMenuItems({ featured: true, per_page: 4 }),
  });

  const availability = getRestaurantAvailability(restaurantQuery.data);
  const latestOrder = ordersQuery.data?.[0];
  const featuredItems = featuredQuery.data?.items ?? [];
  const activeOrders = (ordersQuery.data ?? []).filter((order) => !['delivered', 'cancelled'].includes(order.order_status));
  const isLoading = restaurantQuery.isLoading || ordersQuery.isLoading;
  const isError = restaurantQuery.isError || ordersQuery.isError;
  const quickActions: QuickAction[] = [
    { href: '/(customer)/(tabs)/menu', label: 'Full menu', description: 'Browse all items', icon: appIcons.menu, primary: true },
    { href: '/(customer)/(tabs)/cart', label: 'Cart', description: cartCount > 0 ? `${cartCount} items` : 'Review basket', icon: appIcons.cart },
    { href: '/(customer)/(tabs)/orders', label: 'Orders', description: `${activeOrders.length} active`, icon: appIcons.orders },
    { href: '/(customer)/(tabs)/profile', label: 'Profile', description: 'Addresses & account', icon: appIcons.profile },
  ];

  function refresh() {
    void restaurantQuery.refetch();
    void ordersQuery.refetch();
    void featuredQuery.refetch();
  }

  function handleAdd(item: MenuItem) {
    const customizable = Boolean(item.sizes?.some((size) => size.is_active) || item.addons?.some((addon) => addon.is_active) || item.customization_groups?.length);

    if (customizable) {
      router.push(`/(public)/item/${item.id}`);
      return;
    }

    addItem({ item, quantity: 1 });
    setMessage(`${item.name} added to cart.`);
  }

  return (
    <AppScreen
      refreshing={restaurantQuery.isRefetching || ordersQuery.isRefetching || featuredQuery.isRefetching}
      onRefresh={refresh}
      contentStyle={styles.screen}
    >
      {isLoading ? <LoadingState label="Loading home..." /> : null}
      {isError ? <ErrorState message="Unable to load home data." onRetry={refresh} /> : null}

      {!isLoading && !isError ? (
        <>
          {restaurantQuery.data ? (
            <MenuHeroCarousel
              restaurant={restaurantQuery.data}
              statusLabel={availability.isOpenForOrders ? 'Open now' : 'Closed now'}
              isOpen={availability.isOpenForOrders}
              actionHref="/(customer)/(tabs)/menu"
              actionLabel="Browse menu"
            />
          ) : null}

          {message ? <FeedbackMessage tone="success" message={message} /> : null}

          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <SectionTitle title="Popular to order" subtitle="Tap Add and keep building your cart." />
              <Link href="/(customer)/(tabs)/menu">
                <AppText variant="caption" color={colors.brand.primary}>View all</AppText>
              </Link>
            </View>
            {featuredItems.length === 0 ? (
              <AppCard style={styles.emptyCard}>
                <AppText color={colors.text.secondary}>Featured menu items will appear here soon.</AppText>
              </AppCard>
            ) : (
              <View style={styles.featuredGrid}>
                {featuredItems.map((item) => (
                  <View key={item.id} style={[styles.featuredCell, { width: width >= 980 ? '23.8%' : '48.3%' }]}>
                    <MenuItemCard
                      item={item}
                      detailHref={`/(public)/item/${item.id}`}
                      showAddButton
                      addDisabled={!availability.isOpenForOrders}
                      addDisabledReason={availability.label}
                      onAdd={handleAdd}
                    />
                  </View>
                ))}
              </View>
            )}
          </View>

          <View style={styles.quickGrid}>
            {quickActions.map((action) => (
              <QuickActionCard key={action.label} action={action} />
            ))}
          </View>

          <View style={styles.section}>
            <SectionTitle title="Order tracking" subtitle="Your latest checkout status." />
            {latestOrder ? (
              <AppCard style={styles.orderCard}>
                <View style={styles.statusRow}>
                  <View style={styles.flex}>
                    <AppText variant="title">{latestOrder.order_number}</AppText>
                    <AppText color={colors.text.secondary}>{formatDate(latestOrder.created_at)}</AppText>
                  </View>
                  <StatusBadge status={latestOrder.order_status_label ?? latestOrder.order_status} />
                </View>
                <PriceText amount={latestOrder.total} />
                <Link href={`/(customer)/orders/${latestOrder.id}`} asChild>
                  <AppButton label="Track order" variant="outline" />
                </Link>
              </AppCard>
            ) : (
              <AppCard style={styles.emptyCard}>
                <AppText color={colors.text.secondary}>Your first order will appear here after checkout.</AppText>
              </AppCard>
            )}
          </View>
        </>
      ) : null}
    </AppScreen>
  );
}

function QuickActionCard({ action }: { action: QuickAction }) {
  function handlePress() {
    router.push(action.href);
  }

  return (
    <Pressable
      accessibilityRole="link"
      accessibilityLabel={action.label}
      onPress={handlePress}
      style={({ pressed }) => [styles.quickCard, action.primary && styles.quickCardPrimary, pressed && styles.pressed]}
    >
      <View style={[styles.quickIcon, action.primary && styles.quickIconPrimary]}>
        <AppIcon name={action.icon} size={22} color={action.primary ? colors.text.inverse : colors.brand.primary} />
      </View>
      <View style={styles.flex}>
        <AppText variant="title" numberOfLines={1}>
          {action.label}
        </AppText>
        <AppText color={colors.text.secondary} numberOfLines={1}>
          {action.description}
        </AppText>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.lg,
  },
  featuredGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  featuredCell: {
    minWidth: 0,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  quickCard: {
    minHeight: 84,
    minWidth: 150,
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
  },
  quickCardPrimary: {
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
  },
  quickIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  quickIconPrimary: {
    backgroundColor: colors.brand.primary,
  },
  section: {
    gap: spacing.md,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  orderCard: {
    gap: spacing.md,
  },
  emptyCard: {
    backgroundColor: colors.surface.muted,
  },
  flex: {
    flex: 1,
    minWidth: 0,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
