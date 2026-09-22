<script setup lang="ts">
import type { CartItem } from '@/types/cart'

import EventImage from '@/components/EventImage.vue'
import { formatEventDateTime } from '@/lib/formatDate'
import { formatMoney } from '@/lib/formatMoney'
import { computed, ref } from 'vue'

import EditTicketsDialog from './EditTicketsDialog.vue'

const { affiliateId, items } = defineProps<{ affiliateId: string; items: CartItem[] }>()
const emit = defineEmits<{ removeEvent: [] }>()

const event = computed(() => items[0]!.event)
const editing = ref(false)
</script>

<template>
  <v-card variant="flat" color="surface" class="pa-3 mb-3">
    <div
      class="mb-2"
      style="display: grid; grid-template-columns: auto 1fr auto; align-items: stretch; gap: 12px"
    >
      <div style="aspect-ratio: 4 / 3">
        <EventImage :image-id="event.image.id" cover rounded="lg" height="100%" width="100%" />
      </div>
      <div class="d-flex flex-column justify-center">
        <p class="text-body-2 font-weight-bold">{{ event.title }}</p>
        <p class="text-caption text-medium-emphasis">
          {{ event.venue.name }}, {{ event.venue.city }}
        </p>
        <p class="text-caption text-medium-emphasis">
          {{ formatEventDateTime(event.start, ' | ') }}
        </p>
      </div>
      <v-btn
        icon="mdi-delete-outline"
        variant="text"
        size="small"
        style="align-self: center"
        @click="emit('removeEvent')"
      />
    </div>

    <v-divider class="mb-1" />

    <div class="d-flex align-center justify-space-between">
      <p class="text-body-2 font-weight-bold">Your tickets ({{ items.length }})</p>
      <v-btn icon="mdi-pencil-outline" variant="text" size="small" @click="editing = true" />
    </div>

    <div v-for="item in items" :key="item.id" class="d-flex align-center justify-space-between">
      <p class="text-body-2 text-medium-emphasis">
        <v-icon icon="mdi-ticket-confirmation-outline" size="16" class="mr-1" />
        {{ item.area.name }} • {{ item.price.name }} ({{ item.qty }})
      </p>
      <p class="text-body-2 font-weight-bold">
        {{ formatMoney(item.subtotal, item.price.currency) }}
      </p>
    </div>
  </v-card>

  <EditTicketsDialog
    v-model="editing"
    :affiliate-id="affiliateId"
    :event-id="event.id"
    :items="items"
  />
</template>
