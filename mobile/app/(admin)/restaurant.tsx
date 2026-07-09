import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect } from 'react';
import type { ReactNode } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { z } from 'zod';

import { getAdminRestaurant, updateAdminRestaurant } from '@/src/api/admin.api';
import { normalizeApiError } from '@/src/api/api-error';
import { AppButton } from '@/src/components/common/AppButton';
import { AppCard } from '@/src/components/common/AppCard';
import { AppIcon, appIcons } from '@/src/components/common/AppIcon';
import { AppText } from '@/src/components/common/AppText';
import { PriceText } from '@/src/components/common/PriceText';
import { StatusBadge } from '@/src/components/common/StatusBadge';
import { FeedbackMessage } from '@/src/components/feedback/FeedbackMessage';
import { ErrorState } from '@/src/components/feedback/ErrorState';
import { LoadingScreen } from '@/src/components/feedback/LoadingScreen';
import { AppCheckbox } from '@/src/components/forms/AppCheckbox';
import { AppInput } from '@/src/components/forms/AppInput';
import { AppHeader } from '@/src/components/layout/AppHeader';
import { AppScreen } from '@/src/components/layout/AppScreen';
import { queryKeys } from '@/src/constants/queryKeys';
import { colors, radius, spacing } from '@/src/theme';
import type { AdminRestaurantPayload } from '@/src/types/admin';
import type { Restaurant } from '@/src/types/restaurant';

const australianTimezones = [
  'Australia/Sydney',
  'Australia/Melbourne',
  'Australia/Brisbane',
  'Australia/Adelaide',
  'Australia/Perth',
  'Australia/Hobart',
  'Australia/Darwin',
  'Australia/Canberra',
  'Australia/Broken_Hill',
  'Australia/Lord_Howe',
];

const moneyInput = z.string().trim().min(1, 'Enter an AUD amount.').refine(isNonNegativeNumber, {
  message: 'Enter a valid AUD amount.',
});

const optionalCoordinate = z.string().trim().refine((value) => value === '' || Number.isFinite(Number(value)), {
  message: 'Enter a valid coordinate.',
});

const optionalTime = z.string().trim().refine((value) => value === '' || /^([01]\d|2[0-3]):[0-5]\d$/.test(value), {
  message: 'Use 24-hour time, for example 10:30.',
});

const restaurantSchema = z.object({
  name: z.string().trim().min(2, 'Enter the restaurant name.').max(255, 'Name is too long.'),
  email: z.union([z.literal(''), z.string().trim().email('Enter a valid email.')]),
  phone: z.string().trim().max(30, 'Phone is too long.'),
  address: z.string().trim().max(1000, 'Address is too long.'),
  formatted_address: z.string().trim().max(1000, 'Formatted address is too long.'),
  short_description: z.string().trim().max(1000, 'Description is too long.'),
  opening_time: optionalTime,
  closing_time: optionalTime,
  timezone: z.string().trim().min(1, 'Choose a timezone.'),
  latitude: optionalCoordinate,
  longitude: optionalCoordinate,
  delivery_fee: moneyInput,
  minimum_order_amount: moneyInput,
  is_open: z.boolean(),
});

type RestaurantForm = z.infer<typeof restaurantSchema>;

