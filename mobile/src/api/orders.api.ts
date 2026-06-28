import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { CheckoutPayload, CheckoutResult, Order } from '@/src/types/order';

export async function getCustomerOrders(): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.customer.orders);

  return response.data.data;
}

export async function getCustomerOrder(id: string | number): Promise<Order> {
  const response = await apiClient.get<ApiEnvelope<Order>>(`${endpoints.customer.orders}/${id}`);

  return response.data.data;
}

export async function checkout(payload: CheckoutPayload, idempotencyKey: string): Promise<CheckoutResult> {
  const response = await apiClient.post<ApiEnvelope<CheckoutResult>>(endpoints.customer.checkout, payload, {
    headers: {
      'Idempotency-Key': idempotencyKey,
    },
  });

  return response.data.data;
}

export async function getAdminOrders(): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.admin.orders);

  return response.data.data;
}
