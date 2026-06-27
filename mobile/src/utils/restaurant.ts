import type { Restaurant } from '@/src/types/restaurant';

export type RestaurantAvailability = {
  isOpenForOrders: boolean;
  label: string;
  reason: string | null;
  timezone: string | null;
  nextOpeningTime: string | null;
  currentClosingTime: string | null;
};

export function getRestaurantAvailability(restaurant: Restaurant | null | undefined): RestaurantAvailability {
  return {
    isOpenForOrders: Boolean(restaurant?.is_open_for_orders ?? restaurant?.is_open),
    label: restaurant?.availability_label ?? (restaurant?.is_open ? 'Open for orders' : 'Ordering paused'),
    reason: restaurant?.availability_reason ?? null,
    timezone: restaurant?.timezone ?? null,
    nextOpeningTime: restaurant?.next_opening_time ?? null,
    currentClosingTime: restaurant?.current_closing_time ?? null,
  };
}

export function restaurantInitials(name: string | null | undefined): string {
  const words = (name ?? 'Arcade Kebab House')
    .trim()
    .split(/\s+/)
    .filter((word) => !['the', 'and', '&'].includes(word.toLowerCase()))
    .slice(0, 2);

  return (words.map((word) => word.slice(0, 1)).join('') || 'AK').toUpperCase();
}
