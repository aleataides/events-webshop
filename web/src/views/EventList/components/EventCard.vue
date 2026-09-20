<script setup lang="ts">
import type { EventListItem } from '@/types/event'

import EventImage from '@/components/EventImage.vue'
import { formatEventDateTime } from '@/lib/formatDate'
import { formatMoney } from '@/lib/formatMoney'
import { RouteName } from '@/router/routeNames'
import { useRoute } from 'vue-router'

const { event } = defineProps<{ event: EventListItem }>()
const route = useRoute()
</script>

<template>
  <v-card
    variant="flat"
    color="surface"
    :to="{
      name: RouteName.EventDetail,
      params: { affiliateId: route.params.affiliateId, eventId: event.id },
    }"
  >
    <EventImage :image-id="event.image.id" :aspect-ratio="16 / 9" cover />

    <v-card-text>
      <p class="font-mono text-caption text-disabled mb-2">Image: {{ event.image.copyright }}</p>
      <h3 class="text-subtitle-1 font-weight-bold mb-2 event-title" :title="event.title">
        {{ event.title }}
      </h3>

      <v-divider class="mb-2" />

      <p class="text-caption text-medium-emphasis">
        {{ event.venue.name }}, {{ event.venue.city }}
      </p>
      <p class="text-caption text-medium-emphasis">{{ formatEventDateTime(event.start) }}</p>
    </v-card-text>

    <v-card-actions class="px-4 pb-4">
      <v-btn block variant="flat" color="primary" class="text-white">
        <span v-if="event.soldout">Sold out</span>
        <span v-else-if="event.minPrice"
          >from {{ formatMoney(event.minPrice, event.currency ?? undefined) }} →</span
        >
        <span v-else>Not available</span>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<style scoped>
/* Card is a router-link — keep it clickable without Vuetify's hover tint. */
:deep(.v-card__overlay) {
  opacity: 0 !important;
}

.event-title {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  overflow: hidden;
  min-height: 2lh;
}
</style>
