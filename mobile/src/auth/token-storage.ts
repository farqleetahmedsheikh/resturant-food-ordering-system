import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const TOKEN_KEY = 'arcade_kebab_house.auth_token';

let memoryToken: string | null = null;

function canUseSecureStore(): boolean {
  return (
    Platform.OS !== 'web' &&
    typeof SecureStore.getItemAsync === 'function' &&
    typeof SecureStore.setItemAsync === 'function' &&
    typeof SecureStore.deleteItemAsync === 'function'
  );
}

export async function getStoredToken(): Promise<string | null> {
  if (memoryToken) {
    return memoryToken;
  }

  memoryToken = canUseSecureStore()
    ? await SecureStore.getItemAsync(TOKEN_KEY)
    : await AsyncStorage.getItem(TOKEN_KEY);

  return memoryToken;
}

export async function storeToken(token: string): Promise<void> {
  memoryToken = token;

  if (canUseSecureStore()) {
    await SecureStore.setItemAsync(TOKEN_KEY, token, {
      keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
    });
    return;
  }

  await AsyncStorage.setItem(TOKEN_KEY, token);
}

export async function clearStoredToken(): Promise<void> {
  memoryToken = null;

  if (canUseSecureStore()) {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    return;
  }

  await AsyncStorage.removeItem(TOKEN_KEY);
}
