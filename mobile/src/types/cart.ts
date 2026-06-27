import type { Restaurant } from './restaurant';

export type LocalCartItem = {
  menuItemId: number;
  name: string;
  imageUrl: string | null;
  unitPrice: number;
  quantity: number;
  notes: string;
  isAvailable: boolean;
};

export type BackendCartItem = {
  id: number;
  menu_item_id: number;
  name: string;
  image_url: string | null;
  quantity: number;
  unit_price: number;
  base_price: number;
  addons_total: number;
  line_total: number;
  size: {
    id: number;
    name: string;
    price: number;
  } | null;
  addons: {
    id: number;
    name: string;
    type: string | null;
    price: number;
  }[];
};

export type BackendCart = {
  id: number;
  status: string;
  count: number;
  subtotal: number;
  delivery_fee: number;
  minimum_order_amount: number;
  total: number;
  currency: string;
  restaurant: Restaurant | null;
  items: BackendCartItem[];
};
