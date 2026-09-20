<script setup lang="ts">
import type { Order } from '@/types/order'

import { formatMoney } from '@/lib/formatMoney'

defineProps<{ order: Order }>()
</script>

<template>
  <div class="text-center py-8">
    <v-icon icon="mdi-check-circle-outline" size="64" color="primary" class="mb-4" />
    <h1 class="text-h5 font-weight-bold mb-2">Order confirmed</h1>
    <p class="text-body-2 text-medium-emphasis mb-6">Order {{ order.id }}</p>

    <v-card variant="outlined" class="pa-4 mx-auto text-left" max-width="480">
      <div v-for="item in order.items" :key="item.id" class="d-flex justify-space-between py-1">
        <span>{{ item.price.name }} × {{ item.qty }}</span>
        <span>{{ formatMoney(item.subtotal, item.price.currency) }}</span>
      </div>
      <v-divider class="my-2" />
      <div class="d-flex justify-space-between font-weight-bold">
        <span>Total</span>
        <span>{{ formatMoney(order.total) }}</span>
      </div>
    </v-card>
  </div>
</template>
