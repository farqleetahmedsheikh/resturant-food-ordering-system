export type CustomerAddress = {
  id: number;
  label: string | null;
  recipient_name: string | null;
  phone: string | null;
  address: string;
  latitude: number | null;
  longitude: number | null;
  delivery_notes: string | null;
  is_default: boolean;
  created_at?: string | null;
  updated_at?: string | null;
};

export type CustomerAddressPayload = {
  label?: string | null;
  recipient_name?: string | null;
  phone?: string | null;
  address: string;
  latitude?: number | null;
  longitude?: number | null;
  delivery_notes?: string | null;
  is_default?: boolean;
};
