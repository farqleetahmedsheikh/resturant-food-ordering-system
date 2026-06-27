import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link } from 'expo-router';
import { StyleSheet, View } from 'react-native';

import { getRestaurant } from '@/src/api/restaurant.api';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { QuantityStepper } from '@/src/components/common/QuantityStepper';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { AppInput } from '@/src/components/forms/AppInput';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, spacing } from '@/src/theme';
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
    <AppScreen refreshing={restaurantQuery.isRefetching} onRefresh={refresh}>
      <AppHeader title="Cart" subtitle={`${itemCount} item${itemCount === 1 ? '' : 's'} saved on this device.`} eyebrow="Customer" />

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
                key={item.menuItemId}
                item={item}
                onQuantityChange={(quantity) => updateQuantity(item.menuItemId, quantity)}
                onNotesChange={(notes) => updateNotes(item.menuItemId, notes)}
                onRemove={() => removeItem(item.menuItemId)}
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
          <AppText variant="title" numberOfLines={2}>
            {item.name}
          </AppText>
          <PriceText amount={item.unitPrice} />
          {!item.isAvailable ? (
            <AppText variant="caption" color={colors.semantic.danger}>
              This item may be unavailable. Checkout will confirm with Laravel.
            </AppText>
          ) : null}
        </View>
      </View>
      <View style={styles.itemControls}>
        <QuantityStepper value={item.quantity} onChange={onQuantityChange} />
        <PriceText amount={item.unitPrice * item.quantity} />
      </View>
      <AppInput
        label="Item notes"
        placeholder="Optional kitchen note"
        value={item.notes}
        onChangeText={onNotesChange}
        multiline
        numberOfLines={2}
        textAlignVertical="top"
        style={styles.notesInput}
      />
      <AppButton label="Remove" variant="outline" onPress={onRemove} />
    </AppCard>
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
  list: {
    gap: spacing.md,
  },
  itemCard: {
    gap: spacing.lg,
  },
  itemTop: {
    flexDirection: 'row',
    gap: spacing.md,
  },
  thumbnail: {
    height: 76,
    width: 76,
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
    gap: spacing.xs,
  },
  itemControls: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  notesInput: {
    minHeight: 72,
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
});
