import type { CartItem } from '@/types/cart'

export interface Order {
  id: string
  createdAt: string
  items: CartItem[]
  total: string
}
