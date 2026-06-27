import { create } from 'axios';

import { normalizeApiError } from './api-error';
import { env } from '@/src/config/env';
import { getStoredToken } from '@/src/auth/token-storage';

export const apiClient = create({
  baseURL: env.apiUrl,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

let interceptorsRegistered = false;
let unauthorizedHandler: (() => void | Promise<void>) | null = null;

export function registerUnauthorizedHandler(handler: () => void | Promise<void>): void {
  unauthorizedHandler = handler;
}

export function configureApiClient(): void {
  if (interceptorsRegistered) {
    return;
  }

  apiClient.interceptors.request.use(async (config) => {
    const token = await getStoredToken();

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  });

  apiClient.interceptors.response.use(
    (response) => response,
    async (error) => {
      const normalized = normalizeApiError(error);

      if (normalized.status === 401 && unauthorizedHandler) {
        await unauthorizedHandler();
      }

      return Promise.reject(error);
    },
  );

  interceptorsRegistered = true;
}
