import AsyncStorage from '@react-native-async-storage/async-storage';
import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

import type { LocalCartItem } from '@/src/types/cart';
import type { MenuItem, MenuItemAddon, MenuItemSize } from '@/src/types/menu';

type AddItemInput = {
  item: MenuItem;
  quantity?: number;
  size?: MenuItemSize | null;
  addons?: MenuItemAddon[];
  notes?: string;
};

type CartState = {
  items: LocalCartItem[];
  hasHydrated: boolean;
  addItem: (input: AddItemInput) => void;
  removeItem: (lineKey: string) => void;
  updateQuantity: (lineKey: string, quantity: number) => void;
  updateNotes: (lineKey: string, notes: string) => void;
  clearCart: () => void;
  clearCartState: () => void;
  getSubtotal: () => number;
  getTotal: (deliveryFee?: number) => number;
  getItemCount: () => number;
  setItemCount: (count: number) => void;
  hydrate: () => Promise<void>;
};

function toCartItem(input: AddItemInput): LocalCartItem {
  const quantity = Math.max(1, Math.min(99, Math.floor(input.quantity ?? 1)));
  const addons = [...(input.addons ?? [])].sort((a, b) => a.id - b.id);
  const addonsTotal = roundMoney(addons.reduce((sum, addon) => sum + Number(addon.price ?? 0), 0));
  const basePrice = Number(input.size?.price ?? input.item.price ?? 0);
  const notes = input.notes?.trim().slice(0, 500) ?? '';
  const lineKey = createCartLineKey(input.item.id, input.size?.id ?? null, addons.map((addon) => addon.id), notes);

  return {
    lineKey,
    menuItemId: input.item.id,
    name: input.item.name,
    imageUrl: input.item.image_url,
    unitPrice: roundMoney(basePrice + addonsTotal),
    basePrice,
    sizeId: input.size?.id ?? null,
    sizeName: input.size?.name ?? null,
    addons: addons.map((addon) => ({
      id: addon.id,
      name: addon.name,
      type: addon.type,
      price: Number(addon.price ?? 0),
    })),
    addonsTotal,
    quantity,
    notes,
    isAvailable: input.item.is_available,
  };
}

function roundMoney(value: number): number {
  return Math.round(value * 100) / 100;
}

function createCartLineKey(menuItemId: number, sizeId: number | null, addonIds: number[], notes: string): string {
  return JSON.stringify({
    menuItemId,
    sizeId,
    addonIds: [...addonIds].sort((a, b) => a - b),
    notes: notes.trim(),
  });
}

function lineKeyFor(item: LocalCartItem): string {
  return item.lineKey ?? createCartLineKey(
    item.menuItemId,
    item.sizeId ?? null,
    (item.addons ?? []).map((addon) => addon.id),
    item.notes ?? '',
  );
}

function normalizeStoredItem(item: LocalCartItem): LocalCartItem {
  const addons = item.addons ?? [];
  const notes = item.notes ?? '';
  const sizeId = item.sizeId ?? null;
  const basePrice = Number(item.basePrice ?? item.unitPrice ?? 0);
  const addonsTotal = Number(item.addonsTotal ?? addons.reduce((sum, addon) => sum + Number(addon.price ?? 0), 0));

  return {
    ...item,
    lineKey: item.lineKey ?? createCartLineKey(item.menuItemId, sizeId, addons.map((addon) => addon.id), notes),
    basePrice,
    sizeId,
    sizeName: item.sizeName ?? null,
    addons,
    addonsTotal,
    unitPrice: Number(item.unitPrice ?? basePrice + addonsTotal),
    notes,
  };
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      hasHydrated: false,

      addItem: (input) => {
        const nextItem = toCartItem(input);

        set((state) => {
          const existing = state.items.find((item) => lineKeyFor(item) === nextItem.lineKey);

          if (!existing) {
            return { items: [...state.items, nextItem] };
          }

          return {
            items: state.items.map((item) =>
              lineKeyFor(item) === nextItem.lineKey
                ? {
                    ...item,
                    name: nextItem.name,
                    imageUrl: nextItem.imageUrl,
                    unitPrice: nextItem.unitPrice,
                    basePrice: nextItem.basePrice,
                    sizeId: nextItem.sizeId,
                    sizeName: nextItem.sizeName,
                    addons: nextItem.addons,
                    addonsTotal: nextItem.addonsTotal,
                    isAvailable: nextItem.isAvailable,
                    quantity: Math.min(99, item.quantity + nextItem.quantity),
                    notes: nextItem.notes || item.notes,
                  }
                : item,
            ),
          };
        });
      },

      removeItem: (lineKey) => {
        set((state) => ({
          items: state.items.filter((item) => lineKeyFor(item) !== lineKey),
        }));
      },

      updateQuantity: (lineKey, quantity) => {
        const normalizedQuantity = Math.max(1, Math.min(99, Math.floor(quantity)));

        set((state) => ({
          items: state.items.map((item) =>
            lineKeyFor(item) === lineKey ? { ...item, quantity: normalizedQuantity } : item,
          ),
        }));
      },

      updateNotes: (lineKey, notes) => {
        set((state) => ({
          items: state.items.map((item) =>
            lineKeyFor(item) === lineKey ? { ...item, notes: notes.slice(0, 500) } : item,
          ),
        }));
      },

      clearCart: () => set({ items: [] }),
      clearCartState: () => set({ items: [] }),

      getSubtotal: () =>
        roundMoney(
          get().items.reduce((sum, item) => sum + Number(item.unitPrice ?? 0) * item.quantity, 0),
        ),

      getTotal: (deliveryFee = 0) => roundMoney(get().getSubtotal() + Number(deliveryFee ?? 0)),

      getItemCount: () => get().items.reduce((sum, item) => sum + item.quantity, 0),

      setItemCount: () => undefined,

      hydrate: async () => {
        await useCartStore.persist.rehydrate();
      },
    }),
    {
      name: 'arcade-kebab-house.cart',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({ items: state.items }),
      onRehydrateStorage: () => (state) => {
        if (state) {
          state.items = state.items.map(normalizeStoredItem);
          state.hasHydrated = true;
        }
      },
    },
  ),
);
