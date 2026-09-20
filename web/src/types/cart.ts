import type { Venue } from '@/types/event'

export interface CartItem {
  id: string
  price: {
    id: string
    name: string
    value: string
    currency: string
  }
  qty: number
  subtotal: string
  area: {
    id: string
    name: string
  }
  event: {
    id: string
    title: string
    start: string
    image: {
      id: string
      copyright: string
    }
    venue: Venue
  }
}

export interface Cart {
  id: string
  expiresAt: string
  items: CartItem[]
  total: string
}
