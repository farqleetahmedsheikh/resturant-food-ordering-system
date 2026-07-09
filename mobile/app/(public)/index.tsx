import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link, router } from 'expo-router';
import type { Href } from 'expo-router';
import { useState } from 'react';
import { Pressable, StyleSheet, useWindowDimensions, View } from 'react-native';
import type { DimensionValue } from 'react-native';

import { getMenuItems } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { getRoleRedirect } from '@/src/auth/role-redirect';
import { useAuthStore } from '@/src/auth/auth.store';
import { AppIcon, appIcons, type AppIconName } from '@/src/components/common/AppIcon';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { RestaurantLogo } from '@/src/components/common/RestaurantLogo';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppNavigationHeader } from '@/src/components/layout/AppNavigationHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { env } from '@/src/config/env';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, shadows, spacing } from '@/src/theme';
import type { MenuItem } from '@/src/types/menu';
import { getRestaurantAvailability, type RestaurantAvailability } from '@/src/utils/restaurant';

type QuickAction = {
  title: string;
  description: string;
  icon: AppIconName;
  href: Href;
  emphasized?: boolean;
};

function friendlyStatus(availability: RestaurantAvailability) {
  if (availability.isOpenForOrders) {
    return {
      label: 'Open for orders',
      tone: 'green' as const,
      message: 'Kitchen is taking online orders now.',
    };
  }

  const source = `${availability.label} ${availability.reason ?? ''}`.toLowerCase();
  const paused = source.includes('pause');

  return {
    label: paused ? 'Ordering paused' : 'Closed now',
    tone: 'gold' as const,
    message: availability.nextOpeningTime
      ? `Currently closed - next opening time: ${availability.nextOpeningTime}.`
      : paused
        ? 'Ordering is paused temporarily. You can still browse the menu.'
        : 'Currently closed - you can still browse the menu.',
  };
}

function hasCustomizations(item: MenuItem) {
  return Boolean(
    item.sizes?.some((size) => size.is_active) ||
    item.addons?.some((addon) => addon.is_active),
  );
}

