import { getRestaurantAvailability, restaurantInitials } from '@/src/utils/restaurant';
import type { Restaurant } from '@/src/types/restaurant';

const restaurant: Restaurant = {
  id: 1,
  name: 'Arcade Kebab House',
  email: null,
  phone: null,
  address: null,
  formatted_address: null,
  short_description: null,
  opening_time: '12:00',
  closing_time: '23:00',
  timezone: 'Australia/Sydney',
  latitude: null,
  longitude: null,
  delivery_fee: 4.99,
  minimum_order_amount: 20,
  logo_url: null,
  cover_image_url: null,
  initials: 'AK',
  is_open: true,
  is_open_for_orders: false,
  availability_label: 'Opens tomorrow at 12:00 PM',
  availability_reason: 'The restaurant is outside its configured operating hours.',
  next_opening_time: '2026-06-28T12:00:00+10:00',
  current_closing_time: null,
  currency: 'AUD',
};

describe('restaurant helpers', () => {
  it('uses backend availability fields instead of device time', () => {
    expect(getRestaurantAvailability(restaurant)).toMatchObject({
      isOpenForOrders: false,
      label: 'Opens tomorrow at 12:00 PM',
      timezone: 'Australia/Sydney',
    });
  });

  it('builds dynamic restaurant initials', () => {
    expect(restaurantInitials('Arcade Kebab House')).toBe('AK');
  });
});
