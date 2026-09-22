import type { EventCategory } from '@/types/event'

import apiClient from '@/api/client'

export function listCategories(affiliateId: string): Promise<EventCategory[]> {
  return apiClient
    .get<{ data: EventCategory[] }>(`/api/${affiliateId}/categories`)
    .then((response) => response.data.data)
}
