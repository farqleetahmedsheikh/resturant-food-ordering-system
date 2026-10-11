import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { BackendCart, BackendCartItemPayload, LocalCartItem } from '@/src/types/cart';
import type { MenuItem } from '@/src/types/menu';

export async function getBackendCart(): Promise<BackendCart> {
  const response = await apiClient.get<ApiEnvelope<BackendCart>>(endpoints.customer.cart);

  return response.data.data;
}

export async function addBackendCartItem(menuItemId: number, payload: BackendCartItemPayload): Promise<BackendCart> {
  const response = await apiClient.post<ApiEnvelope<BackendCart>>(`${endpoints.customer.cart}/items/${menuItemId}`, payload);

  return response.data.data;
}

export async function clearBackendCart(): Promise<BackendCart> {
  const response = await apiClient.delete<ApiEnvelope<BackendCart>>(endpoints.customer.cart);

  return response.data.data;
}

export async function getCartRecommendations(placement = 'cart', limit = 6): Promise<MenuItem[]> {
  const response = await apiClient.get<ApiEnvelope<MenuItem[]>>(endpoints.customer.cartRecommendations, {
    params: { placement, limit },
  });

  return response.data.data;
}

export async function syncLocalCartToBackend(items: LocalCartItem[]): Promise<BackendCart> {
  let cart = await clearBackendCart();

  for (const item of items) {
    if (item.quantity > 0) {
      cart = await addBackendCartItem(item.menuItemId, {
        quantity: item.quantity,
        size_id: item.sizeId,
        ...(item.addonSelections ? { addon_selections: item.addonSelections } : {
          addon_ids: (item.addons ?? []).flatMap((addon) => Array.from({ length: Number(addon.quantity ?? 1) }, () => addon.id)),
        }),
        item_notes: item.notes.trim() || null,
      });
    }
  }

  return cart;
}
