import AsyncStorage from '@react-native-async-storage/async-storage';
import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

type CartState = {
  itemCount: number;
  setItemCount: (count: number) => void;
  clearCartState: () => void;
};

export const useCartStore = create<CartState>()(
  persist(
    (set) => ({
      itemCount: 0,
      setItemCount: (count) => set({ itemCount: Math.max(0, count) }),
      clearCartState: () => set({ itemCount: 0 }),
    }),
    {
      name: 'arcade-kebab-house.cart-ui',
      storage: createJSONStorage(() => AsyncStorage),
    },
  ),
);
