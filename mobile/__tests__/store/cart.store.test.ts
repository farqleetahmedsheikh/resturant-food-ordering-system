import AsyncStorage from '@react-native-async-storage/async-storage';

import { useCartStore } from '@/src/store/cart.store';
import type { MenuItem } from '@/src/types/menu';

const menuItem: MenuItem = {
  id: 7,
  restaurant_id: 1,
  category_id: 1,
  name: 'Chicken Kebab',
  slug: 'chicken-kebab',
  description: 'Grilled chicken kebab.',
  price: 14.5,
  compare_at_price: null,
  image_url: null,
  preparation_time: null,
  calories: null,
  is_featured: true,
  is_available: true,
  sort_order: 1,
};

describe('cart store', () => {
  beforeEach(async () => {
    useCartStore.getState().clearCart();
    await AsyncStorage.clear();
  });

  it('calculates subtotal and total in AUD-ready numbers', () => {
    useCartStore.getState().addItem({ item: menuItem, quantity: 2 });

    expect(useCartStore.getState().getSubtotal()).toBe(29);
    expect(useCartStore.getState().getTotal(4.99)).toBe(33.99);
  });

  it('prevents invalid quantities below one', () => {
    useCartStore.getState().addItem({ item: menuItem, quantity: 1 });
    useCartStore.getState().updateQuantity(menuItem.id, 0);

    expect(useCartStore.getState().items[0].quantity).toBe(1);
  });

  it('persists non-sensitive cart items to AsyncStorage', async () => {
    useCartStore.getState().addItem({ item: menuItem, quantity: 1, notes: 'No onion' });

    const stored = await AsyncStorage.getItem('arcade-kebab-house.cart');

    expect(stored).toContain('Chicken Kebab');
    expect(stored).toContain('No onion');
    expect(stored).not.toContain('access_token');
  });
});
