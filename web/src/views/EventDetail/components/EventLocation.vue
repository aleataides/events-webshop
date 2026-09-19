<script setup lang="ts">
import type { Venue } from '@/types/event'

import VenueMap from '@/components/VenueMap.vue'
import { ref } from 'vue'

defineProps<{ venue: Venue }>()

const showMap = ref(false)
</script>

<template>
  <div class="d-flex align-start ga-2 mb-2">
    <v-icon icon="mdi-map-marker-outline" />
    <div>
      <p class="text-body-1 font-weight-bold">
        {{ venue.name }}, {{ venue.street }}, {{ venue.zipCode }} {{ venue.city }}
      </p>
      <v-btn variant="text" size="small" class="pl-0" @click="showMap = !showMap">
        {{ showMap ? 'Hide map' : 'Show map' }}
      </v-btn>
    </div>
  </div>
  <VenueMap
    v-if="showMap"
    :latitude="venue.geo.latitude"
    :longitude="venue.geo.longitude"
    :name="venue.name"
    class="mb-4"
  />
</template>
