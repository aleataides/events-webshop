import { getApiErrorCode } from '@/lib/apiError'
import { useCartStore } from '@/stores/cart'

/**
 * Retries a cart-adding call once after clearing a stale cart id — safe only
 * for adds, since a fresh cart has no reservation to update/remove.
 */
export async function addWithExpiryRetry<T>(action: () => Promise<T>): Promise<T> {
  try {
    return await action()
  } catch (error) {
    if (getApiErrorCode(error) !== 'cart_expired') {
      throw error
    }

    useCartStore().clearCart()

    return action()
  }
}
