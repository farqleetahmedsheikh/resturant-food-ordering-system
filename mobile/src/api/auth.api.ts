import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { AuthSession, AuthUser } from '@/src/types/auth';

type BackendTokenPayload = {
  token_type: 'Bearer';
  access_token: string;
  expires_at: string | null;
  abilities: string[];
  user: AuthUser;
};

type LoginPayload = {
  email: string;
  password: string;
  device_name?: string;
};

type RegisterPayload = LoginPayload & {
  name: string;
  phone?: string;
  password_confirmation: string;
};

function toSession(payload: BackendTokenPayload): AuthSession {
  return {
    tokenType: payload.token_type,
    accessToken: payload.access_token,
    expiresAt: payload.expires_at,
    abilities: payload.abilities,
    user: payload.user,
  };
}

export async function login(payload: LoginPayload): Promise<AuthSession> {
  const response = await apiClient.post<ApiEnvelope<BackendTokenPayload>>(endpoints.auth.login, payload);

  return toSession(response.data.data);
}

export async function register(payload: RegisterPayload): Promise<AuthSession> {
  const response = await apiClient.post<ApiEnvelope<BackendTokenPayload>>(endpoints.auth.register, payload);

  return toSession(response.data.data);
}

export async function me(): Promise<AuthUser> {
  const response = await apiClient.get<ApiEnvelope<AuthUser>>(endpoints.auth.me);

  return response.data.data;
}

export async function logout(): Promise<void> {
  await apiClient.post(endpoints.auth.logout);
}
