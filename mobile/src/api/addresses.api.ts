import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { CustomerAddress, CustomerAddressPayload } from '@/src/types/address';

export async function getCustomerAddresses(): Promise<CustomerAddress[]> {
  const response = await apiClient.get<ApiEnvelope<CustomerAddress[]>>(endpoints.customer.addresses);

  return response.data.data;
}

export async function createCustomerAddress(payload: CustomerAddressPayload): Promise<CustomerAddress> {
  const response = await apiClient.post<ApiEnvelope<CustomerAddress>>(endpoints.customer.addresses, payload);

  return response.data.data;
}

export async function updateCustomerAddress(
  id: string | number,
  payload: CustomerAddressPayload,
): Promise<CustomerAddress> {
  const response = await apiClient.put<ApiEnvelope<CustomerAddress>>(`${endpoints.customer.addresses}/${id}`, payload);

  return response.data.data;
}

export async function deleteCustomerAddress(id: string | number): Promise<void> {
  await apiClient.delete(`${endpoints.customer.addresses}/${id}`);
}
