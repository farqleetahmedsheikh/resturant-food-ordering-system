import { zodResolver } from '@hookform/resolvers/zod';
import { Image } from 'expo-image';
import { useEffect } from 'react';
import { Controller, useForm, useWatch } from 'react-hook-form';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { z } from 'zod';

import { AppBadge } from '@/src/components/common/AppBadge';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppText } from '@/src/components/common/AppText';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { colors, radius, spacing } from '@/src/theme';
import type { AdminMenuItemPayload } from '@/src/types/admin';
import type { Category, MenuItem } from '@/src/types/menu';

type MenuItemEditorProps = {
  categories: Category[];
  item?: MenuItem | null;
  submitLabel: string;
  submitting?: boolean;
  serverError?: string | null;
  onSubmit: (payload: AdminMenuItemPayload) => void;
  onCancel?: () => void;
};

const requiredMoney = z.string().trim().min(1, 'Enter an AUD price.').refine(isNonNegativeNumber, {
  message: 'Enter a valid AUD amount.',
});

const optionalMoney = z.string().trim().refine((value) => value === '' || isNonNegativeNumber(value), {
  message: 'Enter a valid AUD amount.',
});

const optionalInteger = z.string().trim().refine((value) => value === '' || isWholeNumber(value), {
  message: 'Enter a whole number.',
});

const menuItemSchema = z.object({
  category_id: z.string(),
  name: z.string().trim().min(2, 'Enter the item name.').max(255, 'Name is too long.'),
  description: z.string().trim().max(1500, 'Description is too long.'),
  price: requiredMoney,
  compare_at_price: optionalMoney,
  preparation_time: optionalInteger,
  calories: optionalInteger,
  sort_order: optionalInteger,
  is_featured: z.boolean(),
  is_available: z.boolean(),
});

type MenuItemForm = z.infer<typeof menuItemSchema>;

export function MenuItemEditor({
  categories,
  item,
  submitLabel,
  submitting = false,
  serverError,
  onSubmit,
  onCancel,
}: MenuItemEditorProps) {
  const form = useForm<MenuItemForm>({
    resolver: zodResolver(menuItemSchema),
    defaultValues: defaultsFor(item),
  });
  const previewName = useWatch({ control: form.control, name: 'name' });
  const previewDescription = useWatch({ control: form.control, name: 'description' });
  const previewAvailable = useWatch({ control: form.control, name: 'is_available' });
  const previewFeatured = useWatch({ control: form.control, name: 'is_featured' });

  useEffect(() => {
    form.reset(defaultsFor(item));
  }, [form, item]);

  function submit(values: MenuItemForm) {
    onSubmit({
      category_id: toNullableInteger(values.category_id),
      name: values.name.trim(),
      description: clean(values.description),
      price: Number(values.price),
      compare_at_price: toNullableNumber(values.compare_at_price),
      preparation_time: toNullableInteger(values.preparation_time),
      calories: toNullableInteger(values.calories),
      sort_order: toNullableInteger(values.sort_order) ?? 0,
      is_featured: values.is_featured,
      is_available: values.is_available,
    });
  }

  return (
    <View style={styles.wrapper}>
      {serverError ? <FeedbackMessage tone="error" message={serverError} /> : null}

      <AppCard style={styles.previewCard}>
        <View style={styles.previewTop}>
          <View style={styles.imageShell}>
            {item?.image_url ? (
              <Image source={{ uri: item.image_url }} style={styles.image} contentFit="cover" />
            ) : (
              <AppText variant="h2" color={colors.brand.primary}>
                {(previewName || 'A').slice(0, 1).toUpperCase()}
              </AppText>
            )}
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>
              MENU ITEM
            </AppText>
            <AppText variant="title" numberOfLines={1}>
              {previewName || 'New menu item'}
            </AppText>
            <AppText color={colors.text.secondary} numberOfLines={2}>
              {previewDescription || 'Ready to configure for Arcade Kebab House.'}
            </AppText>
          </View>
        </View>
        <View style={styles.badgeRow}>
          <AppBadge label={previewAvailable ? 'Available' : 'Unavailable'} tone={previewAvailable ? 'green' : 'danger'} />
          <AppBadge label={previewFeatured ? 'Featured' : 'Standard'} tone={previewFeatured ? 'gold' : 'neutral'} />
          {item?.sizes?.length ? <AppBadge label={`${item.sizes.length} sizes`} tone="info" /> : null}
          {item?.addons?.length ? <AppBadge label={`${item.addons.length} add-ons`} tone="info" /> : null}
        </View>
      </AppCard>

      <AppCard style={styles.formCard}>
        <AppText variant="title">Item details</AppText>
        <Controller
          control={form.control}
          name="name"
          render={({ field, fieldState }) => (
            <AppInput label="Name" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
          )}
        />
        <Controller
          control={form.control}
          name="description"
          render={({ field, fieldState }) => (
            <AppInput
              label="Description"
              value={field.value}
              onChangeText={field.onChange}
              multiline
              numberOfLines={3}
              textAlignVertical="top"
              style={styles.multilineInput}
              error={fieldState.error?.message}
            />
          )}
        />
        <Controller
          control={form.control}
          name="category_id"
          render={({ field, fieldState }) => (
            <CategoryChooser
              categories={categories}
              value={field.value}
              onChange={field.onChange}
              error={fieldState.error?.message}
            />
          )}
        />
      </AppCard>

      <AppCard style={styles.formCard}>
        <AppText variant="title">Pricing and timing</AppText>
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="price"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Price AUD"
                  value={field.value}
                  onChangeText={field.onChange}
                  keyboardType="decimal-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="compare_at_price"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Compare price"
                  value={field.value}
                  onChangeText={field.onChange}
                  keyboardType="decimal-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
          </View>
        </View>
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="preparation_time"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Prep minutes"
                  value={field.value}
                  onChangeText={field.onChange}
                  keyboardType="number-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="calories"
              render={({ field, fieldState }) => (
                <AppInput
                  label="Calories"
                  value={field.value}
                  onChangeText={field.onChange}
                  keyboardType="number-pad"
                  error={fieldState.error?.message}
                />
              )}
            />
          </View>
        </View>
        <Controller
          control={form.control}
          name="sort_order"
          render={({ field, fieldState }) => (
            <AppInput
              label="Sort order"
              value={field.value}
              onChangeText={field.onChange}
              keyboardType="number-pad"
              error={fieldState.error?.message}
            />
          )}
        />
      </AppCard>

      <AppCard style={styles.formCard}>
        <AppText variant="title">Visibility</AppText>
        <Controller
          control={form.control}
          name="is_available"
          render={({ field }) => (
            <AppCheckbox checked={field.value} label="Available for ordering" onChange={field.onChange} />
          )}
        />
        <Controller
          control={form.control}
          name="is_featured"
          render={({ field }) => (
            <AppCheckbox checked={field.value} label="Feature in popular picks" onChange={field.onChange} />
          )}
        />
        <View style={styles.infoPanel}>
          <AppText variant="caption" color={colors.text.secondary}>
            Existing images, sizes, and add-ons are preserved when you update from mobile. Use the web admin for image uploads.
          </AppText>
        </View>
      </AppCard>

      <View style={styles.actions}>
        {onCancel ? <AppButton label="Cancel" variant="outline" onPress={onCancel} style={styles.actionButton} /> : null}
        <AppButton
          label={submitLabel}
          loading={submitting}
          onPress={form.handleSubmit(submit)}
          style={styles.actionButton}
        />
      </View>
    </View>
  );
}

