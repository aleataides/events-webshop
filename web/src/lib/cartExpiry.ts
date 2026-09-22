import { getApiErrorCode } from '@/lib/apiError'
import { useCartStore } from '@/stores/cart'
import { ErrorCode } from '@/types/errorCode'

/**
 * Retries a cart-adding call once after clearing a stale cart id — safe only
 * for adds, since a fresh cart has no reservation to update/remove.
 */
export async function addWithExpiryRetry<T>(action: () => Promise<T>): Promise<T> {
  try {
    return await action()
  } catch (error) {
    if (getApiErrorCode(error) !== ErrorCode.CartExpired) {
      throw error
    }

    useCartStore().clearCart()

    return action()
  }
}