export default function AdminRestaurantSettingsScreen() {
  const queryClient = useQueryClient();
  const query = useQuery({
    queryKey: queryKeys.adminRestaurant,
    queryFn: getAdminRestaurant,
  });
  const form = useForm<RestaurantForm>({
    resolver: zodResolver(restaurantSchema),
    defaultValues: defaultsFor(null),
  });
  const mutation = useMutation({
    mutationFn: updateAdminRestaurant,
    onSuccess: async (restaurant) => {
      form.reset(defaultsFor(restaurant));
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.adminRestaurant }),
        queryClient.invalidateQueries({ queryKey: queryKeys.restaurant }),
        queryClient.invalidateQueries({ queryKey: queryKeys.adminDashboard }),
      ]);
    },
  });

  useEffect(() => {
    if (query.data) {
      form.reset(defaultsFor(query.data));
    }
  }, [form, query.data]);

  if (query.isLoading) {
    return <LoadingScreen label="Loading restaurant settings..." />;
  }

  if (query.isError || !query.data) {
    return <ErrorState message="Unable to load restaurant settings." onRetry={() => void query.refetch()} />;
  }

  const restaurant = mutation.data ?? query.data;
  const serverError = mutation.isError ? normalizeApiError(mutation.error).message : null;

  function save(values: RestaurantForm) {
    const payload: AdminRestaurantPayload = {
      name: values.name.trim(),
      email: clean(values.email),
      phone: clean(values.phone),
      address: clean(values.address),
      formatted_address: clean(values.formatted_address) ?? clean(values.address),
      short_description: clean(values.short_description),
      opening_time: clean(values.opening_time),
      closing_time: clean(values.closing_time),
      timezone: values.timezone,
      latitude: toNullableNumber(values.latitude),
      longitude: toNullableNumber(values.longitude),
      delivery_fee: Number(values.delivery_fee),
      minimum_order_amount: Number(values.minimum_order_amount),
      is_open: values.is_open,
    };

    mutation.mutate(payload);
  }

  return (
    <AppScreen
      keyboard
      refreshing={query.isRefetching}
      onRefresh={() => void query.refetch()}
      contentStyle={styles.screen}
    >
      <AppHeader title="Restaurant" subtitle="Edit Arcade Kebab House ordering information." eyebrow="Admin" />

      <AppCard style={styles.heroCard}>
        <View style={styles.heroTop}>
          <View style={styles.heroIcon}>
            <AppIcon name={appIcons.contact} size={20} color={colors.text.inverse} />
          </View>
          <View style={styles.flex}>
            <AppText variant="caption" color={colors.brand.primary}>
              RESTAURANT SETTINGS
            </AppText>
            <AppText variant="h2" numberOfLines={1}>
              {restaurant.name}
            </AppText>
            <AppText color={colors.text.secondary} numberOfLines={2}>
              {restaurant.availability_reason ?? restaurant.short_description ?? 'Single restaurant ordering control.'}
            </AppText>
          </View>
          <StatusBadge status={restaurant.is_open_for_orders ? 'Open' : 'Closed'} />
        </View>
        <View style={styles.statRow}>
          <SettingStat label="Minimum" value={<PriceText amount={restaurant.minimum_order_amount} variant="caption" />} />
          <SettingStat label="Delivery" value={<PriceText amount={restaurant.delivery_fee} variant="caption" />} />
          <SettingStat label="Timezone" value={restaurant.timezone ?? 'Australia/Sydney'} />
        </View>
      </AppCard>

      {serverError ? <FeedbackMessage tone="error" message={serverError} /> : null}
      {mutation.isSuccess ? <FeedbackMessage tone="success" message="Restaurant settings updated." /> : null}

      <AppCard style={styles.card}>
        <AppText variant="title">Business details</AppText>
        <Controller
          control={form.control}
          name="name"
          render={({ field, fieldState }) => (
            <AppInput label="Restaurant name" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
          )}
        />
        <Controller
          control={form.control}
          name="short_description"
          render={({ field, fieldState }) => (
            <AppInput
              label="Short description"
              value={field.value}
              onChangeText={field.onChange}
              multiline
              numberOfLines={2}
              textAlignVertical="top"
              style={styles.multilineInput}
              error={fieldState.error?.message}
            />
          )}
        />
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="phone"
              render={({ field, fieldState }) => (
                <AppInput label="Phone" value={field.value} onChangeText={field.onChange} keyboardType="phone-pad" error={fieldState.error?.message} />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="email"
              render={({ field, fieldState }) => (
                <AppInput label="Email" value={field.value} onChangeText={field.onChange} keyboardType="email-address" error={fieldState.error?.message} />
              )}
            />
          </View>
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <AppText variant="title">Ordering controls</AppText>
        <Controller
          control={form.control}
          name="is_open"
          render={({ field }) => (
            <View style={styles.togglePanel}>
              <AppCheckbox checked={field.value} label="Accept online orders" onChange={field.onChange} />
              <AppText variant="caption" color={colors.text.secondary}>
                Turn this off to pause ordering without hiding the website.
              </AppText>
            </View>
          )}
        />
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="minimum_order_amount"
              render={({ field, fieldState }) => (
                <AppInput label="Minimum AUD" value={field.value} onChangeText={field.onChange} keyboardType="decimal-pad" error={fieldState.error?.message} />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="delivery_fee"
              render={({ field, fieldState }) => (
                <AppInput label="Delivery AUD" value={field.value} onChangeText={field.onChange} keyboardType="decimal-pad" error={fieldState.error?.message} />
              )}
            />
          </View>
        </View>
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="opening_time"
              render={({ field, fieldState }) => (
                <AppInput label="Opens" placeholder="10:00" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="closing_time"
              render={({ field, fieldState }) => (
                <AppInput label="Closes" placeholder="23:00" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
              )}
            />
          </View>
        </View>
        <Controller
          control={form.control}
          name="timezone"
          render={({ field, fieldState }) => (
            <TimezoneChooser value={field.value} onChange={field.onChange} error={fieldState.error?.message} />
          )}
        />
      </AppCard>

      <AppCard style={styles.card}>
        <AppText variant="title">Location</AppText>
        <Controller
          control={form.control}
          name="address"
          render={({ field, fieldState }) => (
            <AppInput
              label="Address"
              value={field.value}
              onChangeText={field.onChange}
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
          name="formatted_address"
          render={({ field, fieldState }) => (
            <AppInput label="Display address" value={field.value} onChangeText={field.onChange} error={fieldState.error?.message} />
          )}
        />
        <View style={styles.twoColumn}>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="latitude"
              render={({ field, fieldState }) => (
                <AppInput label="Latitude" value={field.value} onChangeText={field.onChange} keyboardType="decimal-pad" error={fieldState.error?.message} />
              )}
            />
          </View>
          <View style={styles.splitField}>
            <Controller
              control={form.control}
              name="longitude"
              render={({ field, fieldState }) => (
                <AppInput label="Longitude" value={field.value} onChangeText={field.onChange} keyboardType="decimal-pad" error={fieldState.error?.message} />
              )}
            />
          </View>
        </View>
      </AppCard>

      <AppButton
        label="Save restaurant"
        loading={mutation.isPending}
        fullWidth
        onPress={form.handleSubmit(save)}
      />
    </AppScreen>
  );
}

function TimezoneChooser({
  value,
  onChange,
  error,
}: {
  value: string;
  onChange: (value: string) => void;
  error?: string;
}) {
  return (
    <View style={styles.timezoneBlock}>
      <AppText variant="caption" color={colors.text.secondary}>
        Timezone
      </AppText>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.timezoneRow}>
        {australianTimezones.map((timezone) => {
          const active = value === timezone;

          return (
            <Pressable
              key={timezone}
              accessibilityRole="button"
              accessibilityState={{ selected: active }}
              onPress={() => onChange(timezone)}
              style={({ pressed }) => [styles.timezoneChip, active && styles.timezoneChipActive, pressed && styles.pressed]}
            >
              <AppText variant="caption" color={active ? colors.text.inverse : colors.text.secondary}>
                {timezone.replace('Australia/', '')}
              </AppText>
            </Pressable>
          );
        })}
      </ScrollView>
      {error ? (
        <AppText variant="caption" color={colors.semantic.danger}>
          {error}
        </AppText>
      ) : null}
    </View>
  );
}

function SettingStat({ label, value }: { label: string; value: ReactNode }) {
  return (
    <View style={styles.stat}>
      <AppText variant="caption" color={colors.text.secondary}>
        {label}
      </AppText>
      {typeof value === 'string' ? (
        <AppText variant="caption" color={colors.text.primary} numberOfLines={1}>
          {value}
        </AppText>
      ) : (
        value
      )}
    </View>
  );
}

function defaultsFor(restaurant: Restaurant | null): RestaurantForm {
  return {
    name: restaurant?.name ?? 'Arcade Kebab House',
    email: restaurant?.email ?? '',
    phone: restaurant?.phone ?? '',
    address: restaurant?.address ?? '',
    formatted_address: restaurant?.formatted_address ?? restaurant?.address ?? '',
    short_description: restaurant?.short_description ?? '',
    opening_time: restaurant?.opening_time ?? '',
    closing_time: restaurant?.closing_time ?? '',
    timezone: restaurant?.timezone ?? 'Australia/Sydney',
    latitude: inputNumber(restaurant?.latitude),
    longitude: inputNumber(restaurant?.longitude),
    delivery_fee: inputNumber(restaurant?.delivery_fee),
    minimum_order_amount: inputNumber(restaurant?.minimum_order_amount),
    is_open: restaurant?.is_open ?? true,
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

function isNonNegativeNumber(value: string): boolean {
  const number = Number(value);

  return Number.isFinite(number) && number >= 0;
}

const styles = StyleSheet.create({
  screen: {
    gap: spacing.sm,
  },
  heroCard: {
    gap: spacing.md,
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
  statRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  stat: {
    flex: 1,
    minHeight: 46,
    justifyContent: 'center',
    borderRadius: radius.md,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.sm,
  },
  card: {
    gap: spacing.md,
    padding: spacing.md,
  },
  twoColumn: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  splitField: {
    flex: 1,
    minWidth: 0,
  },
  togglePanel: {
    borderRadius: radius.lg,
    backgroundColor: colors.surface.muted,
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.md,
  },
  multilineInput: {
    minHeight: 86,
    paddingTop: spacing.md,
  },
  addressInput: {
    minHeight: 104,
    paddingTop: spacing.md,
  },
  timezoneBlock: {
    gap: spacing.sm,
  },
  timezoneRow: {
    gap: spacing.sm,
    paddingRight: spacing.lg,
  },
  timezoneChip: {
    minHeight: 40,
    justifyContent: 'center',
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.border.light,
    backgroundColor: colors.surface.card,
    paddingHorizontal: spacing.md,
  },
  timezoneChipActive: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
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
