export const RouteName = {
  EventList: 'event-list',
  EventDetail: 'event-detail',
  Cart: 'cart',
  NotFound: 'not-found',
} as const

export type RouteName = (typeof RouteName)[keyof typeof RouteName]
