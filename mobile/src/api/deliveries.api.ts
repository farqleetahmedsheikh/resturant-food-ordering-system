import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { Delivery } from '@/src/types/delivery';

export async function getRiderDeliveries(): Promise<Delivery[]> {
  const response = await apiClient.get<ApiEnvelope<Delivery[]>>(endpoints.rider.deliveries);

  return response.data.data;
}

export async function getRiderDashboard(): Promise<Record<string, unknown>> {
  const response = await apiClient.get<ApiEnvelope<Record<string, unknown>>>(endpoints.rider.dashboard);

  return response.data.data;
}
