<script setup lang="ts">
import type { Price } from '@/types/event'

import { formatMoney } from '@/lib/formatMoney'

const { price, qty, max } = defineProps<{ price: Price; qty: number; max: number }>()
const emit = defineEmits<{ 'update:qty': [qty: number] }>()
</script>

<template>
  <div class="d-flex align-center justify-space-between py-3">
    <div>
      <p class="text-body-1 font-weight-bold">{{ price.name }}</p>
      <p class="text-body-2 text-medium-emphasis">{{ formatMoney(price.value, price.currency) }}</p>
    </div>

    <div class="d-flex align-center ga-3">
      <v-btn
        icon="mdi-minus"
        size="x-small"
        variant="outlined"
        :disabled="qty <= 0"
        @click="emit('update:qty', qty - 1)"
      />
      <span class="text-body-1 font-weight-bold" style="min-width: 24px; text-align: center">{{
        qty
      }}</span>
      <v-btn
        icon="mdi-plus"
        size="x-small"
        variant="outlined"
        :disabled="qty >= max"
        @click="emit('update:qty', qty + 1)"
      />
    </div>
  </div>
</template>
