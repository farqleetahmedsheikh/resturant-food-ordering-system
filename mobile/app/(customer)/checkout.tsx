import { zodResolver } from '@hookform/resolvers/zod';
import { useQuery } from '@tanstack/react-query';
import { router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';
import { Linking, StyleSheet, View } from 'react-native';
import { z } from 'zod';

import { syncLocalCartToBackend } from '@/src/api/cart.api';
import { normalizeApiError } from '@/src/api/api-error';
import { checkout as checkoutApi } from '@/src/api/orders.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { useAuthStore } from '@/src/auth/auth.store';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { AppInput } from '@/src/components/forms/AppInput';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useLocationPermission } from '@/src/hooks/useLocationPermission';
import { useCartStore } from '@/src/store/cart.store';
import { colors, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';
import { compactNote } from '@/src/utils/forms';
import { createIdempotencyKey } from '@/src/utils/idempotency';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

const schema = z.object({
  customer_name: z.string().trim().min(2, 'Enter your name.'),
  customer_phone: z.string().trim().min(6, 'Enter your phone number.').max(30, 'Phone number is too long.'),
  customer_email: z.string().trim().email('Enter a valid email address.').optional().or(z.literal('')),
  delivery_address: z.string().trim().min(8, 'Enter a delivery address.').max(1000, 'Address is too long.'),
  order_notes: z.string().max(700, 'Notes are too long.').optional(),
});

type CheckoutForm = z.infer<typeof schema>;

export default function CustomerCheckoutScreen() {
  const user = useAuthStore((state) => state.session?.user);
  const items = useCartStore((state) => state.items);
  const subtotal = useCartStore((state) => state.getSubtotal());
  const location = useLocationPermission();
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const form = useForm<CheckoutForm>({
    resolver: zodResolver(schema),
    defaultValues: {
      customer_name: user?.name ?? '',
      customer_phone: user?.phone ?? '',
      customer_email: user?.email ?? '',
      delivery_address: '',
      order_notes: '',
    },
  });

  const restaurant = restaurantQuery.data;
  const availability = getRestaurantAvailability(restaurant);
  const deliveryFee = Number(restaurant?.delivery_fee ?? 0);
  const minimumOrder = Number(restaurant?.minimum_order_amount ?? 0);
  const total = subtotal + deliveryFee;
  const belowMinimum = minimumOrder > 0 && subtotal < minimumOrder;
  const checkoutBlocked = items.length === 0 || !availability.isOpenForOrders || belowMinimum;

  async function onSubmit(values: CheckoutForm) {
    if (checkoutBlocked) {
      form.setError('root', {
        message: !availability.isOpenForOrders
          ? availability.reason ?? 'Restaurant is closed now.'
          : belowMinimum
            ? `Minimum order is ${formatCurrency(minimumOrder)}.`
            : 'Your cart is empty.',
      });
      return;
    }

    try {
      await syncLocalCartToBackend(items);

      const itemNotes = items
        .filter((item) => item.notes.trim())
        .map((item) => `${item.name}: ${item.notes.trim()}`)
        .join('\n');

      const result = await checkoutApi(
        {
          customer_name: values.customer_name,
          customer_phone: values.customer_phone,
          customer_email: values.customer_email || null,
          delivery_address: values.delivery_address,
          delivery_latitude: location.coords?.latitude ?? null,
          delivery_longitude: location.coords?.longitude ?? null,
          order_notes: compactNote([values.order_notes, itemNotes ? `Item notes:\n${itemNotes}` : null]),
        },
        createIdempotencyKey(),
      );

      await Linking.openURL(result.checkout_url);
      router.replace(`/(customer)/orders/${result.order_id}`);
    } catch (error) {
      const normalized = normalizeApiError(error);
      Object.entries(normalized.validationErrors).forEach(([field, messages]) => {
        form.setError(field as keyof CheckoutForm, { message: messages[0] });
      });
      form.setError('root', { message: normalized.message });
    }
  }

  if (restaurantQuery.isLoading) {
    return <LoadingState label="Loading checkout..." />;
  }

  return (
    <AppScreen keyboard>
      <AppHeader title="Checkout" subtitle="Pay securely by card through Stripe Checkout." eyebrow="Customer" />

      {items.length === 0 ? (
        <EmptyState title="Your cart is empty" message="Add an item before checkout." />
      ) : (
        <>
          {!availability.isOpenForOrders ? (
            <FeedbackMessage tone="warning" message={availability.reason ?? 'Ordering is paused right now.'} />
          ) : null}
          {belowMinimum ? (
            <FeedbackMessage tone="warning" message={`Minimum order is ${formatCurrency(minimumOrder)}.`} />
          ) : null}
          {form.formState.errors.root ? (
            <FeedbackMessage tone="error" message={form.formState.errors.root.message ?? 'Unable to place order.'} />
          ) : null}

          <AppCard style={styles.card}>
            <SectionTitle title="Cart summary" subtitle={`${items.length} saved line item${items.length === 1 ? '' : 's'}`} />
            {items.map((item) => (
              <View key={item.menuItemId} style={styles.summaryRow}>
                <View style={styles.flex}>
                  <AppText variant="title" numberOfLines={1}>
                    {item.quantity} x {item.name}
                  </AppText>
                  <AppText color={colors.text.secondary}>{formatCurrency(item.unitPrice)} each</AppText>
                </View>
                <PriceText amount={item.unitPrice * item.quantity} />
              </View>
            ))}
            <View style={styles.divider} />
            <SummaryRow label="Subtotal" amount={subtotal} />
            <SummaryRow label="Delivery fee" amount={deliveryFee} />
            <SummaryRow label="Total" amount={total} strong />
          </AppCard>

          <AppCard style={styles.card}>
            <SectionTitle title="Delivery details" subtitle="Manual delivery address is required." />
            <Controller
              control={form.control}
              name="customer_name"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Name"
                  value={field.value}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
                  error={fieldState.error?.message}
                />
              )}
            />
            <Controller
              control={form.control}
              name="customer_phone"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Phone"
                  value={field.value}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
                  keyboardType="phone-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
            <Controller
              control={form.control}
              name="customer_email"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Email"
                  value={field.value ?? ''}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
                  keyboardType="email-address"
                  error={fieldState.error?.message}
                />
              )}
            />
            <Controller
              control={form.control}
              name="delivery_address"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Delivery address"
                  value={field.value}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
                  multiline
                  numberOfLines={3}
                  textAlignVertical="top"
                  style={styles.addressInput}
                  error={fieldState.error?.message}
                />
              )}
            />
            <Controller
              control={form.control}
              name="order_notes"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Delivery notes"
                  value={field.value ?? ''}
                  onChangeText={field.onChange}
                  onBlur={field.onBlur}
                  multiline
                  numberOfLines={3}
                  textAlignVertical="top"
                  style={styles.addressInput}
                  error={fieldState.error?.message}
                />
              )}
            />
            <AppButton
              label={location.coords ? 'Location saved' : 'Use my current location'}
              variant="outline"
              loading={location.loading}
              onPress={() => void location.requestCurrentLocation()}
            />
            {location.error ? <FeedbackMessage tone="warning" message={location.error} /> : null}
            {location.coords ? (
              <FeedbackMessage
                tone="success"
                message={`Coordinates ready: ${location.coords.latitude.toFixed(5)}, ${location.coords.longitude.toFixed(5)}`}
              />
            ) : null}
            <AppButton
              label="Pay securely with card"
              loading={form.formState.isSubmitting}
              disabled={checkoutBlocked}
              fullWidth
              onPress={form.handleSubmit(onSubmit)}
            />
          </AppCard>
        </>
      )}
    </AppScreen>
  );
}

function SummaryRow({ label, amount, strong = false }: { label: string; amount: number; strong?: boolean }) {
  return (
    <View style={styles.summaryRow}>
      <AppText variant={strong ? 'title' : 'body'}>{label}</AppText>
      <PriceText amount={amount} variant={strong ? 'title' : 'body'} />
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: spacing.lg,
  },
  summaryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  flex: {
    flex: 1,
  },
  divider: {
    height: 1,
    backgroundColor: colors.border.light,
  },
  addressInput: {
    minHeight: 88,
  },
});
