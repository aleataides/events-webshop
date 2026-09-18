import type { Cart } from '@/types/cart'

import apiClient from '@/api/client'

export function getCart(affiliateId: string): Promise<Cart | null> {
  return apiClient
    .get<{ data: Cart | null }>(`/api/${affiliateId}/cart`)
    .then((response) => response.data.data)
}

export function addCartItem(affiliateId: string, priceId: string, qty: number): Promise<Cart> {
  return apiClient
    .post<{ data: Cart }>(`/api/${affiliateId}/cart/items`, { priceId, qty })
    .then((response) => response.data.data)
}
