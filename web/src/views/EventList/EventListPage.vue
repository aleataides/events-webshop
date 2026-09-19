<script setup lang="ts">
import type { EventCategory, EventListItem } from '@/types/event'

import { listCategories } from '@/api/categories'
import { listEvents } from '@/api/events'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue'
import { useRoute } from 'vue-router'

import EventCard from './components/EventCard.vue'

const route = useRoute()
const affiliateId = route.params.affiliateId as string

const search = ref('')
const categoryId = ref<string | null>(null)
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
      category: categoryId.value ?? undefined,
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

const hasDateFilter = computed(() => dateFrom.value !== '' || dateTo.value !== '')
const activeFilterCount = computed(
  () => Number(categoryId.value !== null) + Number(hasDateFilter.value),
)

function clearFilters(): void {
  categoryId.value = null
  dateFrom.value = ''
  dateTo.value = ''
}

watch([categoryId, dateFrom, dateTo], () => fetchPage(true))

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
</script>

<template>
  <v-container class="py-8" style="max-width: 1440px">
    <div class="d-flex align-center ga-4 flex-wrap mb-8">
      <v-menu :close-on-content-click="false">
        <template #activator="{ props: menuProps }">
          <v-badge
            :content="activeFilterCount"
            :model-value="activeFilterCount > 0"
            color="primary"
            floating
          >
            <v-btn variant="outlined" prepend-icon="mdi-tune-variant" v-bind="menuProps"
              >Filter</v-btn
            >
          </v-badge>
        </template>
        <v-card min-width="280" class="pa-4">
          <v-select
            v-model="categoryId"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Category"
            clearable
            class="mb-2"
          />
          <v-text-field v-model="dateFrom" type="date" label="From" class="mb-2" />
          <v-text-field v-model="dateTo" type="date" label="To" class="mb-2" />
          <v-btn
            v-if="activeFilterCount > 0"
            variant="text"
            size="small"
            block
            @click="clearFilters"
          >
            Clear filters
          </v-btn>
        </v-card>
      </v-menu>

      <v-spacer />

      <v-text-field
        v-model="search"
        label="Search events"
        prepend-inner-icon="mdi-magnify"
        style="max-width: 320px"
        density="comfortable"
      />
    </div>

    <v-row>
      <v-col v-for="event in events" :key="event.id" cols="12" sm="6" md="3">
        <EventCard :event="event" />
      </v-col>
    </v-row>

    <p v-if="!loading && events.length === 0" class="text-body-2 text-center py-8">
      No events found.
    </p>

    <div ref="sentinel" class="d-flex justify-center py-8">
      <LoadingSpinner v-if="loading" />
    </div>
  </v-container>
</template>
