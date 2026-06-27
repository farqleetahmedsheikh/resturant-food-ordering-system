import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'arcade_kebab_house.auth_token';

let memoryToken: string | null = null;

export async function getStoredToken(): Promise<string | null> {
  if (memoryToken) {
    return memoryToken;
  }

  memoryToken = await SecureStore.getItemAsync(TOKEN_KEY);

  return memoryToken;
}

export async function storeToken(token: string): Promise<void> {
  memoryToken = token;
  await SecureStore.setItemAsync(TOKEN_KEY, token, {
    keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
  });
}

export async function clearStoredToken(): Promise<void> {
  memoryToken = null;
  await SecureStore.deleteItemAsync(TOKEN_KEY);
}
