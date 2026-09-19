import type { Order } from '@/types/order'

import { buyCart, getCart, removeCartItem, updateCartItem } from '@/api/cart'
import { getApiErrorCode } from '@/lib/apiError'
import { useCartStore } from '@/stores/cart'
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'

export function useCart(affiliateId: string) {
  const cartStore = useCartStore()
  const { cart } = storeToRefs(cartStore)

  const loading = ref(true)
  const expired = ref(false)
  const order = ref<Order | null>(null)

  function expireLocally(): void {
    expired.value = true
    cartStore.clearCart()
  }

  async function runOrHandleExpiry(action: () => Promise<void>): Promise<void> {
    try {
      await action()
    } catch (error) {
      if (getApiErrorCode(error) !== 'cart_expired') {
        throw error
      }

      expireLocally()
    }
  }

  async function refresh(): Promise<void> {
    loading.value = true
    await runOrHandleExpiry(async () => {
      const result = await getCart(affiliateId)
      if (result) {
        cartStore.setCart(result)
      } else {
        cartStore.clearCart()
      }
    })
    loading.value = false
  }

  onMounted(refresh)

  function updateQty(itemId: string, qty: number): Promise<void> {
    return runOrHandleExpiry(async () => {
      cartStore.setCart(await updateCartItem(affiliateId, itemId, qty))
    })
  }

  function removeItem(itemId: string): Promise<void> {
    return runOrHandleExpiry(async () => {
      cartStore.setCart(await removeCartItem(affiliateId, itemId))
    })
  }

  function buy(): Promise<void> {
    return runOrHandleExpiry(async () => {
      order.value = await buyCart(affiliateId)
      cartStore.clearCart()
    })
  }

  return { cart, loading, expired, order, updateQty, removeItem, buy, expireLocally }
}
