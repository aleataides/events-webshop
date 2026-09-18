<script setup lang="ts">
import type { CartItem } from '@/types/cart'

const { item } = defineProps<{ item: CartItem }>()
const emit = defineEmits<{ 'update:qty': [qty: number]; remove: [] }>()
</script>

<template>
  <div class="d-flex align-center justify-space-between py-3">
    <div>
      <p class="text-body-1 font-weight-bold">{{ item.price.name }}</p>
      <p class="text-body-2 text-medium-emphasis">
        {{ item.price.value }} {{ item.price.currency }}
      </p>
    </div>

    <div class="d-flex align-center ga-3">
      <v-btn
        icon="mdi-minus"
        size="small"
        variant="outlined"
        @click="emit('update:qty', item.qty - 1)"
      />
      <span class="text-body-1 font-weight-bold" style="min-width: 24px; text-align: center">{{
        item.qty
      }}</span>
      <v-btn
        icon="mdi-plus"
        size="small"
        variant="outlined"
        @click="emit('update:qty', item.qty + 1)"
      />

      <span class="text-body-1 font-weight-bold ml-4" style="min-width: 72px; text-align: right"
        >{{ item.subtotal }} €</span
      >

      <v-btn icon="mdi-delete-outline" size="small" variant="text" @click="emit('remove')" />
    </div>
  </div>
</template>
