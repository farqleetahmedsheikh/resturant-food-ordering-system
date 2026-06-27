import { useQuery } from '@tanstack/react-query';
import { useMemo, useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { getCategories, getMenuItems } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { AppText } from '@/src/components/common/AppText';
import { SectionTitle } from '@/src/components/common/SectionTitle';
import { AppInput } from '@/src/components/forms/AppInput';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { LoadingState } from '@/src/components/feedback/LoadingState';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { MenuItemCard } from '@/src/components/menu/MenuItemCard';
import { queryKeys } from '@/src/constants/queryKeys';
import { useCartStore } from '@/src/store/cart.store';
import { colors, radius, spacing } from '@/src/theme';
import type { MenuItem } from '@/src/types/menu';
import { getRestaurantAvailability } from '@/src/utils/restaurant';

type MenuBrowserProps = {
  mode: 'public' | 'customer';
};

export function MenuBrowser({ mode }: MenuBrowserProps) {
  const [category, setCategory] = useState<string | number | undefined>();
  const [search, setSearch] = useState('');
  const [feedback, setFeedback] = useState<string | null>(null);
  const addItem = useCartStore((state) => state.addItem);

  const params = useMemo(
    () => ({
      include_unavailable: true,
      per_page: 50,
      category,
      search: search.trim() || undefined,
    }),
    [category, search],
  );

  const restaurantQuery = useQuery({
    queryKey: queryKeys.restaurant,
    queryFn: getRestaurant,
  });
  const categoriesQuery = useQuery({
    queryKey: queryKeys.categories,
    queryFn: getCategories,
  });
  const menuQuery = useQuery({
    queryKey: queryKeys.menuItems(params),
    queryFn: () => getMenuItems(params),
  });

  const restaurant = restaurantQuery.data;
  const availability = getRestaurantAvailability(restaurant);
  const isLoading = restaurantQuery.isLoading || categoriesQuery.isLoading || menuQuery.isLoading;
  const isError = restaurantQuery.isError || categoriesQuery.isError || menuQuery.isError;
  const refreshing = restaurantQuery.isRefetching || categoriesQuery.isRefetching || menuQuery.isRefetching;
  const items = menuQuery.data?.items ?? [];
  const categories = categoriesQuery.data ?? [];
  const addDisabled = mode === 'customer' && !availability.isOpenForOrders;

  function retry() {
    void restaurantQuery.refetch();
    void categoriesQuery.refetch();
    void menuQuery.refetch();
  }

  function refresh() {
    retry();
  }

  function handleAdd(item: MenuItem) {
    addItem({ item, quantity: 1 });
    setFeedback(`${item.name} added to cart.`);
  }

  return (
    <AppScreen refreshing={refreshing} onRefresh={refresh} contentStyle={styles.screen}>
      <AppHeader
        title={mode === 'customer' ? 'Menu' : 'Browse Menu'}
        subtitle={mode === 'customer' ? availability.label : 'Search the live Arcade Kebab House menu.'}
        eyebrow={mode === 'customer' ? 'Customer' : 'Public'}
      />

      {mode === 'customer' && !availability.isOpenForOrders ? (
        <FeedbackMessage
          tone="warning"
          message={availability.reason ?? 'Ordering is paused. You can browse now and checkout when ordering reopens.'}
        />
      ) : null}

      {feedback ? <FeedbackMessage tone="success" message={feedback} /> : null}

      <AppInput
        label="Search"
        placeholder="Search kebabs, burgers, sides..."
        value={search}
        onChangeText={setSearch}
        autoCorrect={false}
        returnKeyType="search"
      />

      <View style={styles.filters}>
        <FilterChip label="All" selected={!category} onPress={() => setCategory(undefined)} />
        {categories.map((item) => (
          <FilterChip
            key={item.id}
            label={item.name}
            selected={category === item.id}
            onPress={() => setCategory(item.id)}
          />
        ))}
      </View>

      {isLoading ? <LoadingState label="Loading menu..." /> : null}

      {isError ? <ErrorState message="Unable to load menu items." onRetry={retry} /> : null}

      {!isLoading && !isError ? (
        <View style={styles.list}>
          <SectionTitle title="Menu items" subtitle={`${items.length} item${items.length === 1 ? '' : 's'} found`} />
          {items.length === 0 ? (
            <EmptyState title="No items found" message="Try a different search or category." />
          ) : (
            items.map((item) => (
              <MenuItemCard
                key={item.id}
                item={item}
                detailHref={mode === 'public' ? `/(public)/item/${item.id}` : undefined}
                showAddButton={mode === 'customer'}
                addDisabled={addDisabled}
                addDisabledReason={addDisabled ? availability.label : undefined}
                onAdd={handleAdd}
              />
            ))
          )}
        </View>
      ) : null}
    </AppScreen>
  );
}

type FilterChipProps = {
  label: string;
  selected: boolean;
  onPress: () => void;
};

function FilterChip({ label, selected, onPress }: FilterChipProps) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ selected }}
      onPress={onPress}
      style={[styles.chip, selected && styles.selectedChip]}
    >
      <AppText variant="caption" color={selected ? colors.text.inverse : colors.text.secondary}>
        {label}
      </AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.lg,
  },
  filters: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  chip: {
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.strong,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.lg,
  },
  selectedChip: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  list: {
    gap: spacing.md,
  },
});
