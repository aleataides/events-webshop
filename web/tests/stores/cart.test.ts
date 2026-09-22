import { useCartStore } from '@/stores/cart'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it } from 'vitest'

describe('useCartStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
  })

  it('persists the cart id to localStorage on setCart', () => {
    const store = useCartStore()

    store.setCart({ id: 'cart-1', expiresAt: '2026-01-01T00:00:00Z', items: [], total: '0.00' })

    expect(localStorage.getItem('cartId')).toBe('cart-1')
  })

  it('clears the stored cart id on clearCart', () => {
    const store = useCartStore()
    store.setCart({ id: 'cart-1', expiresAt: '2026-01-01T00:00:00Z', items: [], total: '0.00' })

    store.clearCart()

    expect(store.cart).toBeNull()
    expect(localStorage.getItem('cartId')).toBeNull()
  })
})
