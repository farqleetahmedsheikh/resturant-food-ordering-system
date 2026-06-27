import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope, PaginationMeta } from '@/src/types/api';
import type { Category, MenuItem } from '@/src/types/menu';

export async function getCategories(): Promise<Category[]> {
  const response = await apiClient.get<ApiEnvelope<Category[]>>(endpoints.categories);

  return response.data.data;
}

export async function getMenuItems(params?: {
  featured?: boolean;
  category?: string | number;
  search?: string;
  include_unavailable?: boolean;
  per_page?: number;
}): Promise<{ items: MenuItem[]; meta?: PaginationMeta }> {
  const response = await apiClient.get<ApiEnvelope<MenuItem[]>>(endpoints.menuItems, {
    params,
  });

  return {
    items: response.data.data,
    meta: response.data.meta as PaginationMeta | undefined,
  };
}

export async function getMenuItem(id: string | number): Promise<MenuItem> {
  const response = await apiClient.get<ApiEnvelope<MenuItem>>(`${endpoints.menuItems}/${id}`, {
    params: {
      include_unavailable: true,
    },
  });

  return response.data.data;
}
