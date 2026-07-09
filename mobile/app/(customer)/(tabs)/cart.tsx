import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { QuantityStepper } from '@/src/components/common/QuantityStepper';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, shadows, spacing } from '@/src/theme';
import type { LocalCartItem } from '@/src/types/cart';
import { formatCurrency } from '@/src/utils/currency';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

export default function CustomerCartScreen() {
  const items = useCartStore((state) => state.items);
  const removeItem = useCartStore((state) => state.removeItem);
  const updateQuantity = useCartStore((state) => state.updateQuantity);
  const updateNotes = useCartStore((state) => state.updateNotes);
  const clearCart = useCartStore((state) => state.clearCart);
  const subtotal = useCartStore((state) => state.getSubtotal());
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  const restaurant = restaurantQuery.data;
  const availability = getRestaurantAvailability(restaurant);
  const deliveryFee = Number(restaurant?.delivery_fee ?? 0);
  const minimumOrder = Number(restaurant?.minimum_order_amount ?? 0);
  const total = subtotal + deliveryFee;
  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);
  const belowMinimum = minimumOrder > 0 && subtotal < minimumOrder;
  const checkoutDisabled = items.length === 0 || !availability.isOpenForOrders || belowMinimum;
  const checkoutMessage = !availability.isOpenForOrders
    ? availability.reason ?? 'Checkout is available when ordering reopens.'
    : belowMinimum
      ? `Add ${formatCurrency(minimumOrder - subtotal)} more to reach the minimum order.`
      : null;

  function refresh() {
    void restaurantQuery.refetch();
  }

  return (
    <AppScreen
      refreshing={restaurantQuery.isRefetching}
      onRefresh={refresh}
      contentStyle={styles.screen}
      footer={items.length > 0 ? (
        <CartCheckoutBar
          itemCount={itemCount}
          total={total}
          disabled={checkoutDisabled}
        />
      ) : null}
    >
      <View style={styles.hero}>
        <View style={styles.heroTop}>
          <View style={styles.heroIcon}>
            <AppIcon name={appIcons.cart} color={colors.text.inverse} size={20} />
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>CUSTOMER</AppText>
            <AppText variant="h2">Your cart</AppText>
            <AppText color={colors.text.secondary}>{itemCount} item{itemCount === 1 ? '' : 's'} saved for checkout.</AppText>
          </View>
          <AppBadge label={availability.isOpenForOrders ? 'Open' : 'Paused'} tone={availability.isOpenForOrders ? 'green' : 'gold'} />
        </View>
        <View style={styles.heroTotals}>
          <SummaryTile label="Subtotal" value={subtotal} />
          <SummaryTile label="Delivery" value={deliveryFee} />
          <SummaryTile label="Total" value={total} />
        </View>
      </View>
      {restaurantQuery.isLoading ? <LoadingState label="Loading restaurant settings..." /> : null}

      {!availability.isOpenForOrders ? (
        <FeedbackMessage tone="warning" message={checkoutMessage ?? 'Ordering is paused right now.'} />
      ) : null}

      {items.length === 0 ? (
        <EmptyState title="Your cart is empty" message="Add items from the menu and they will stay here after app reload." />
      ) : (
        <>
          <View style={styles.list}>
            {items.map((item) => (
              <CartItemRow
                key={item.lineKey}
                item={item}
                onQuantityChange={(quantity) => updateQuantity(item.lineKey, quantity)}
                onNotesChange={(notes) => updateNotes(item.lineKey, notes)}
                onRemove={() => removeItem(item.lineKey)}
              />
            ))}
          </View>

          <AppCard style={styles.summary}>
            <SectionTitle title="Summary" subtitle="Final pricing is confirmed by Laravel during checkout." />
            <SummaryRow label="Subtotal" value={subtotal} />
            <SummaryRow label="Delivery fee" value={deliveryFee} />
            <SummaryRow label="Minimum order" value={minimumOrder} />
            <View style={styles.divider} />
            <SummaryRow label="Total" value={total} strong />
            {checkoutMessage ? <FeedbackMessage tone="warning" message={checkoutMessage} /> : null}
            <Link href="/(customer)/checkout" asChild>
              <AppButton label="Checkout" fullWidth disabled={checkoutDisabled} />
            </Link>
            <AppButton label="Clear cart" variant="outline" fullWidth onPress={clearCart} />
          </AppCard>
        </>
      )}
    </AppScreen>
  );
}

