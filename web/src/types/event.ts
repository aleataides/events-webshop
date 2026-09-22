export interface Venue {
  id: string
  name: string
  street: string
  zipCode: string
  city: string
  country: string
  geo: {
    latitude: string
    longitude: string
  }
}

export interface EventCategory {
  id: string
  name: string
}

export interface EventListItem {
  id: string
  title: string
  subtitle: string
  start: string
  end: string
  salesEnd: string
  doorsOpen: string | null
  doorsClose: string | null
  status: string
  eventType: string
  soldout: boolean
  image: {
    id: string
    copyright: string
  }
  minPrice: string | null
  maxPrice: string | null
  currency: string | null
  venue: Venue
  categories: EventCategory[]
}

export interface Price {
  id: string
  name: string
  value: string
  basePrice: string
  ticketFee: string
  outletFee: string
  currency: string
}

export interface Area {
  id: string
  name: string
  capacity: number
  available: number
  prices: Price[]
}

export interface EventDetail extends EventListItem {
  description: string
  priceInfo: string
  areas: Area[]
}