export default function PublicHomeScreen() {
  const { width } = useWindowDimensions();
  const user = useAuthStore((state) => state.session?.user ?? null);
  const addItem = useCartStore((state) => state.addItem);
  const cartCount = useCartStore((state) => state.getItemCount());
  const cartSubtotal = useCartStore((state) => state.getSubtotal());
  const [message, setMessage] = useState<string | null>(null);

  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const featuredQuery = useQuery({
    queryKey: queryKeys.menuItems({ featured: true, per_page: 6 }),
    queryFn: () => getMenuItems({ featured: true, per_page: 6 }),
  });

  if (restaurantQuery.isLoading) {
    return <LoadingScreen label="Loading restaurant..." />;
  }

  if (restaurantQuery.isError) {
    return <ErrorState message="Unable to load restaurant details." onRetry={() => void restaurantQuery.refetch()} />;
  }

  const restaurant = restaurantQuery.data;
  const availability = getRestaurantAvailability(restaurant);
  const status = friendlyStatus(availability);
  const featuredItems = featuredQuery.data?.items ?? [];
  const isTablet = width >= 680;
  const isDesktop = width >= 900;
  const isMobile = width < 768;
  const popularCardWidth: DimensionValue = isTablet ? '48.2%' : '100%';
  const customerHomeHref = getRoleRedirect(user, env.enableAdminMobile) as Href;
  const cartHref = (user ? '/(customer)/(tabs)/cart' : '/(auth)/login') as Href;
  const quickActions: QuickAction[] = [
    {
      title: 'Menu',
      description: 'Browse kebabs, grilled plates, sides, and drinks.',
      icon: appIcons.menu,
      href: '/(public)/menu',
      emphasized: true,
    },
    {
      title: user ? 'Home' : 'Sign in',
      description: user ? 'Continue ordering from your customer home.' : 'Return faster and track orders after checkout.',
      icon: user ? appIcons.home : appIcons.login,
      href: user ? customerHomeHref : '/(auth)/login',
    },
    {
      title: user ? 'Orders' : 'Create account',
      description: user ? 'Check current and past order status.' : 'Save your details for quicker checkout.',
      icon: user ? appIcons.orders : appIcons.signup,
      href: user ? '/(customer)/(tabs)/orders' : '/(auth)/register',
    },
    {
      title: 'Contact',
      description: 'Find the restaurant phone, address, and location.',
      icon: appIcons.contact,
      href: '/(public)/contact',
    },
  ];

  function refresh() {
    void restaurantQuery.refetch();
    void featuredQuery.refetch();
  }

  function handlePopularAction(item: MenuItem) {
    if (!item.is_available || hasCustomizations(item) || !availability.isOpenForOrders) {
      router.push(`/(public)/item/${item.id}`);
      return;
    }

    addItem({ item, quantity: 1 });
    setMessage(`${item.name} added to cart.`);
  }

  return (
    <AppScreen
      refreshing={restaurantQuery.isRefetching || featuredQuery.isRefetching}
      onRefresh={refresh}
      contentStyle={[styles.screen, isDesktop && styles.desktopScreen]}
      header={
        <AppNavigationHeader
          logoUrl={restaurant?.logo_url}
          name={restaurant?.name}
          initials={restaurant?.initials}
          showBack={false}
        />
      }
      footer={
        isMobile ? (
          <StickyOrderBar
            cartCount={cartCount}
            cartSubtotal={cartSubtotal}
            cartHref={cartHref}
          />
        ) : null
      }
    >
      <View style={[styles.hero, isTablet && styles.heroWide]}>
        <View pointerEvents="none" style={styles.heroGlow} />
        <View style={styles.heroCopy}>
          <View style={styles.heroKicker}>
            <AppBadge label={status.label} tone={status.tone} />
            <View style={styles.securePill}>
              <AppIcon name={appIcons.cart} size={16} color={colors.brand.primary} />
              <AppText variant="caption" color={colors.brand.primary}>
                Secure checkout
              </AppText>
            </View>
          </View>
          <AppText variant="h1" accessibilityRole="header" style={styles.heroTitle}>
            Fresh kebabs, grilled plates & drinks - ready to order.
          </AppText>
          <AppText color={colors.text.secondary}>
            Order from Arcade Kebab House with simple pickup and delivery tracking.
          </AppText>
          <View style={styles.heroActions}>
            <Link href="/(public)/menu" asChild>
              <AppButton label="Start Order" />
            </Link>
            <Link href="/(public)/menu" asChild>
              <AppButton label="View Menu" variant="outline" />
            </Link>
          </View>
          <View style={styles.heroStats}>
            <MiniStat label="Minimum" value={<PriceText amount={restaurant?.minimum_order_amount ?? 0} />} />
            <MiniStat label="Delivery" value={<PriceText amount={restaurant?.delivery_fee ?? 0} />} />
            <MiniStat label="Browse" value="Anytime" />
          </View>
        </View>

        {isTablet || restaurant?.cover_image_url ? (
          <View style={styles.heroVisual}>
          {restaurant?.cover_image_url ? (
            <Image
              source={{ uri: restaurant.cover_image_url }}
              style={styles.coverImage}
              contentFit="cover"
              accessibilityRole="image"
              accessibilityLabel={`${restaurant.name} food photo`}
            />
          ) : (
            <View style={styles.foodPlaceholder}>
              <RestaurantLogo
                name={restaurant?.name}
                logoUrl={restaurant?.logo_url}
                initials={restaurant?.initials}
                size={82}
              />
              <AppText variant="caption" color={colors.text.secondary}>
                Fresh from the grill
              </AppText>
            </View>
          )}
          </View>
        ) : null}
      </View>

      {!availability.isOpenForOrders ? (
        <AppCard style={styles.closedCard}>
          <AppBadge label={status.label} tone={status.tone} />
          <AppText color={colors.text.secondary}>{status.message}</AppText>
        </AppCard>
      ) : null}

      {message ? <FeedbackMessage tone="success" message={message} /> : null}

      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <SectionTitle title="Popular picks" subtitle="Customer favourites from the live menu." />
          <Link href="/(public)/menu">
            <AppText variant="caption" color={colors.brand.primary}>
              View all menu
            </AppText>
          </Link>
        </View>

        {featuredQuery.isLoading ? (
          <AppCard>
            <AppText color={colors.text.secondary}>Loading popular items...</AppText>
          </AppCard>
        ) : featuredQuery.isError ? (
          <ErrorState message="Unable to load popular items." onRetry={() => void featuredQuery.refetch()} />
        ) : featuredItems.length === 0 ? (
          <EmptyState title="Menu is being updated" message="Popular items will appear soon. You can still view all items." />
        ) : (
          <View style={styles.popularGrid}>
            {featuredItems.map((item) => (
              <View key={item.id} style={[styles.popularCell, { width: popularCardWidth }]}>
                <PopularItemCard
                  item={item}
                  orderingEnabled={availability.isOpenForOrders}
                  onAction={handlePopularAction}
                />
              </View>
            ))}
          </View>
        )}
      </View>

      <View style={styles.section}>
        <SectionTitle title="Quick actions" subtitle="Everything important is one tap away." />
        <View style={styles.quickGrid}>
          {quickActions.map((action) => (
            <QuickActionCard key={action.title} action={action} />
          ))}
        </View>
      </View>

      <AppCard style={styles.orderDetails}>
        <SectionTitle title="Ordering details" subtitle="Useful details before you start." />
        <DetailRow label="Status" value={status.label} badgeTone={status.tone} />
        <DetailRow
          label="Today's hours"
          value={restaurant?.opening_time && restaurant?.closing_time
            ? `${restaurant.opening_time} - ${restaurant.closing_time}`
            : 'Hours managed by restaurant'}
        />
        {!availability.isOpenForOrders ? (
          <DetailRow
            label="Note"
            value={availability.nextOpeningTime ? `Next opens ${availability.nextOpeningTime}` : status.message}
          />
        ) : null}
        <DetailRow label="Minimum order" value={<PriceText amount={restaurant?.minimum_order_amount ?? 0} />} />
        <DetailRow label="Delivery fee" value={<PriceText amount={restaurant?.delivery_fee ?? 0} />} />
      </AppCard>
    </AppScreen>
  );
}

