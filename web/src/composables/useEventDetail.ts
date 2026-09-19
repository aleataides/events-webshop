import type { EventDetail } from '@/types/event'

import { getEvent } from '@/api/events'
import { onMounted, ref } from 'vue'

export function useEventDetail(affiliateId: string, eventId: string) {
  const event = ref<EventDetail | null>(null)
  const loading = ref(true)

  onMounted(async () => {
    event.value = await getEvent(affiliateId, eventId)
    loading.value = false
  })

  return { event, loading }
}
