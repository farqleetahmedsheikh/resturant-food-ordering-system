import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { BackendCart, LocalCartItem } from '@/src/types/cart';

export async function getBackendCart(): Promise<BackendCart> {
  const response = await apiClient.get<ApiEnvelope<BackendCart>>(endpoints.customer.cart);

  return response.data.data;
}

export async function addBackendCartItem(menuItemId: number, quantity: number): Promise<BackendCart> {
  const response = await apiClient.post<ApiEnvelope<BackendCart>>(`${endpoints.customer.cart}/items/${menuItemId}`, {
    quantity,
  });

  return response.data.data;
}

export async function clearBackendCart(): Promise<BackendCart> {
  const response = await apiClient.delete<ApiEnvelope<BackendCart>>(endpoints.customer.cart);

  return response.data.data;
}

export async function syncLocalCartToBackend(items: LocalCartItem[]): Promise<BackendCart> {
  let cart = await clearBackendCart();

  for (const item of items) {
    if (item.quantity > 0) {
      cart = await addBackendCartItem(item.menuItemId, item.quantity);
    }
  }

  return cart;
}
