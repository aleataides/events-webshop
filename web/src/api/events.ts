import type { EventDetail, EventListItem } from '@/types/event'
import type { Paginated } from '@/types/pagination'

import apiClient from '@/api/client'

export interface EventListParams {
  q?: string
  category?: string
  date_from?: string
  date_to?: string
  cursor?: string
}

export function listEvents(
  affiliateId: string,
  params: EventListParams,
): Promise<Paginated<EventListItem>> {
  return apiClient
    .get<Paginated<EventListItem>>(`/api/${affiliateId}/events`, { params })
    .then((response) => response.data)
}

export function getEvent(affiliateId: string, eventId: string): Promise<EventDetail> {
  return apiClient
    .get<{ data: EventDetail }>(`/api/${affiliateId}/events/${eventId}`)
    .then((response) => response.data.data)
}
