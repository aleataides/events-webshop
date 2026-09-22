import type { Cart } from '@/types/cart'
import type { Order } from '@/types/order'

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

export function updateCartItem(affiliateId: string, itemId: string, qty: number): Promise<Cart> {
  return apiClient
    .patch<{ data: Cart }>(`/api/${affiliateId}/cart/items/${itemId}`, { qty })
    .then((response) => response.data.data)
}

export function removeCartItem(affiliateId: string, itemId: string): Promise<Cart> {
  return apiClient
    .delete<{ data: Cart }>(`/api/${affiliateId}/cart/items/${itemId}`)
    .then((response) => response.data.data)
}

export function buyCart(affiliateId: string): Promise<Order> {
  return apiClient
    .post<{ data: Order }>(`/api/${affiliateId}/cart/buy`)
    .then((response) => response.data.data)
}
