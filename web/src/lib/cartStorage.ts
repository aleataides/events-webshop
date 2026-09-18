const CART_ID_KEY = 'cartId'

/**
 * Server-issued cart id, persisted so it survives reloads — see
 * docs/shared/business-rules.md#cart-identity. Shared by the API client's
 * X-Cart-Id interceptor and the cart store.
 */
export function getStoredCartId(): string | null {
  return localStorage.getItem(CART_ID_KEY)
}

export function setStoredCartId(cartId: string): void {
  localStorage.setItem(CART_ID_KEY, cartId)
}

export function clearStoredCartId(): void {
  localStorage.removeItem(CART_ID_KEY)
}
