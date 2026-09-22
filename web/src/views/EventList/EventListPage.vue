<script setup lang="ts">
import type { EventCategory, EventListItem } from '@/types/event'

import { listCategories } from '@/api/categories'
import { listEvents } from '@/api/events'
import EmptyState from '@/components/EmptyState.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { useCart } from '@/composables/useCart'
import { onActivated, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue'
import { onBeforeRouteLeave, useRoute } from 'vue-router'

import ExpiredCartDialog from '../Cart/components/ExpiredCartDialog.vue'
import EventCard from './components/EventCard.vue'
import EventFilterBar from './components/EventFilterBar.vue'

// Named explicitly for App.vue's <keep-alive :include>  — relying on
// filename inference would be fragile across build/minification.
defineOptions({ name: 'EventListPage' })

const route = useRoute()
const affiliateId = route.params.affiliateId as string

const { expired } = useCart(affiliateId)

const search = ref('')
const categoryIds = ref<string[]>([])
const dateFrom = ref('')
const dateTo = ref('')

const categories = ref<EventCategory[]>([])
const events = ref<EventListItem[]>([])
const cursor = ref<string | null>(null)
const hasMore = ref(false)
const loading = ref(false)

let searchDebounce: ReturnType<typeof setTimeout> | undefined

async function fetchPage(reset: boolean): Promise<void> {
  if (loading.value) {
    return
  }

  loading.value = true
  try {
    const result = await listEvents(affiliateId, {
      q: search.value || undefined,
      category: categoryIds.value,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      cursor: reset ? undefined : (cursor.value ?? undefined),
    })

    events.value = reset ? result.data : [...events.value, ...result.data]
    cursor.value = result.meta.next_cursor
    hasMore.value = result.meta.has_more
  } finally {
    loading.value = false
  }
}

watch([categoryIds, dateFrom, dateTo], () => fetchPage(true))

watch(search, () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => fetchPage(true), 300)
})

const sentinel = useTemplateRef('sentinel')
let observer: IntersectionObserver | undefined

onMounted(async () => {
  categories.value = await listCategories(affiliateId)
  await fetchPage(true)

  observer = new IntersectionObserver((entries) => {
    if (entries[0]?.isIntersecting && hasMore.value && !loading.value) {
      void fetchPage(false)
    }
  })
  if (sentinel.value) {
    observer.observe(sentinel.value)
  }
})

onUnmounted(() => {
  observer?.disconnect()
  clearTimeout(searchDebounce)
})

// router-link navigation isn't restored by Vue Router's scrollBehavior
// (only browser back/forward is) — save/restore across keep-alive instead.
// Captured in the route guard, not onDeactivated: by the time that fires,
// keep-alive has already swapped in the (shorter) new page, and the browser
// has clamped window.scrollY to fit it — too late to read the real value.
let savedScrollY = 0

onBeforeRouteLeave(() => {
  savedScrollY = window.scrollY
})

onActivated(() => {
  window.scrollTo(0, savedScrollY)
})
</script>

<template>
  <EventFilterBar
    v-model:search="search"
    v-model:category-ids="categoryIds"
    v-model:date-from="dateFrom"
    v-model:date-to="dateTo"
    :categories="categories"
  />

  <v-container class="py-8" style="max-width: 1440px">
    <v-row>
      <v-col v-for="event in events" :key="event.id" cols="12" sm="6" md="3">
        <EventCard :event="event" />
      </v-col>
    </v-row>

    <EmptyState v-if="!loading && events.length === 0" message="No events found." />

    <div ref="sentinel" class="d-flex align-center justify-center py-8">
      <LoadingSpinner v-if="loading" />
    </div>
  </v-container>

  <ExpiredCartDialog :model-value="expired" @close="expired = false" />
</template>
