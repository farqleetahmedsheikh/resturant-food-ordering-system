import { fireEvent, waitFor } from '@testing-library/react-native';

import LoginScreen from '@/app/(auth)/login';
import RegisterScreen from '@/app/(auth)/register';
import { renderWithTheme } from '../test-utils';

describe('auth forms', () => {
  it('shows login validation errors', async () => {
    const screen = renderWithTheme(<LoginScreen />);

    fireEvent.press(screen.getAllByText('Login')[1]);

    await waitFor(() => {
      expect(screen.getByText('Enter a valid email address.')).toBeTruthy();
      expect(screen.getByText('Password must be at least 8 characters.')).toBeTruthy();
    });
  });

  it('shows register validation errors', async () => {
    const screen = renderWithTheme(<RegisterScreen />);

    fireEvent.press(screen.getByText('Create Account'));

    await waitFor(() => {
      expect(screen.getByText('Enter your full name.')).toBeTruthy();
      expect(screen.getByText('Please agree to the Terms and Conditions and acknowledge the Privacy Policy.')).toBeTruthy();
    });
  });
});
