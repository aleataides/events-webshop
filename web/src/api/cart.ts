import type { Cart } from '@/types/cart'

import apiClient from '@/api/client'

export function getCart(affiliateId: string): Promise<Cart | null> {
  return apiClient
    .get<{ data: Cart | null }>(`/api/${affiliateId}/cart`)
    .then((response) => response.data.data)
}
