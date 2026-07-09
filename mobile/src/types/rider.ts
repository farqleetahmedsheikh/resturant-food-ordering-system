import type { Order } from './order';

export type Rider = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  is_active: boolean;
  last_known_latitude?: number | null;
  last_known_longitude?: number | null;
  last_location_updated_at?: string | null;
  assigned_orders_count?: number | null;
  delivered_orders_count?: number | null;
  created_at?: string | null;
};

export type AdminRiderDetail = {
  rider: Rider;
  active_orders: Order[];
  delivery_history: Order[];
};