function CartCheckoutBar({ itemCount, total, disabled }: { itemCount: number; total: number; disabled: boolean }) {
  return (
    <View style={styles.checkoutBar}>
      <View style={styles.flex}>
        <AppText variant="caption" color={colors.text.secondary}>
          {itemCount} item{itemCount === 1 ? '' : 's'} ready
        </AppText>
        <PriceText amount={total} variant="title" />
      </View>
      <Link href="/(customer)/checkout" asChild>
        <AppButton label="Checkout" disabled={disabled} />
      </Link>
    </View>
  );
}

function CartItemRow({
  item,
  onQuantityChange,
  onNotesChange,
  onRemove,
}: {
  item: LocalCartItem;
  onQuantityChange: (quantity: number) => void;
  onNotesChange: (notes: string) => void;
  onRemove: () => void;
}) {
  return (
    <AppCard style={styles.itemCard}>
      <View style={styles.itemTop}>
        <View style={styles.thumbnail}>
          {item.imageUrl ? (
            <Image source={{ uri: item.imageUrl }} style={styles.thumbnailImage} contentFit="cover" />
          ) : (
            <AppText variant="title" color={colors.brand.primary}>
              {item.name.slice(0, 1).toUpperCase()}
            </AppText>
          )}
        </View>
        <View style={styles.itemCopy}>
          <AppText variant="title" numberOfLines={1}>
            {item.name}
          </AppText>
          <AppText color={colors.text.secondary} numberOfLines={1}>
            {item.sizeName ? `${item.sizeName} · ` : ''}{item.addons.length > 0 ? item.addons.map((addon) => addon.name).join(', ') : 'No add-ons'}
          </AppText>
          {!item.isAvailable ? (
            <AppText variant="caption" color={colors.semantic.danger}>
              Availability will be checked at checkout.
            </AppText>
          ) : null}
        </View>
        <View style={styles.itemPrice}>
          <PriceText amount={item.unitPrice * item.quantity} />
          <AppText variant="caption" color={colors.text.secondary}>
            {formatCurrency(item.unitPrice)} each
          </AppText>
        </View>
      </View>
      <View style={styles.itemControls}>
        <QuantityStepper value={item.quantity} onChange={onQuantityChange} size="compact" />
        <View style={styles.notesShell}>
          <TextInput
            accessibilityLabel={`Notes for ${item.name}`}
            placeholder="Add note"
            placeholderTextColor={colors.text.muted}
            value={item.notes}
            onChangeText={onNotesChange}
            numberOfLines={1}
            style={styles.notesInput}
          />
        </View>
        <Pressable accessibilityRole="button" onPress={onRemove} style={styles.removeChip}>
          <AppText variant="caption" color={colors.brand.primary}>
            Remove
          </AppText>
        </Pressable>
      </View>
    </AppCard>
  );
}

function SummaryTile({ label, value }: { label: string; value: number }) {
  return (
    <View style={styles.summaryTile}>
      <AppText variant="caption" color={colors.text.secondary}>{label}</AppText>
      <PriceText amount={value} variant="title" />
    </View>
  );
}

function SummaryRow({ label, value, strong = false }: { label: string; value: number; strong?: boolean }) {
  return (
    <View style={styles.summaryRow}>
      <AppText variant={strong ? 'title' : 'body'}>{label}</AppText>
      <PriceText amount={value} variant={strong ? 'title' : 'body'} />
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.md,
    paddingBottom: 104,
  },
  hero: {
    gap: spacing.md,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    padding: spacing.md,
    ...shadows.card,
  },
  heroTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  heroIcon: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.primary,
  },
  heroTotals: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  summaryTile: {
    flex: 1,
    gap: 2,
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.md,
  },
  list: {
    gap: spacing.sm,
  },
  itemCard: {
    gap: spacing.sm,
    padding: spacing.md,
  },
  itemTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  thumbnail: {
    height: 54,
    width: 54,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    borderRadius: radius.lg,
    backgroundColor: colors.gold.pale,
  },
  thumbnailImage: {
    height: '100%',
    width: '100%',
  },
  itemCopy: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  itemPrice: {
    minWidth: 76,
    alignItems: 'flex-end',
    gap: 2,
  },
  itemControls: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  notesShell: {
    flex: 1,
    minWidth: 0,
    minHeight: 36,
    justifyContent: 'center',
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.muted,
  },
  notesInput: {
    minHeight: 36,
    paddingHorizontal: spacing.md,
    color: colors.text.primary,
    fontSize: 13,
    fontWeight: '600',
  },
  removeChip: {
    minHeight: 36,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.md,
  },
  summary: {
    gap: spacing.md,
  },
  summaryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  divider: {
    height: 1,
    backgroundColor: colors.border.light,
  },
  checkoutBar: {
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
  flex: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
});
