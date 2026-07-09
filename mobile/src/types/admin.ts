import type { Order } from './order';

export type AdminDashboardCards = {
  total_orders: number;
  pending_orders: number;
  preparing_orders: number;
  assigned_deliveries: number;
  out_for_delivery: number;
  delivered_orders: number;
  total_riders: number;
  total_paid_revenue: number;
  total_categories: number;
  active_categories: number;
  total_menu_items: number;
  available_menu_items: number;
  featured_items: number;
  restaurant_is_open: boolean;
};

export type AdminDashboard = {
  cards: AdminDashboardCards;
  latest_orders: Order[];
};

export type AdminOrderFilters = {
  status?: string;
  payment_status?: string;
  rider_id?: number | string;
  date_from?: string;
  date_to?: string;
  search?: string;
  per_page?: number;
};

export type AdminOrderStatusPayload = {
  order_status: string;
  reason?: string | null;
};

export type AssignRiderPayload = {
  rider_id: number;
};

export type AdminRestaurantPayload = {
  name: string;
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  formatted_address?: string | null;
  short_description?: string | null;
  opening_time?: string | null;
  closing_time?: string | null;
  timezone: string;
  latitude?: number | null;
  longitude?: number | null;
  delivery_fee: number;
  minimum_order_amount: number;
  is_open: boolean;
};

export type AdminMenuItemsParams = {
  search?: string;
  category_id?: number | string;
  availability?: 'available' | 'unavailable';
  per_page?: number;
};

export type AdminMenuItemPayload = {
  restaurant_id?: number | null;
  category_id?: number | null;
  name: string;
  slug?: string | null;
  description?: string | null;
  price: number;
  compare_at_price?: number | null;
  preparation_time?: number | null;
  calories?: number | null;
  is_featured: boolean;
  is_available: boolean;
  sort_order?: number;
};
