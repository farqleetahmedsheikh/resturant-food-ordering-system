import { apiClient } from './client';
import { endpoints } from './endpoints';
import type {
  AdminOrderFilters,
  AdminOrderStatusPayload,
  AssignRiderPayload,
} from '@/src/types/admin';
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

export async function getAdminOrders(filters: AdminOrderFilters = {}): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.admin.orders, {
    params: filters,
  });

  return response.data.data;
}

export async function getAdminOrder(id: string | number): Promise<Order> {
  const response = await apiClient.get<ApiEnvelope<Order>>(`${endpoints.admin.orders}/${id}`);

  return response.data.data;
}

export async function updateAdminOrderStatus(
  id: string | number,
  payload: AdminOrderStatusPayload,
): Promise<Order> {
  const response = await apiClient.patch<ApiEnvelope<Order>>(`${endpoints.admin.orders}/${id}/status`, payload);

  return response.data.data;
}

export async function assignAdminOrderRider(
  id: string | number,
  payload: AssignRiderPayload,
): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.admin.orders}/${id}/assign-rider`, payload);

  return response.data.data;
}

export async function unassignAdminOrderRider(id: string | number): Promise<Order> {
  const response = await apiClient.delete<ApiEnvelope<Order>>(`${endpoints.admin.orders}/${id}/unassign-rider`);

  return response.data.data;
}
