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
}

export interface Cart {
  id: string
  expiresAt: string
  items: CartItem[]
  total: string
}