type PopularItemCardProps = {
  item: MenuItem;
  orderingEnabled: boolean;
  onAction: (item: MenuItem) => void;
};

function PopularItemCard({ item, orderingEnabled, onAction }: PopularItemCardProps) {
  const custom = hasCustomizations(item);
  const unavailable = !item.is_available;
  const ctaLabel = unavailable ? 'Unavailable' : !orderingEnabled ? 'View' : custom ? 'Customize' : 'Add';

  return (
    <AppCard style={[styles.popularCard, unavailable && styles.disabledCard]}>
      <View style={styles.popularImageShell}>
        {item.image_url ? (
          <Image
            source={{ uri: item.image_url }}
            style={styles.popularImage}
            contentFit="cover"
            accessibilityRole="image"
            accessibilityLabel={`${item.name} photo`}
          />
        ) : (
          <View style={styles.itemPlaceholder}>
            <AppText variant="h2" color={colors.brand.primary}>
              {item.name.slice(0, 1).toUpperCase()}
            </AppText>
          </View>
        )}
      </View>

      <View style={styles.popularBody}>
        <View style={styles.itemBadges}>
          <AppBadge label={item.category?.name ?? 'Menu'} tone="neutral" />
          <AppBadge label={unavailable ? 'Unavailable' : 'Available'} tone={unavailable ? 'danger' : 'green'} />
        </View>
        <AppText variant="title" numberOfLines={1}>
          {item.name}
        </AppText>
        <AppText color={colors.text.secondary} numberOfLines={2}>
          {item.description ?? 'Freshly prepared at Arcade Kebab House.'}
        </AppText>
        <View style={styles.popularFooter}>
          <PriceText amount={item.price} />
          <AppButton
            label={ctaLabel}
            variant={unavailable ? 'secondary' : custom || !orderingEnabled ? 'outline' : 'primary'}
            disabled={unavailable}
            onPress={() => onAction(item)}
          />
        </View>
      </View>
    </AppCard>
  );
}

function QuickActionCard({ action }: { action: QuickAction }) {
  function handlePress() {
    router.push(action.href);
  }

  return (
    <Pressable
      accessibilityRole="link"
      accessibilityLabel={action.title}
      onPress={handlePress}
      style={({ pressed }) => [
        styles.quickCard,
        action.emphasized && styles.quickCardPrimary,
        pressed && styles.pressed,
      ]}
    >
      <View style={[styles.actionIcon, action.emphasized && styles.actionIconPrimary]}>
        <AppIcon
          name={action.icon}
          size={22}
          color={action.emphasized ? colors.text.inverse : colors.brand.primary}
        />
      </View>
      <View style={styles.quickCopy}>
        <AppText variant="title" numberOfLines={1}>
          {action.title}
        </AppText>
        <AppText color={colors.text.secondary} numberOfLines={2}>
          {action.description}
        </AppText>
      </View>
    </Pressable>
  );
}

function MiniStat({ label, value }: { label: string; value: string | React.ReactNode }) {
  return (
    <View style={styles.miniStat}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      {typeof value === 'string' ? <AppText variant="caption">{value}</AppText> : value}
    </View>
  );
}

type DetailRowProps = {
  label: string;
  value: string | React.ReactNode;
  badgeTone?: 'brand' | 'gold' | 'green' | 'danger' | 'info' | 'neutral';
};

