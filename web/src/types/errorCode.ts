// Mirrors api/src/Enums/ErrorCode.php — the stable codes in the API's error envelope.
export const ErrorCode = {
  AffiliateNotFound: 'affiliate_not_found',
  CartEmpty: 'cart_empty',
  CartExpired: 'cart_expired',
  EventNotFound: 'event_not_found',
  InsufficientStock: 'insufficient_stock',
  InvalidRequest: 'invalid_request',
  PriceNotFound: 'price_not_found',
  TicketReservationNotFound: 'cart_item_not_found',
} as const

export type ErrorCode = (typeof ErrorCode)[keyof typeof ErrorCode]
