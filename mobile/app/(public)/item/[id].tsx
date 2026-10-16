import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link, useLocalSearchParams } from 'expo-router';
import { useMemo, useState } from 'react';
import type { SetStateAction } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { getMenuItem } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { QuantityStepper } from '@/src/components/common/QuantityStepper';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { AppInput } from '@/src/components/forms/AppInput';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore, type CartAddon } from '@/src/store/cart.store';
import type { CustomizationSnapshot } from '@/src/types/cart';
import { colors, radius, spacing } from '@/src/theme';
import { formatCurrency } from '@/src/utils/currency';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

export default function MenuItemDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const [quantity, setQuantity] = useState(1);
  const [selectedSizeId, setSelectedSizeId] = useState<number | null>(null);
  const [selectedAddonIds, setSelectedAddonIds] = useState<number[]>([]);
  const [editedAddonQuantities, setEditedAddonQuantities] = useState<{
    itemId: string;
    values: Record<number, Record<number, number>>;
  } | null>(null);
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

  const itemId = String(itemQuery.data?.id ?? id);
  const defaultAddonQuantities = useMemo(() => {
    return itemQuery.data?.customization_groups?.reduce<Record<number, Record<number, number>>>((result, group) => {
      const groupDefaults = (group.addons ?? []).reduce<Record<number, number>>((values, addon) => {
        if ((addon as typeof addon & { is_default?: boolean }).is_default) values[addon.id] = 1;
        return values;
      }, {});
      if (Object.keys(groupDefaults).length > 0) result[group.id] = groupDefaults;
      return result;
    }, {}) ?? {};
  }, [itemQuery.data?.customization_groups]);
  const selectedAddonQuantities = editedAddonQuantities?.itemId === itemId
    ? editedAddonQuantities.values
    : defaultAddonQuantities;

  function setSelectedAddonQuantities(action: SetStateAction<Record<number, Record<number, number>>>) {
    setEditedAddonQuantities((current) => {
      const values = current?.itemId === itemId ? current.values : defaultAddonQuantities;
      return {
        itemId,
        values: typeof action === 'function' ? action(values) : action,
      };
    });
  }

  if (itemQuery.isLoading || restaurantQuery.isLoading) {
    return <LoadingScreen label="Loading item..." />;
  }

  if (itemQuery.isError || !itemQuery.data) {
    return <ErrorState message="Unable to load this item." onRetry={() => void itemQuery.refetch()} />;
  }

  const item = itemQuery.data;
  const sizes = item.sizes ?? [];
  const addons = item.addons ?? [];
  const groups = item.customization_groups ?? [];
  const selectedSize = sizes.find((size) => size.id === selectedSizeId) ?? sizes[0] ?? null;
  const groupedAddons: CartAddon[] = groups.flatMap((group) => group.addons ?? []).flatMap((addon) => {
    const quantity = groups.reduce((total, group) => total + Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 0), 0);
    const isDefault = groups.some((group) => (group.addons ?? []).some((entry) => entry.id === addon.id && (entry as typeof entry & { is_default?: boolean }).is_default));
    return quantity > 0 ? [{ ...addon, price: isDefault ? 0 : addon.price, quantity }] : [];
  });
  const selectedAddons: CartAddon[] = groups.length ? groupedAddons : addons.filter((addon) => selectedAddonIds.includes(addon.id));
  const basePrice = Number(selectedSize?.price ?? item.price);
  const addonsTotal = selectedAddons.reduce((sum, addon) => sum + Number(addon.price ?? 0) * Number(addon.quantity ?? 1), 0);
  const customizationSnapshot: CustomizationSnapshot[] = groups.map((group) => ({
    group_id: group.id,
    group_name: group.name,
    selected: (group.addons ?? []).filter((addon) => Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 0) > 0).map((addon) => ({
      name: addon.name,
      quantity: Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 1),
      price: (addon as typeof addon & { is_default?: boolean }).is_default ? 0 : Number(addon.price ?? 0),
    })),
    removed: (group.addons ?? []).filter((addon) => Boolean((addon as typeof addon & { is_default?: boolean }).is_default) && Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 0) === 0).map((addon) => ({ name: addon.name, quantity: 0, price: 0 })),
  }));
  const unitPrice = basePrice + addonsTotal;
  const availability = getRestaurantAvailability(restaurantQuery.data);
  const disabled = !availability.isOpenForOrders || !item.is_available;
  const disabledMessage = !item.is_available
    ? 'This item is currently unavailable.'
    : availability.reason ?? 'Ordering is currently paused.';

  function addToCart() {
    for (const group of groups) {
      const count = Object.values(selectedAddonQuantities[group.id] ?? {}).reduce((sum, value) => sum + Number(value || 0), 0);
      const minimum = group.is_required ? Math.max(1, group.min_selections) : group.min_selections;
      const maximum = group.selection_type === 'single' ? 1 : group.max_selections;
      if (count < minimum || (maximum !== null && count > maximum)) {
        setMessage(`${group.name}: choose ${minimum === maximum ? minimum : `between ${minimum} and ${maximum ?? 'the allowed number'}`} option(s).`);
        return;
      }
    }
    const addonSelections = groups.length
      ? Object.fromEntries(groups.map((group) => [
        group.id,
        Object.fromEntries((group.addons ?? []).map((addon) => [addon.id, Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 0)])),
      ]))
      : undefined;
    addItem({ item, quantity, size: selectedSize, addons: selectedAddons, addonSelections, customizations: customizationSnapshot, notes });
    setMessage(`${quantity} x ${item.name} added to cart.`);
  }

  function toggleAddon(addonId: number) {
    setSelectedAddonIds((current) =>
      current.includes(addonId) ? current.filter((id) => id !== addonId) : [...current, addonId],
    );
  }

  function toggleGroupedAddon(groupId: number, addonId: number) {
    setSelectedAddonQuantities((current) => {
      const group = groups.find((entry) => entry.id === groupId);
      const existing = Number(current[groupId]?.[addonId] ?? 0);
      if (group?.selection_type === 'single') {
        return { ...current, [groupId]: { [addonId]: existing > 0 ? 0 : 1 } };
      }
      return { ...current, [groupId]: { ...(current[groupId] ?? {}), [addonId]: existing > 0 ? 0 : 1 } };
    });
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
        <PriceText amount={unitPrice} variant="h2" />

        {message ? <FeedbackMessage tone="success" message={message} /> : null}
        {disabled ? <FeedbackMessage tone="warning" message={disabledMessage} /> : null}

        {sizes.length > 0 ? (
          <View style={styles.optionGroup}>
            <SectionTitle title="Choose a size" subtitle="Required for this item" />
            <View style={styles.optionGrid}>
              {sizes.map((size) => {
                const active = selectedSize?.id === size.id;

                return (
                  <Pressable
                    key={size.id}
                    accessibilityRole="button"
                    accessibilityState={{ selected: active }}
                    disabled={disabled}
                    onPress={() => setSelectedSizeId(size.id)}
                    style={[styles.optionCard, active && styles.optionCardActive]}
                  >
                    <AppText variant="title">{size.name}</AppText>
                    <AppText color={active ? colors.brand.primary : colors.text.secondary}>
                      {formatCurrency(size.price)}
                    </AppText>
                  </Pressable>
                );
              })}
            </View>
          </View>
        ) : null}

        {addons.length > 0 && groups.length === 0 ? (
          <View style={styles.optionGroup}>
            <SectionTitle title="Add extras" subtitle="Optional toppings and sauces" />
            <View style={styles.optionGrid}>
              {addons.map((addon) => {
                const active = selectedAddonIds.includes(addon.id);

                return (
                  <Pressable
                    key={addon.id}
                    accessibilityRole="checkbox"
                    accessibilityState={{ checked: active }}
                    disabled={disabled}
                    onPress={() => toggleAddon(addon.id)}
                    style={[styles.optionCard, active && styles.optionCardActive]}
                  >
                    <AppText variant="title">{addon.name}</AppText>
                    <AppText color={active ? colors.brand.primary : colors.text.secondary}>
                      + {formatCurrency(addon.price)}
                    </AppText>
                  </Pressable>
                );
              })}
            </View>
          </View>
        ) : null}

        {groups.map((group) => (
          <View key={group.id} style={styles.optionGroup}>
            <SectionTitle title={group.name} subtitle={group.description ?? (group.is_required ? 'Required selection' : 'Optional selection')} />
            <View style={styles.optionGrid}>
              {(group.addons ?? []).map((addon) => {
                const selected = Number(selectedAddonQuantities[group.id]?.[addon.id] ?? 0);
                return (
                  <Pressable key={addon.id} accessibilityRole={group.selection_type === 'single' ? 'radio' : 'checkbox'} accessibilityState={{ checked: selected > 0 }} disabled={disabled} onPress={() => toggleGroupedAddon(group.id, addon.id)} style={[styles.optionCard, selected > 0 && styles.optionCardActive]}>
                    <AppText variant="title">{addon.name}</AppText>
                    <AppText color={selected > 0 ? colors.brand.primary : colors.text.secondary}>+ {formatCurrency(addon.price)}{selected > 1 ? ` x${selected}` : ''}</AppText>
                    {group.quantity_limit > 1 && selected > 0 ? <View style={styles.quantityRow}><Pressable onPress={() => setSelectedAddonQuantities((current) => ({ ...current, [group.id]: { ...(current[group.id] ?? {}), [addon.id]: Math.max(0, selected - 1) } }))}><AppText color={colors.brand.primary}>-</AppText></Pressable><AppText>{selected}</AppText><Pressable disabled={selected >= group.quantity_limit} onPress={() => setSelectedAddonQuantities((current) => ({ ...current, [group.id]: { ...(current[group.id] ?? {}), [addon.id]: selected + 1 } }))}><AppText color={colors.brand.primary}>+</AppText></Pressable></View> : null}
                  </Pressable>
                );
              })}
            </View>
          </View>
        ))}

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
            <PriceText amount={unitPrice * quantity} />
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
  optionGroup: {
    gap: spacing.md,
  },
  optionGrid: {
    gap: spacing.md,
  },
  optionCard: {
    minHeight: 72,
    justifyContent: 'center',
    gap: spacing.xs,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.strong,
    backgroundColor: colors.surface.card,
    padding: spacing.lg,
  },
  optionCardActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.soft,
  },
  quantityRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginTop: spacing.sm,
  },
});
