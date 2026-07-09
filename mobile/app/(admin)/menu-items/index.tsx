import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { Link, router } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';

import { getAdminCategories, getAdminMenuItems } from '@/src/api/admin.api';
import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { AppInput } from '@/src/components/forms/AppInput';
import { EmptyState } from '@/src/components/feedback/EmptyState';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { AdminMenuItemsParams } from '@/src/types/admin';
import type { Category, MenuItem } from '@/src/types/menu';

type AvailabilityFilter = '' | 'available' | 'unavailable';

const availabilityFilters: { label: string; value: AvailabilityFilter }[] = [
  { label: 'All', value: '' },
  { label: 'Available', value: 'available' },
  { label: 'Hidden', value: 'unavailable' },
];

export default function AdminMenuItemsScreen() {
  const [search, setSearch] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [availability, setAvailability] = useState<AvailabilityFilter>('');
  const params = useMemo<AdminMenuItemsParams>(
    () => ({
      search: search.trim() || undefined,
      category_id: categoryId || undefined,
      availability: availability || undefined,
      per_page: 75,
    }),
    [availability, categoryId, search],
  );
  const categoriesQuery = useQuery({
    queryKey: queryKeys.adminCategories,
    queryFn: getAdminCategories,
  });
  const itemsQuery = useQuery({
    queryKey: queryKeys.adminMenuItems(params),
    queryFn: () => getAdminMenuItems(params),
  });

  if (itemsQuery.isLoading) {
    return <LoadingScreen label="Loading menu manager..." />;
  }

  if (itemsQuery.isError) {
    return <ErrorState message="Unable to load menu items." onRetry={() => void itemsQuery.refetch()} />;
  }

  const items = itemsQuery.data?.items ?? [];
  const categories = categoriesQuery.data ?? [];
  const availableCount = items.filter((item) => item.is_available).length;
  const featuredCount = items.filter((item) => item.is_featured).length;

  return (
    <AppScreen
      refreshing={itemsQuery.isRefetching || categoriesQuery.isRefetching}
      onRefresh={() => {
        void itemsQuery.refetch();
        void categoriesQuery.refetch();
      }}
      contentStyle={styles.screen}
    >
      <AppHeader title="Menu items" subtitle="Add, update, and control what customers can order." eyebrow="Admin" />

      <AppCard style={styles.heroCard}>
        <View style={styles.heroTop}>
          <View style={styles.heroIcon}>
            <AppIcon name={appIcons.menu} size={20} color={colors.text.inverse} />
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>
              LIVE MENU CONTROL
            </AppText>
            <AppText variant="h2">{items.length} loaded</AppText>
            <AppText color={colors.text.secondary}>
              {availableCount} available · {featuredCount} featured
            </AppText>
          </View>
          <Link href="/(admin)/menu-items/new" asChild>
            <AppButton label="New" />
          </Link>
        </View>
      </AppCard>

      <AppCard style={styles.filterCard}>
        <AppInput
          label="Search menu"
          placeholder="Chicken, drinks, plates..."
          value={search}
          onChangeText={setSearch}
          autoCapitalize="none"
        />
        <ChipRow
          items={[
            { label: 'All categories', value: '' },
            ...categories.map((category) => ({ label: category.name, value: String(category.id), disabled: !category.is_active })),
          ]}
          value={categoryId}
          onChange={setCategoryId}
        />
        <ChipRow items={availabilityFilters} value={availability} onChange={setAvailability} />
      </AppCard>

      {items.length === 0 ? (
        <EmptyState title="No menu items found" message="Try a different search or create a new item for the live menu." />
      ) : (
        <View style={styles.itemList}>
          {items.map((item) => (
            <MenuItemRow key={item.id} item={item} categories={categories} />
          ))}
        </View>
      )}
    </AppScreen>
  );
}

function ChipRow<TValue extends string>({
  items,
  value,
  onChange,
}: {
  items: { label: string; value: TValue; disabled?: boolean }[];
  value: TValue;
  onChange: (value: TValue) => void;
}) {
  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
      {items.map((item) => {
        const active = item.value === value;

        return (
          <Pressable
            key={item.value || item.label}
            accessibilityRole="button"
            accessibilityState={{ selected: active, disabled: Boolean(item.disabled) }}
            disabled={item.disabled}
            onPress={() => onChange(item.value)}
            style={({ pressed }) => [
              styles.chip,
              active && styles.chipActive,
              item.disabled && styles.chipDisabled,
              pressed && styles.pressed,
            ]}
          >
            <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary} numberOfLines={1}>
              {item.label}
            </AppText>
          </Pressable>
        );
      })}
    </ScrollView>
  );
}

function MenuItemRow({ item, categories }: { item: MenuItem; categories: Category[] }) {
  const categoryName = item.category?.name ?? categories.find((category) => category.id === item.category_id)?.name ?? 'No category';

  return (
    <Pressable
      accessibilityRole="button"
      onPress={() => router.push({ pathname: '/(admin)/menu-items/[id]', params: { id: String(item.id) } })}
      style={({ pressed }) => pressed && styles.pressed}
    >
      <AppCard style={[styles.itemCard, !item.is_available && styles.itemCardMuted]}>
        <View style={styles.imageShell}>
          {item.image_url ? (
            <Image source={{ uri: item.image_url }} style={styles.image} contentFit="cover" />
          ) : (
            <AppText variant="title" color={colors.brand.primary}>
              {item.name.slice(0, 1).toUpperCase()}
            </AppText>
          )}
        </View>
        <View style={styles.itemBody}>
          <View style={styles.badgeRow}>
            <AppBadge label={categoryName} tone="neutral" />
            <AppBadge label={item.is_available ? 'Available' : 'Hidden'} tone={item.is_available ? 'green' : 'danger'} />
            {item.is_featured ? <AppBadge label="Featured" tone="gold" /> : null}
          </View>
          <AppText variant="title" numberOfLines={1}>
            {item.name}
          </AppText>
          <AppText color={colors.text.secondary} numberOfLines={2}>
            {item.description ?? 'No description yet.'}
          </AppText>
          <View style={styles.itemFooter}>
            <PriceText amount={item.price} />
            <View style={styles.editPill}>
              <AppText variant="caption" color={colors.brand.primary}>
                Edit
              </AppText>
              <AppIcon name={appIcons.chevronRight} size={15} color={colors.brand.primary} />
            </View>
          </View>
        </View>
      </AppCard>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  heroCard: {
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
    padding: spacing.md,
  },
  heroTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  heroIcon: {
    width: 42,
    height: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.brand.primary,
  },
  filterCard: {
    gap: spacing.md,
    padding: spacing.md,
  },
  chipRow: {
    gap: spacing.sm,
    paddingRight: spacing.lg,
  },
  chip: {
    minHeight: 38,
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  chipActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  chipDisabled: {
    opacity: 0.5,
  },
  itemList: {
    gap: spacing.sm,
  },
  itemCard: {
    minHeight: 136,
    flexDirection: 'row',
    gap: spacing.md,
    padding: spacing.md,
    borderRadius: radius.lg,
  },
  itemCardMuted: {
    opacity: 0.72,
  },
  imageShell: {
    width: 82,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.lg,
    backgroundColor: colors.gold.pale,
  },
  image: {
    height: '100%',
    width: '100%',
  },
  itemBody: {
    flex: 1,
    minWidth: 0,
    gap: spacing.xs,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.xs,
  },
  itemFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
  },
  editPill: {
    minHeight: 34,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
    paddingHorizontal: spacing.md,
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
