import type { Cart } from '@/types/cart'

import { clearStoredCartId, setStoredCartId } from '@/lib/cartStorage'
import { defineStore } from 'pinia'

/**
 * Read-through cache of the BE cart — backend is the source of truth, see
 * docs/web/conventions.md#state.
 */
export const useCartStore = defineStore('cart', {
  state: () => ({
    cart: null as Cart | null,
  }),
  getters: {
    itemCount: (state): number => state.cart?.items.reduce((sum, item) => sum + item.qty, 0) ?? 0,
  },
  actions: {
    setCart(cart: Cart): void {
      this.cart = cart
      setStoredCartId(cart.id)
    },
    clearCart(): void {
      this.cart = null
      clearStoredCartId()
    },
  },
})
