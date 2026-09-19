<script setup lang="ts">
import type { Venue } from '@/types/event'

import VenueMap from '@/components/VenueMap.vue'

defineProps<{ venue: Venue }>()
</script>

<template>
  <div class="d-flex align-start ga-2 mb-2">
    <v-icon icon="mdi-map-marker-outline" />
    <p class="text-body-1 font-weight-bold">
      {{ venue.name }}, {{ venue.street }}, {{ venue.zipCode }} {{ venue.city }}
    </p>
  </div>

  <v-expansion-panels variant="accordion" class="mb-4">
    <v-expansion-panel title="Show map">
      <template #text>
        <VenueMap
          :latitude="venue.geo.latitude"
          :longitude="venue.geo.longitude"
          :name="venue.name"
        />
      </template>
    </v-expansion-panel>
  </v-expansion-panels>
</template>
