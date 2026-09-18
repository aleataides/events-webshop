import { clearStoredCartId, setStoredCartId } from '@/lib/cartStorage'
import { defineStore } from 'pinia'

interface CartItem {
  id: string
  price: {
    id: string
    name: string
    value: string
    currency: string
  }
  qty: number
  subtotal: string
}

interface Cart {
  id: string
  expiresAt: string
  items: CartItem[]
  total: string
}

/**
 * Read-through cache of the BE cart — backend is the source of truth, see
 * docs/web/conventions.md#state.
 */
export const useCartStore = defineStore('cart', {
  state: () => ({
    cart: null as Cart | null,
  }),
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
