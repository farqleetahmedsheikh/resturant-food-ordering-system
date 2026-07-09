export type UserRole = 'customer' | 'rider' | 'admin';

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: UserRole | string;
  is_active: boolean;
  last_known_latitude?: number | null;
  last_known_longitude?: number | null;
  last_location_updated_at?: string | null;
  created_at?: string | null;
};

export type AuthSession = {
  tokenType: 'Bearer';
  accessToken: string;
  expiresAt: string | null;
  abilities: string[];
  user: AuthUser;
};
