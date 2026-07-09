import type { Order } from './order';

export type Delivery = Order;

export type RiderDashboard = {
  total_assigned_orders: number;
  active_deliveries: number;
  delivered_orders: number;
  failed_deliveries: number;
  latest_orders: Order[];
};

export type RiderLocationPayload = {
  latitude: number;
  longitude: number;
};

export type DeliveryStatusPayload = {
  status: 'accepted' | 'picked_up' | 'out_for_delivery' | 'delivered' | 'failed';
  notes?: string | null;
};
