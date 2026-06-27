import { waitFor } from '@testing-library/react-native';

import CustomerOrdersScreen from '@/app/(customer)/(tabs)/orders';
import { getCustomerOrders } from '@/src/api/orders.api';
import { renderWithQueryClient } from '../test-utils';

jest.mock('@/src/api/orders.api', () => ({
  getCustomerOrders: jest.fn(),
}));

describe('orders screen', () => {
  it('renders the empty order state', async () => {
    jest.mocked(getCustomerOrders).mockResolvedValue([]);

    const screen = renderWithQueryClient(<CustomerOrdersScreen />);

    await waitFor(() => {
      expect(screen.getByText('No orders yet')).toBeTruthy();
    });
  });
});
