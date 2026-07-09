import { apiClient } from './client';
import { endpoints } from './endpoints';
import type { ApiEnvelope } from '@/src/types/api';
import type { AuthUser } from '@/src/types/auth';

export type ProfileUpdatePayload = {
  name: string;
  phone?: string | null;
};

export async function updateCustomerProfile(payload: ProfileUpdatePayload): Promise<AuthUser> {
  const response = await apiClient.put<ApiEnvelope<AuthUser>>(endpoints.customer.profile, payload);

  return response.data.data;
}
