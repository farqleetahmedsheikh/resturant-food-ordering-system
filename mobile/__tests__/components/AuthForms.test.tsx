import { fireEvent, render, waitFor } from '@testing-library/react-native';

import LoginScreen from '@/app/(auth)/login';
import RegisterScreen from '@/app/(auth)/register';

describe('auth forms', () => {
  it('shows login validation errors', async () => {
    const screen = render(<LoginScreen />);

    fireEvent.press(screen.getAllByText('Login')[1]);

    await waitFor(() => {
      expect(screen.getByText('Enter a valid email address.')).toBeTruthy();
      expect(screen.getByText('Password must be at least 8 characters.')).toBeTruthy();
    });
  });

  it('shows register validation errors', async () => {
    const screen = render(<RegisterScreen />);

    fireEvent.press(screen.getByText('Create Account'));

    await waitFor(() => {
      expect(screen.getByText('Enter your full name.')).toBeTruthy();
      expect(screen.getByText('Please acknowledge the account terms placeholder.')).toBeTruthy();
    });
  });
});
