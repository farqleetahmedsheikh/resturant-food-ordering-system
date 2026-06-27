import { waitFor } from '@testing-library/react-native';

import CustomerCartScreen from '@/app/(customer)/(tabs)/cart';
import { getRestaurant } from '@/src/api/restaurant.api';
import { useCartStore } from '@/src/store/cart.store';
import { renderWithQueryClient } from '../test-utils';

jest.mock('@/src/api/restaurant.api', () => ({
  getRestaurant: jest.fn(),
}));

describe('cart screen', () => {
  beforeEach(() => {
    useCartStore.getState().clearCart();
    jest.mocked(getRestaurant).mockResolvedValue({
      id: 1,
      name: 'Arcade Kebab House',
      email: null,
      phone: null,
      address: null,
      formatted_address: null,
      short_description: null,
      opening_time: null,
      closing_time: null,
      timezone: 'Australia/Sydney',
      latitude: null,
      longitude: null,
      delivery_fee: 4.99,
      minimum_order_amount: 20,
      logo_url: null,
      cover_image_url: null,
      initials: 'AK',
      is_open: true,
      is_open_for_orders: true,
      availability_label: 'Open for orders',
      availability_reason: null,
      currency: 'AUD',
    });
  });

  it('renders the empty cart state', async () => {
    const screen = renderWithQueryClient(<CustomerCartScreen />);

    await waitFor(() => {
      expect(screen.getByText('Your cart is empty')).toBeTruthy();
    });
  });
});
