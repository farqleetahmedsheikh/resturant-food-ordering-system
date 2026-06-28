export type Order = {
  id: number;
  order_number: string;
  customer?: {
    id: number | null;
    name: string | null;
    phone: string | null;
    email: string | null;
  };
  delivery_address?: string | null;
  delivery_latitude?: number | null;
  delivery_longitude?: number | null;
  order_notes?: string | null;
  subtotal?: number;
  delivery_fee?: number;
  total: number;
  currency?: string;
  payment_method?: string;
  payment_status: string;
  order_status: string;
  order_status_label?: string | null;
  assigned_at?: string | null;
  picked_up_at?: string | null;
  delivered_at?: string | null;
  items?: OrderItem[];
  delivery?: {
    id?: number;
    status?: string;
    status_label?: string | null;
  } | null;
  status_history?: OrderStatusHistory[];
  created_at: string;
  updated_at?: string | null;
};

export type OrderItem = {
  id: number;
  menu_item_id: number | null;
  item_name: string;
  size_name?: string | null;
  size_price?: number | null;
  addons?: {
    id?: number;
    name: string;
    type?: string | null;
    price?: number;
  }[];
  addons_total?: number;
  quantity: number;
  price: number;
  total: number;
};

export type OrderStatusHistory = {
  id: number;
  previous_status?: string | null;
  new_status: string;
  changed_by_role?: string | null;
  reason?: string | null;
  created_at?: string | null;
};

export type CheckoutPayload = {
  customer_name: string;
  customer_phone: string;
  customer_email?: string | null;
  delivery_address: string;
  delivery_latitude?: number | null;
  delivery_longitude?: number | null;
  order_notes?: string | null;
  payment_method?: 'stripe';
};

export type CheckoutResult = {
  order_id: number;
  checkout_url: string;
  stripe_checkout_session_id: string;
  order: Order;
};