function DetailRow({ label, value, badgeTone }: DetailRowProps) {
  return (
    <View style={styles.detailRow}>
      <AppText color={colors.text.secondary}>{label}</AppText>
      {badgeTone && typeof value === 'string' ? (
        <AppBadge label={value} tone={badgeTone} />
      ) : typeof value === 'string' ? (
        <AppText variant="caption" style={styles.detailValue}>
          {value}
        </AppText>
      ) : value}
    </View>
  );
}

type StickyOrderBarProps = {
  cartCount: number;
  cartSubtotal: number;
  cartHref: Href;
};

function StickyOrderBar({ cartCount, cartSubtotal, cartHref }: StickyOrderBarProps) {
  const hasItems = cartCount > 0;

  return (
    <View style={styles.stickyBar}>
      <View style={styles.stickyCopy}>
        <AppText variant="caption" color={colors.text.secondary}>
          {hasItems ? `${cartCount} item${cartCount === 1 ? '' : 's'} in cart` : 'Ready when you are'}
        </AppText>
        {hasItems ? (
          <PriceText amount={cartSubtotal} variant="title" />
        ) : (
          <AppText variant="title">Start an order</AppText>
        )}
      </View>
      <Link href={hasItems ? cartHref : '/(public)/menu'} asChild>
        <AppButton label={hasItems ? 'View Cart' : 'Start Order'} />
      </Link>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    alignSelf: 'center',
    width: '100%',
    maxWidth: 1080,
    gap: spacing.xl,
    paddingBottom: 96,
  },
  desktopScreen: {
    paddingBottom: spacing['4xl'],
  },
  hero: {
    position: 'relative',
    gap: spacing.md,
    overflow: 'hidden',
    borderRadius: 28,
    borderWidth: 1,
    borderColor: '#F1D2C2',
    backgroundColor: colors.surface.card,
    padding: spacing.lg,
    ...shadows.card,
  },
  heroGlow: {
    bottom: 0,
    left: 0,
    position: 'absolute',
    right: 0,
    top: 0,
    backgroundColor: '#FFF4EA',
    opacity: 0.86,
  },
  heroWide: {
    flexDirection: 'row',
    alignItems: 'stretch',
  },
  heroCopy: {
    flex: 1,
    minWidth: 260,
    gap: spacing.lg,
  },
  heroKicker: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  securePill: {
    minHeight: 28,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.md,
  },
  heroTitle: {
    maxWidth: 620,
    fontSize: 28,
    lineHeight: 34,
  },
  heroActions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  heroStats: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  miniStat: {
    minWidth: 92,
    flex: 1,
    gap: 2,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: 'rgba(255,255,255,0.82)',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
  },
  heroVisual: {
    minWidth: 230,
    flex: 0.78,
  },
  coverImage: {
    height: 190,
    width: '100%',
    overflow: 'hidden',
    borderRadius: radius.xl,
    backgroundColor: colors.gold.pale,
  },
  foodPlaceholder: {
    minHeight: 190,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    borderRadius: radius.xl,
    backgroundColor: colors.gold.pale,
  },
  closedCard: {
    gap: spacing.md,
    borderColor: colors.gold.DEFAULT,
    backgroundColor: colors.gold.pale,
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
  popularGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.md,
  },
  popularCell: {
    minWidth: 0,
  },
  popularCard: {
    minHeight: 140,
    flexDirection: 'row',
    gap: spacing.sm,
    padding: spacing.md,
  },
  disabledCard: {
    opacity: 0.72,
  },
  popularImageShell: {
    width: 84,
    minHeight: 104,
    overflow: 'hidden',
    borderRadius: radius.lg,
    backgroundColor: colors.gold.pale,
  },
  popularImage: {
    height: '100%',
    width: '100%',
  },
  itemPlaceholder: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.brand.soft,
  },
  popularBody: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  itemBadges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.xs,
  },
  popularFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  quickCard: {
    minHeight: 112,
    minWidth: 150,
    flex: 1,
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: spacing.sm,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  quickCardPrimary: {
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
  },
  actionIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.soft,
  },
  actionIconPrimary: {
    backgroundColor: colors.brand.primary,
  },
  quickCopy: {
    width: '100%',
    gap: spacing.xs,
  },
  orderDetails: {
    gap: spacing.md,
  },
  detailRow: {
    minHeight: 46,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
    borderTopWidth: 1,
    borderTopColor: colors.border.light,
    paddingTop: spacing.md,
  },
  detailValue: {
    flex: 1,
    textAlign: 'right',
  },
  stickyBar: {
    minHeight: 76,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
    borderTopWidth: 1,
    borderTopColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    ...shadows.card,
  },
  stickyCopy: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  pressed: {
    transform: [{ scale: 0.98 }],
    opacity: 0.9,
  },
});
