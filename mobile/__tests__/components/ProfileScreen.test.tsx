import { fireEvent } from '@testing-library/react-native';

import CustomerProfileScreen from '@/app/(customer)/(tabs)/profile';
import { useAuthStore } from '@/src/auth/auth.store';
import { checkHealth } from '@/src/api/health.api';
import { renderWithQueryClient } from '../test-utils';

jest.mock('@/src/api/health.api', () => ({
  checkHealth: jest.fn(),
}));

describe('profile screen', () => {
  beforeEach(() => {
    jest.mocked(checkHealth).mockResolvedValue({
      ok: true,
      apiUrl: 'http://192.168.1.20:8000/api/v1',
      responseTimeMs: 12,
      status: 'ok',
      application: 'Arcade Kebab House',
      appVersion: '1.0.0',
      expoVersion: '56.0.0',
    });

    useAuthStore.setState({
      status: 'authenticated',
      error: null,
      session: {
        tokenType: 'Bearer',
        accessToken: 'test-token',
        expiresAt: null,
        abilities: ['customer'],
        user: {
          id: 1,
          name: 'Customer User',
          email: 'customer@example.com',
          phone: '+61 400 000 000',
          role: 'customer',
          is_active: true,
        },
      },
    });
  });

  it('opens a custom logout confirmation', () => {
    const screen = renderWithQueryClient(<CustomerProfileScreen />);

    fireEvent.press(screen.getByText('Logout'));

    expect(screen.getByText('Logout?')).toBeTruthy();
    expect(screen.getByText('Your secure session token will be removed from this device.')).toBeTruthy();
  });
});
