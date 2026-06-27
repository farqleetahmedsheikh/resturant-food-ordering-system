import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { Order } from '@/src/types/order';

export async function getCustomerOrders(): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.customer.orders);

  return response.data.data;
}

export async function getAdminOrders(): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.admin.orders);

  return response.data.data;
}
