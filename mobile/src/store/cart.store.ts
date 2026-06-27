import AsyncStorage from '@react-native-async-storage/async-storage';
import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

import type { LocalCartItem } from '@/src/types/cart';
import type { MenuItem } from '@/src/types/menu';

type AddItemInput = {
  item: MenuItem;
  quantity?: number;
  notes?: string;
};

type CartState = {
  items: LocalCartItem[];
  hasHydrated: boolean;
  addItem: (input: AddItemInput) => void;
  removeItem: (menuItemId: number) => void;
  updateQuantity: (menuItemId: number, quantity: number) => void;
  updateNotes: (menuItemId: number, notes: string) => void;
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

  return {
    menuItemId: input.item.id,
    name: input.item.name,
    imageUrl: input.item.image_url,
    unitPrice: Number(input.item.price ?? 0),
    quantity,
    notes: input.notes?.trim() ?? '',
    isAvailable: input.item.is_available,
  };
}

function roundMoney(value: number): number {
  return Math.round(value * 100) / 100;
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      hasHydrated: false,

      addItem: (input) => {
        const nextItem = toCartItem(input);

        set((state) => {
          const existing = state.items.find((item) => item.menuItemId === nextItem.menuItemId);

          if (!existing) {
            return { items: [...state.items, nextItem] };
          }

          return {
            items: state.items.map((item) =>
              item.menuItemId === nextItem.menuItemId
                ? {
                    ...item,
                    name: nextItem.name,
                    imageUrl: nextItem.imageUrl,
                    unitPrice: nextItem.unitPrice,
                    isAvailable: nextItem.isAvailable,
                    quantity: Math.min(99, item.quantity + nextItem.quantity),
                    notes: nextItem.notes || item.notes,
                  }
                : item,
            ),
          };
        });
      },

      removeItem: (menuItemId) => {
        set((state) => ({
          items: state.items.filter((item) => item.menuItemId !== menuItemId),
        }));
      },

      updateQuantity: (menuItemId, quantity) => {
        const normalizedQuantity = Math.max(1, Math.min(99, Math.floor(quantity)));

        set((state) => ({
          items: state.items.map((item) =>
            item.menuItemId === menuItemId ? { ...item, quantity: normalizedQuantity } : item,
          ),
        }));
      },

      updateNotes: (menuItemId, notes) => {
        set((state) => ({
          items: state.items.map((item) =>
            item.menuItemId === menuItemId ? { ...item, notes: notes.slice(0, 300) } : item,
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
          state.hasHydrated = true;
        }
      },
    },
  ),
);
