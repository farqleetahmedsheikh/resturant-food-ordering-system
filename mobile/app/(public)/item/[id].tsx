import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link, useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';

import { getMenuItem } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { QuantityStepper } from '@/src/components/common/QuantityStepper';
import { AppInput } from '@/src/components/forms/AppInput';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, spacing } from '@/src/theme';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

export default function MenuItemDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const [quantity, setQuantity] = useState(1);
  const [notes, setNotes] = useState('');
  const [message, setMessage] = useState<string | null>(null);
  const addItem = useCartStore((state) => state.addItem);

  const itemQuery = useQuery({
    queryKey: ['menu-item', id],
    queryFn: () => getMenuItem(id),
    enabled: Boolean(id),
  });
  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });

  if (itemQuery.isLoading || restaurantQuery.isLoading) {
    return <LoadingScreen label="Loading item..." />;
  }

  if (itemQuery.isError || !itemQuery.data) {
    return <ErrorState message="Unable to load this item." onRetry={() => void itemQuery.refetch()} />;
  }

  const item = itemQuery.data;
  const availability = getRestaurantAvailability(restaurantQuery.data);
  const disabled = !availability.isOpenForOrders || !item.is_available;
  const disabledMessage = !item.is_available
    ? 'This item is currently unavailable.'
    : availability.reason ?? 'Ordering is currently paused.';

  function addToCart() {
    addItem({ item, quantity, notes });
    setMessage(`${quantity} x ${item.name} added to cart.`);
  }

  return (
    <AppScreen keyboard>
      <View style={styles.imageShell}>
        {item.image_url ? (
          <Image source={{ uri: item.image_url }} style={styles.image} contentFit="cover" />
        ) : (
          <AppText variant="h1" color={colors.brand.primary}>
            {item.name.slice(0, 1).toUpperCase()}
          </AppText>
        )}
      </View>

      <AppCard style={styles.card}>
        <View style={styles.badges}>
          <AppBadge label={item.category?.name ?? 'Menu item'} tone="neutral" />
          {item.is_featured ? <AppBadge label="Popular" tone="gold" /> : null}
          <AppBadge label={item.is_available ? 'Available' : 'Unavailable'} tone={item.is_available ? 'green' : 'danger'} />
        </View>
        <AppText variant="h1">{item.name}</AppText>
        <AppText color={colors.text.secondary}>{item.description ?? 'Freshly prepared for every order.'}</AppText>
        <PriceText amount={item.price} variant="h2" />

        {message ? <FeedbackMessage tone="success" message={message} /> : null}
        {disabled ? <FeedbackMessage tone="warning" message={disabledMessage} /> : null}

        <View style={styles.row}>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.text.secondary}>
              Quantity
            </AppText>
            <QuantityStepper value={quantity} onChange={setQuantity} disabled={disabled} />
          </View>
          <View style={styles.total}>
            <AppText variant="caption" color={colors.text.secondary}>
              Item total
            </AppText>
            <PriceText amount={item.price * quantity} />
          </View>
        </View>

        <AppInput
          label="Notes"
          placeholder="Optional kitchen note"
          value={notes}
          onChangeText={setNotes}
          multiline
          numberOfLines={3}
          textAlignVertical="top"
          style={styles.notesInput}
        />

        <AppButton label="Add to cart" disabled={disabled} fullWidth onPress={addToCart} />
        <Link href="/(auth)/login" asChild>
          <AppButton label="Sign in to checkout" variant="outline" fullWidth />
        </Link>
      </AppCard>
    </AppScreen>
  );
}

const styles = StyleSheet.create({
  imageShell: {
    minHeight: 220,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    borderRadius: radius.xl,
    backgroundColor: colors.gold.pale,
  },
  image: {
    height: 240,
    width: '100%',
  },
  card: {
    gap: spacing.lg,
  },
  badges: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  row: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    alignItems: 'center',
    gap: spacing.lg,
  },
  flex: {
    flex: 1,
    minWidth: 150,
    gap: spacing.sm,
  },
  total: {
    minWidth: 120,
    gap: spacing.sm,
  },
  notesInput: {
    minHeight: 88,
  },
});
