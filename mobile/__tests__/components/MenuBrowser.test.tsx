import { waitFor } from '@testing-library/react-native';

import { getCategories, getMenuItems } from '@/src/api/menu.api';
import { getRestaurant } from '@/src/api/restaurant.api';
import { MenuBrowser } from '@/src/components/menu/MenuBrowser';
import { renderWithQueryClient } from '../test-utils';

jest.mock('@/src/api/menu.api', () => ({
  getCategories: jest.fn(),
  getMenuItems: jest.fn(),
}));

jest.mock('@/src/api/restaurant.api', () => ({
  getRestaurant: jest.fn(),
}));

const restaurant = {
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
};

describe('MenuBrowser', () => {
  beforeEach(() => {
    jest.mocked(getRestaurant).mockResolvedValue(restaurant);
    jest.mocked(getCategories).mockResolvedValue([]);
  });

  it('shows a loading state', () => {
    jest.mocked(getRestaurant).mockReturnValue(new Promise<typeof restaurant>(() => undefined));
    jest.mocked(getCategories).mockReturnValue(new Promise<[]>(() => undefined));
    jest.mocked(getMenuItems).mockReturnValue(new Promise<{ items: [] }>(() => undefined));

    const screen = renderWithQueryClient(<MenuBrowser mode="public" />);

    expect(screen.getByText('Loading menu...')).toBeTruthy();
  });

  it('shows an error state', async () => {
    jest.mocked(getMenuItems).mockRejectedValue(new Error('Network down'));

    const screen = renderWithQueryClient(<MenuBrowser mode="public" />);

    await waitFor(() => {
      expect(screen.getByText('Unable to load menu items.')).toBeTruthy();
    });
  });

  it('shows an empty state', async () => {
    jest.mocked(getMenuItems).mockResolvedValue({ items: [] });

    const screen = renderWithQueryClient(<MenuBrowser mode="public" />);

    await waitFor(() => {
      expect(screen.getByText('No items found')).toBeTruthy();
    });
  });
});
