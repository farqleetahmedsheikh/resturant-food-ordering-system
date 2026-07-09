import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  AdminDashboard,
  AdminMenuItemPayload,
  AdminMenuItemsParams,
  AdminRestaurantPayload,
} from '@/src/types/admin';
import type { ApiEnvelope, PaginationMeta } from '@/src/types/api';
import type { Category, MenuItem } from '@/src/types/menu';
import type { Restaurant } from '@/src/types/restaurant';
import type { AdminRiderDetail, Rider } from '@/src/types/rider';

export async function getAdminDashboard(): Promise<AdminDashboard> {
  const response = await apiClient.get<ApiEnvelope<AdminDashboard>>(endpoints.admin.dashboard);

  return response.data.data;
}

export async function getAdminRiders(): Promise<Rider[]> {
  const response = await apiClient.get<ApiEnvelope<Rider[]>>(endpoints.admin.riders);

  return response.data.data;
}

export async function getAdminRider(id: string | number): Promise<AdminRiderDetail> {
  const response = await apiClient.get<ApiEnvelope<AdminRiderDetail>>(`${endpoints.admin.riders}/${id}`);

  return response.data.data;
}

export async function getAdminRestaurant(): Promise<Restaurant> {
  const response = await apiClient.get<ApiEnvelope<Restaurant>>(endpoints.admin.restaurant);

  return response.data.data;
}

export async function updateAdminRestaurant(payload: AdminRestaurantPayload): Promise<Restaurant> {
  const response = await apiClient.put<ApiEnvelope<Restaurant>>(endpoints.admin.restaurant, payload);

  return response.data.data;
}

export async function getAdminCategories(): Promise<Category[]> {
  const response = await apiClient.get<ApiEnvelope<Category[]>>(endpoints.admin.categories, {
    params: {
      per_page: 75,
    },
  });

  return response.data.data;
}

export async function getAdminMenuItems(
  params: AdminMenuItemsParams = {},
): Promise<{ items: MenuItem[]; meta?: PaginationMeta }> {
  const response = await apiClient.get<ApiEnvelope<MenuItem[]>>(endpoints.admin.menuItems, {
    params,
  });

  return {
    items: response.data.data,
    meta: response.data.meta as PaginationMeta | undefined,
  };
}

export async function getAdminMenuItem(id: string | number): Promise<MenuItem> {
  const response = await apiClient.get<ApiEnvelope<MenuItem>>(`${endpoints.admin.menuItems}/${id}`);

  return response.data.data;
}

export async function createAdminMenuItem(payload: AdminMenuItemPayload): Promise<MenuItem> {
  const response = await apiClient.post<ApiEnvelope<MenuItem>>(endpoints.admin.menuItems, payload);

  return response.data.data;
}

export async function updateAdminMenuItem(
  id: string | number,
  payload: AdminMenuItemPayload,
): Promise<MenuItem> {
  const response = await apiClient.put<ApiEnvelope<MenuItem>>(`${endpoints.admin.menuItems}/${id}`, payload);

  return response.data.data;
}
