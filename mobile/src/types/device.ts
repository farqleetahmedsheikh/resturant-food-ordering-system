export type UserDevice = {
  id: number;
  device_uuid: string | null;
  device_name: string | null;
  platform: 'ios' | 'android' | 'web' | string;
  app_version: string | null;
  last_seen_at: string | null;
  revoked_at: string | null;
  created_at?: string | null;
};

export type DeviceRegistrationPayload = {
  device_uuid?: string | null;
  device_name?: string | null;
  platform: 'ios' | 'android' | 'web';
  push_token: string;
  app_version?: string | null;
};
