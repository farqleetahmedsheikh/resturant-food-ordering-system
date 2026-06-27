import * as SecureStore from 'expo-secure-store';

import { useAuthStore } from '@/src/auth/auth.store';

describe('auth store', () => {
  it('logout clears the SecureStore token and auth state', async () => {
    useAuthStore.setState({
      status: 'authenticated',
      session: null,
      error: null,
    });

    await useAuthStore.getState().logout();

    expect(SecureStore.deleteItemAsync).toHaveBeenCalled();
    expect(useAuthStore.getState().status).toBe('guest');
    expect(useAuthStore.getState().session).toBeNull();
  });
});
