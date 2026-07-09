import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { DeviceRegistrationPayload, UserDevice } from '@/src/types/device';

export async function getRegisteredDevices(): Promise<UserDevice[]> {
  const response = await apiClient.get<ApiEnvelope<UserDevice[]>>(endpoints.devices);

  return response.data.data;
}

export async function registerDevice(payload: DeviceRegistrationPayload): Promise<UserDevice> {
  const response = await apiClient.post<ApiEnvelope<UserDevice>>(endpoints.devices, payload);

  return response.data.data;
}

export async function revokeDevice(id: string | number): Promise<void> {
  await apiClient.delete(`${endpoints.devices}/${id}`);
}
