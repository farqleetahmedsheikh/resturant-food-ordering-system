import { create } from 'zustand';

import { login as loginApi, logout as logoutApi, me, register as registerApi } from '@/src/api/auth.api';
import { registerUnauthorizedHandler } from '@/src/api/client';
import { normalizeApiError } from '@/src/api/api-error';
import { clearStoredToken, getStoredToken, storeToken } from './token-storage';
import type { AuthSession, AuthUser } from '@/src/types/auth';

type LoginInput = {
  email: string;
  password: string;
};

type RegisterInput = LoginInput & {
  name: string;
  phone?: string;
  password_confirmation: string;
};

type AuthStatus = 'loading' | 'guest' | 'authenticated' | 'error';

type AuthState = {
  status: AuthStatus;
  session: AuthSession | null;
  error: string | null;
  restore: () => Promise<void>;
  login: (input: LoginInput) => Promise<AuthSession>;
  register: (input: RegisterInput) => Promise<AuthSession>;
  logout: () => Promise<void>;
  clearSession: () => Promise<void>;
  updateUser: (user: AuthUser) => void;
};

async function persistSession(session: AuthSession): Promise<void> {
  if (!session.user.is_active) {
    throw new Error('Your account is inactive. Please contact support.');
  }

  await storeToken(session.accessToken);
}

export const useAuthStore = create<AuthState>((set, get) => ({
  status: 'loading',
  session: null,
  error: null,

  restore: async () => {
    let token: string | null = null;

    try {
      token = await getStoredToken();
    } catch {
      await clearStoredToken().catch(() => undefined);
      set({
        status: 'guest',
        session: null,
        error: 'Secure session storage was reset. Please login again.',
      });
      return;
    }

    if (!token) {
      set({ status: 'guest', session: null, error: null });
      return;
    }

    try {
      const user = await me();

      if (!user.is_active) {
        await clearStoredToken().catch(() => undefined);
        set({ status: 'guest', session: null, error: 'Your account is inactive.' });
        return;
      }

      set({
        status: 'authenticated',
        error: null,
        session: {
          tokenType: 'Bearer',
          accessToken: token,
          expiresAt: null,
          abilities: [],
          user,
        },
      });
    } catch (error) {
      await clearStoredToken().catch(() => undefined);
      set({
        status: 'guest',
        session: null,
        error: normalizeApiError(error).message,
      });
    }
  },

  login: async (input) => {
    try {
      const session = await loginApi({
        ...input,
        device_name: 'Arcade Kebab House Mobile',
      });
      await persistSession(session);
      set({ status: 'authenticated', session, error: null });
      return session;
    } catch (error) {
      const message = normalizeApiError(error).message;
      set({ error: message });
      throw error;
    }
  },

  register: async (input) => {
    try {
      const session = await registerApi({
        ...input,
        device_name: 'Arcade Kebab House Mobile',
      });
      await persistSession(session);
      set({ status: 'authenticated', session, error: null });
      return session;
    } catch (error) {
      const message = normalizeApiError(error).message;
      set({ error: message });
      throw error;
    }
  },

  logout: async () => {
    try {
      if (get().session) {
        await logoutApi();
      }
    } finally {
      await get().clearSession();
    }
  },

  clearSession: async () => {
    await clearStoredToken();
    set({ status: 'guest', session: null, error: null });
  },

  updateUser: (user) => {
    const session = get().session;

    if (!session) {
      return;
    }

    set({
      session: {
        ...session,
        user,
      },
    });
  },
}));

registerUnauthorizedHandler(() => useAuthStore.getState().clearSession());
