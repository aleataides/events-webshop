<script setup lang="ts">
import type { EventDetail } from '@/types/event'

import PriceStepper from './PriceStepper.vue'

defineProps<{
  event: EventDetail
  selectedQty: Record<string, number>
  totalQty: number
  totalValue: number
  submitting: boolean
}>()

const emit = defineEmits<{
  'update:qty': [priceId: string, qty: number]
  select: []
}>()
</script>

<template>
  <v-card v-if="event.priceInfo" variant="outlined" class="pa-4 mb-4">
    <p class="text-subtitle-1 font-weight-bold mb-2">Price and Access information</p>
    <p class="text-body-2 text-medium-emphasis">{{ event.priceInfo }}</p>
  </v-card>

  <v-card v-for="area in event.areas" :key="area.id" variant="outlined" class="pa-4 mb-4">
    <p class="text-subtitle-1 font-weight-bold mb-2">{{ area.name }}</p>
    <p v-if="area.available <= 0" class="text-body-2 text-medium-emphasis">Sold out</p>
    <template v-else>
      <PriceStepper
        v-for="price in area.prices"
        :key="price.id"
        :price="price"
        :qty="selectedQty[price.id] ?? 0"
        :max="area.available"
        @update:qty="(qty) => emit('update:qty', price.id, qty)"
      />
    </template>
  </v-card>

  <v-btn
    block
    size="x-large"
    color="primary"
    class="text-white"
    :disabled="totalQty === 0"
    :loading="submitting"
    @click="emit('select')"
  >
    Select {{ totalQty }} ticket{{ totalQty === 1 ? '' : 's' }} — {{ totalValue.toFixed(2) }} €
  </v-btn>
  <p class="text-caption text-medium-emphasis text-center mt-2">incl. VAT</p>
</template>
