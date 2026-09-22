<script setup lang="ts">
import type { Venue } from '@/types/event'

import { defineAsyncComponent, ref } from 'vue'

const VenueMap = defineAsyncComponent(() => import('@/components/VenueMap.vue'))

defineProps<{ venue: Venue }>()

const showMap = ref(false)
</script>

<template>
  <div class="d-flex align-start ga-2 mb-2">
    <v-icon icon="mdi-map-marker-outline" />
    <p class="text-body-1 font-weight-bold">
      {{ venue.name }}, {{ venue.street }}, {{ venue.zipCode }} {{ venue.city }}
    </p>
  </div>

  <button
    type="button"
    class="d-flex align-center ga-1 text-body-2 font-weight-medium mb-4 show-map-link"
    @click="showMap = !showMap"
  >
    <span class="show-map-link__text">Show Map</span>
    <v-icon :icon="showMap ? 'mdi-chevron-up' : 'mdi-chevron-down'" size="18" />
  </button>

  <VenueMap
    v-if="showMap"
    :latitude="venue.geo.latitude"
    :longitude="venue.geo.longitude"
    :name="venue.name"
    class="mb-4"
  />
</template>

<style scoped>
.show-map-link {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}

.show-map-link:hover .show-map-link__text {
  text-decoration: underline;
}
</style>
