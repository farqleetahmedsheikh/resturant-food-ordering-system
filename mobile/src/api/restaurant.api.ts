import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { Restaurant } from '@/src/types/restaurant';

export async function getRestaurant(): Promise<Restaurant> {
  const response = await apiClient.get<ApiEnvelope<Restaurant>>(endpoints.restaurant);

  return response.data.data;
}
