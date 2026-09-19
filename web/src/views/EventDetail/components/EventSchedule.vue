<script setup lang="ts">
import type { EventDetail } from '@/types/event'

import { formatEventDateTime } from '@/lib/formatDate'

defineProps<{ event: EventDetail }>()
</script>

<template>
  <div class="d-flex align-start ga-2 mb-6">
    <v-icon icon="mdi-calendar-outline" />
    <div>
      <p class="text-body-1 font-weight-bold">{{ formatEventDateTime(event.start) }}</p>
      <p v-if="event.doorsOpen" class="text-body-2 text-medium-emphasis">
        Doors open on {{ formatEventDateTime(event.doorsOpen) }}
      </p>
      <p class="text-body-2 text-medium-emphasis">To: {{ formatEventDateTime(event.end) }}</p>
    </div>
  </div>
</template>