function CategoryChooser({
  categories,
  value,
  onChange,
  error,
}: {
  categories: Category[];
  value: string;
  onChange: (value: string) => void;
  error?: string;
}) {
  return (
    <View style={styles.categoryBlock}>
      <AppText variant="caption" color={colors.text.secondary}>
        Category
      </AppText>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryRow}>
        <ChoiceChip label="No category" active={!value} onPress={() => onChange('')} />
        {categories.map((category) => (
          <ChoiceChip
            key={category.id}
            label={category.name}
            active={value === String(category.id)}
            disabled={!category.is_active}
            onPress={() => onChange(String(category.id))}
          />
        ))}
      </ScrollView>
      {error ? (
        <AppText variant="caption" color={colors.semantic.danger}>
          {error}
        </AppText>
      ) : null}
    </View>
  );
}

function ChoiceChip({
  label,
  active,
  disabled,
  onPress,
}: {
  label: string;
  active: boolean;
  disabled?: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ selected: active, disabled: Boolean(disabled) }}
      disabled={disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.choiceChip,
        active && styles.choiceChipActive,
        disabled && styles.choiceChipDisabled,
        pressed && styles.pressed,
      ]}
    >
      <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary} numberOfLines={1}>
        {label}
      </AppText>
    </Pressable>
  );
}

function defaultsFor(item?: MenuItem | null): MenuItemForm {
  return {
    category_id: item?.category_id ? String(item.category_id) : '',
    name: item?.name ?? '',
    description: item?.description ?? '',
    price: inputNumber(item?.price),
    compare_at_price: inputNumber(item?.compare_at_price),
    preparation_time: inputNumber(item?.preparation_time),
    calories: inputNumber(item?.calories),
    sort_order: inputNumber(item?.sort_order),
    is_featured: item?.is_featured ?? false,
    is_available: item?.is_available ?? true,
  };
}

function inputNumber(value: number | null | undefined): string {
  return value === null || value === undefined ? '' : String(value);
}

function clean(value: string): string | null {
  const trimmed = value.trim();

  return trimmed === '' ? null : trimmed;
}

function toNullableNumber(value: string): number | null {
  const trimmed = value.trim();

  return trimmed === '' ? null : Number(trimmed);
}

function toNullableInteger(value: string): number | null {
  const trimmed = value.trim();

  return trimmed === '' ? null : Number.parseInt(trimmed, 10);
}

function isNonNegativeNumber(value: string): boolean {
  const number = Number(value);

  return Number.isFinite(number) && number >= 0;
}

function isWholeNumber(value: string): boolean {
  const number = Number(value);

  return Number.isInteger(number) && number >= 0;
}

const styles = StyleSheet.create({
  wrapper: {
    gap: spacing.md,
  },
  previewCard: {
    gap: spacing.md,
    borderColor: colors.brand.border,
    backgroundColor: colors.brand.soft,
    padding: spacing.md,
  },
  previewTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  imageShell: {
    width: 76,
    height: 76,
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
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  formCard: {
    gap: spacing.md,
    padding: spacing.md,
  },
  categoryBlock: {
    gap: spacing.sm,
  },
  categoryRow: {
    gap: spacing.sm,
    paddingRight: spacing.lg,
  },
  choiceChip: {
    minHeight: 40,
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  choiceChipActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  choiceChipDisabled: {
    opacity: 0.45,
  },
  twoColumn: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  splitField: {
    flex: 1,
    minWidth: 0,
  },
  multilineInput: {
    minHeight: 92,
    paddingTop: spacing.md,
  },
  infoPanel: {
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    padding: spacing.md,
  },
  actions: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  actionButton: {
    flex: 1,
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
