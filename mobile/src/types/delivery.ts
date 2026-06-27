export type Delivery = {
  id: number;
  order_number: string;
  customer_name: string;
  customer_phone: string;
  delivery_address: string;
  total: number;
  order_status: string;
  delivery_status?: string | null;
  assigned_at?: string | null;
};
