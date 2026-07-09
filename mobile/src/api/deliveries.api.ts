import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { AuthUser } from '@/src/types/auth';
import type {
  Delivery,
  DeliveryStatusPayload,
  RiderDashboard,
  RiderLocationPayload,
} from '@/src/types/delivery';
import type { Order } from '@/src/types/order';

export async function getRiderDeliveries(): Promise<Delivery[]> {
  const response = await apiClient.get<ApiEnvelope<Delivery[]>>(endpoints.rider.deliveries);

  return response.data.data;
}

export async function getRiderDelivery(id: string | number): Promise<Order> {
  const response = await apiClient.get<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}`);

  return response.data.data;
}

export async function getRiderDeliveryHistory(): Promise<Order[]> {
  const response = await apiClient.get<ApiEnvelope<Order[]>>(endpoints.rider.deliveryHistory);

  return response.data.data;
}

export async function getRiderDashboard(): Promise<RiderDashboard> {
  const response = await apiClient.get<ApiEnvelope<RiderDashboard>>(endpoints.rider.dashboard);

  return response.data.data;
}

export async function acceptRiderDelivery(id: string | number): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}/accept`);

  return response.data.data;
}

export async function markRiderDeliveryPickedUp(id: string | number): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}/picked-up`);

  return response.data.data;
}

export async function markRiderDeliveryOutForDelivery(id: string | number): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}/out-for-delivery`);

  return response.data.data;
}

export async function markRiderDeliveryDelivered(id: string | number): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}/delivered`);

  return response.data.data;
}

export async function updateRiderDeliveryStatus(
  id: string | number,
  payload: DeliveryStatusPayload,
): Promise<Order> {
  const response = await apiClient.post<ApiEnvelope<Order>>(`${endpoints.rider.deliveries}/${id}/status`, payload);

  return response.data.data;
}

export async function updateRiderLocation(payload: RiderLocationPayload): Promise<AuthUser> {
  const response = await apiClient.post<ApiEnvelope<AuthUser>>(endpoints.rider.location, payload);

  return response.data.data;
}
