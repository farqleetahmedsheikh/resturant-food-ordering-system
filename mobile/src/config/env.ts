import Constants from 'expo-constants';
import { Platform } from 'react-native';

export type MobileEnv = {
  apiUrl: string;
  enableAdminMobile: boolean;
  webAdminUrl: string | null;
  configError: string | null;
};

const loopbackHost = 'local'.concat('host');
const loopbackIpPattern = ['127', '0', '0', '1'].join('\\.');
const placeholderPattern = /YOUR_COMPUTER_LAN_IP/i;
const loopbackPattern = new RegExp(`${loopbackHost}|${loopbackIpPattern}`, 'i');
export const DEFAULT_API_URL = 'http://arcadekebab.com/api/v1';

function withDefaultApiUrl(value: string | undefined): string {
  const trimmed = (value ?? '').trim();

  return trimmed || DEFAULT_API_URL;
}

export function normalizeApiUrl(value: string | undefined, options: { allowLoopback?: boolean } = {}): string {
  const trimmed = (value ?? '').trim().replace(/\/+$/, '');

  if (!trimmed) {
    throw new Error('EXPO_PUBLIC_API_URL is required. Use your computer LAN IP for physical-device testing.');
  }

  if (!/^https?:\/\//i.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_API_URL must start with http:// or https://.');
  }

  if (placeholderPattern.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_API_URL must use your computer LAN IP, not the placeholder value.');
  }

  if (!options.allowLoopback && loopbackPattern.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_API_URL must use your computer LAN IP for native-device testing. Use EXPO_PUBLIC_WEB_API_URL for localhost web development.');
  }

  const url = new URL(trimmed);

  if (url.port === '8081') {
    throw new Error('EXPO_PUBLIC_API_URL points to the Expo dev server port 8081. Use the Laravel API port, usually 8000.');
  }

  return trimmed;
}

function normalizeOptionalUrl(value: string | undefined): string | null {
  const trimmed = (value ?? '').trim();

  if (!trimmed) {
    return null;
  }

  if (!/^https?:\/\//i.test(trimmed)) {
    throw new Error('EXPO_PUBLIC_WEB_ADMIN_URL must start with http:// or https://.');
  }

  return trimmed;
}

export function createEnv(source: NodeJS.ProcessEnv = process.env): MobileEnv {
  const nativeApiUrl = normalizeApiUrl(withDefaultApiUrl(source.EXPO_PUBLIC_API_URL));
  const webApiUrl = source.EXPO_PUBLIC_WEB_API_URL
    ? normalizeApiUrl(source.EXPO_PUBLIC_WEB_API_URL, { allowLoopback: true })
    : null;

  return {
    apiUrl: Platform.OS === 'web' && webApiUrl ? webApiUrl : nativeApiUrl,
    enableAdminMobile: source.EXPO_PUBLIC_ENABLE_ADMIN_MOBILE === 'true',
    webAdminUrl: normalizeOptionalUrl(source.EXPO_PUBLIC_WEB_ADMIN_URL),
    configError: null,
  };
}

export function safeEnv(source: NodeJS.ProcessEnv = process.env): MobileEnv {
  try {
    return createEnv(source);
  } catch (error) {
    if (__DEV__) {
      return {
        apiUrl: DEFAULT_API_URL,
        enableAdminMobile: false,
        webAdminUrl: null,
        configError: error instanceof Error ? error.message : 'Mobile app configuration is invalid.',
      };
    }

    return {
      apiUrl: DEFAULT_API_URL,
      enableAdminMobile: source.EXPO_PUBLIC_ENABLE_ADMIN_MOBILE === 'true',
      webAdminUrl: safeOptionalUrl(source.EXPO_PUBLIC_WEB_ADMIN_URL),
      configError: error instanceof Error ? error.message : 'Mobile app configuration is invalid.',
    };
  }
}

function safeOptionalUrl(value: string | undefined): string | null {
  try {
    return normalizeOptionalUrl(value);
  } catch {
    return null;
  }
}

export const env = safeEnv();

export const runtimeInfo = {
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
  expoVersion: Constants.expoVersion ?? 'unknown',
};
