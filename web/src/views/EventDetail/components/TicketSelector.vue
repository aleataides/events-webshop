<script setup lang="ts">
import type { EventDetail } from '@/types/event'

import { formatMoney } from '@/lib/formatMoney'
import { computed } from 'vue'

import PriceStepper from './PriceStepper.vue'

const props = defineProps<{
  event: EventDetail
  selectedQty: Record<string, number>
  totalQty: number
  totalValue: number
  submitting: boolean
  compact?: boolean
}>()

const emit = defineEmits<{
  'update:qty': [priceId: string, qty: number]
  select: []
}>()

const isSoldOut = computed(() => props.event.areas.every((area) => area.available <= 0))

const ctaLabel = computed(() => {
  if (isSoldOut.value) {
    return 'Sold out'
  }

  if (props.totalQty === 0) {
    return 'Select tickets'
  }

  const ticketWord = props.totalQty === 1 ? 'ticket' : 'tickets'
  const currency = props.event.areas[0]?.prices[0]?.currency

  return `${props.totalQty} ${ticketWord} — ${formatMoney(props.totalValue.toString(), currency)}`
})
</script>

<template>
  <v-card v-if="!compact" variant="flat" color="surface" class="pa-4 mb-4">
    <p class="text-subtitle-1 font-weight-bold mb-2">Price and Access information</p>
    <p class="text-body-2 text-medium-emphasis">
      Unsere Ticketermäßigung gilt für SchülerInnen und StudentInnen bis 30 Jahre gegen Vorlage
      eines entsprechenden gültigen Ausweises an der Abendkasse.
    </p>
    <p v-if="event.priceInfo" class="text-body-2 text-medium-emphasis mt-2">
      {{ event.priceInfo }}
    </p>
  </v-card>

  <v-card variant="flat" color="surface" class="pa-4 mb-4">
    <template v-for="(area, index) in event.areas" :key="area.id">
      <v-divider v-if="index > 0" class="my-2" />
      <p class="text-subtitle-1 font-weight-bold mb-2">{{ area.name }}</p>
      <p v-if="area.available <= 0" class="text-body-2 text-medium-emphasis">Sold out</p>
      <template v-else>
        <template v-for="(price, priceIndex) in area.prices" :key="price.id">
          <v-divider v-if="priceIndex > 0" />
          <PriceStepper
            :price="price"
            :qty="selectedQty[price.id] ?? 0"
            :max="area.available"
            @update:qty="(qty) => emit('update:qty', price.id, qty)"
          />
        </template>
      </template>
    </template>
  </v-card>

  <v-btn
    block
    :size="compact ? 'default' : 'x-large'"
    color="primary"
    class="text-white"
    :disabled="totalQty === 0"
    :loading="submitting"
    @click="emit('select')"
  >
    {{ ctaLabel }}
  </v-btn>
  <p v-if="!compact" class="text-caption text-medium-emphasis text-center mt-2">incl. VAT</p>
</template>
