export type Category = {
  id: number;
  restaurant_id: number | null;
  name: string;
  slug: string;
  description: string | null;
  image_url: string | null;
  sort_order: number;
  is_active: boolean;
  menu_items_count?: number;
};

export type MenuItemSize = {
  id: number;
  name: string;
  price: number;
  sort_order: number;
  is_active: boolean;
};

export type MenuItemAddon = {
  id: number;
  name: string;
  type: string | null;
  price: number;
  sort_order: number;
  is_active: boolean;
};

export type MenuItem = {
  id: number;
  restaurant_id: number | null;
  category_id: number | null;
  category?: Category | null;
  name: string;
  slug: string;
  description: string | null;
  price: number;
  compare_at_price: number | null;
  image_url: string | null;
  preparation_time: number | null;
  calories: number | null;
  is_featured: boolean;
  is_available: boolean;
  sort_order: number;
  sizes?: MenuItemSize[];
  addons?: MenuItemAddon[];
};
