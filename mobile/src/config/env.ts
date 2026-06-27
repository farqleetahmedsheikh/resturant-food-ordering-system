import Constants from 'expo-constants';

export type MobileEnv = {
  apiUrl: string;
  enableAdminMobile: boolean;
};

const placeholderPattern = /YOUR_COMPUTER_LAN_IP|localhost|127\.0\.0\.1/i;

export function normalizeApiUrl(value: string | undefined): string {
  const trimmed = (value ?? '').trim().replace(/\/+$/, '');

  if (!trimmed) {
    throw new Error('EXPO_PUBLIC_API_URL is required. Use your computer LAN IP for physical-device testing.');
  }

  if (!/^https?:\/\//i.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_API_URL must start with http:// or https://.');
  }

  if (placeholderPattern.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_API_URL must use your computer LAN IP, not localhost or the placeholder value.');
  }

  return trimmed;
}

export function createEnv(source: NodeJS.ProcessEnv = process.env): MobileEnv {
  return {
    apiUrl: normalizeApiUrl(source.EXPO_PUBLIC_API_URL),
    enableAdminMobile: source.EXPO_PUBLIC_ENABLE_ADMIN_MOBILE === 'true',
  };
}

export function safeEnv(): MobileEnv {
  try {
    return createEnv();
  } catch (error) {
    if (__DEV__) {
      return {
        apiUrl: 'http://YOUR_COMPUTER_LAN_IP:8000/api/v1',
        enableAdminMobile: false,
      };
    }

    throw error;
  }
}

export const env = safeEnv();

export const runtimeInfo = {
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
  expoVersion: Constants.expoVersion ?? 'unknown',
};
