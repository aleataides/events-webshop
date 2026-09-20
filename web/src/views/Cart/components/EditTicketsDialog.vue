<script setup lang="ts">
import type { CartItem } from '@/types/cart'

import { addCartItem, removeCartItem, updateCartItem } from '@/api/cart'
import EventImage from '@/components/EventImage.vue'
import { useEventDetail } from '@/composables/useEventDetail'
import { getApiErrorCode } from '@/lib/apiError'
import { addWithExpiryRetry } from '@/lib/cartExpiry'
import { formatEventDateTime } from '@/lib/formatDate'
import { useCartStore } from '@/stores/cart'
import TicketSelector from '@/views/EventDetail/components/TicketSelector.vue'
import { computed, ref, watch } from 'vue'

const { affiliateId, eventId, items } = defineProps<{
  affiliateId: string
  eventId: string
  items: CartItem[]
}>()

const modelValue = defineModel<boolean>({ required: true })

const { event, loading } = useEventDetail(affiliateId, eventId)
const cartStore = useCartStore()
const submitting = ref(false)
const selectedQty = ref<Record<string, number>>({})

watch(
  () => modelValue.value,
  (open) => {
    if (open) {
      selectedQty.value = Object.fromEntries(items.map((item) => [item.price.id, item.qty]))
    }
  },
)

const totalQty = computed(() => Object.values(selectedQty.value).reduce((sum, qty) => sum + qty, 0))

const totalValue = computed(() => {
  const prices = event.value?.areas.flatMap((area) => area.prices) ?? []

  return prices.reduce(
    (sum, price) => sum + Number(price.value) * (selectedQty.value[price.id] ?? 0),
    0,
  )
})

async function applyChanges(): Promise<void> {
  submitting.value = true
  try {
    const prices = event.value?.areas.flatMap((area) => area.prices) ?? []
    const currentByPriceId = new Map(items.map((item) => [item.price.id, item]))

    for (const price of prices) {
      const newQty = selectedQty.value[price.id] ?? 0
      const current = currentByPriceId.get(price.id)

      if (current) {
        if (newQty === 0) {
          cartStore.setCart(await removeCartItem(affiliateId, current.id))
        } else if (newQty !== current.qty) {
          cartStore.setCart(await updateCartItem(affiliateId, current.id, newQty))
        }
      } else if (newQty > 0) {
        cartStore.setCart(
          await addWithExpiryRetry(() => addCartItem(affiliateId, price.id, newQty)),
        )
      }
    }

    modelValue.value = false
  } catch (error) {
    // Expired mid-edit — a stale reservation can't be updated/removed, so
    // just drop it locally; CartPage's own expiry check picks it up.
    if (getApiErrorCode(error) !== 'cart_expired') {
      throw error
    }

    cartStore.clearCart()
    modelValue.value = false
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <v-dialog v-model="modelValue" max-width="480">
    <v-card class="pa-4">
      <div class="d-flex align-center justify-space-between mb-4">
        <p class="text-h6 font-weight-bold">Change tickets</p>
        <v-btn icon="mdi-close" variant="text" size="small" @click="modelValue = false" />
      </div>

      <div v-if="loading" class="d-flex justify-center py-8">
        <v-progress-circular indeterminate color="primary" />
      </div>

      <template v-else-if="event">
        <div class="d-flex align-center ga-3 mb-3">
          <EventImage
            :image-id="event.image.id"
            :aspect-ratio="1"
            cover
            width="40"
            height="40"
            rounded="lg"
            class="flex-0-0"
          />
          <div>
            <p class="text-body-2 font-weight-bold">{{ event.title }}</p>
            <p class="text-caption text-medium-emphasis">
              {{ event.venue.name }}, {{ event.venue.city }} ·
              {{ formatEventDateTime(event.start) }}
            </p>
          </div>
        </div>

        <v-divider class="mb-4" />

        <TicketSelector
          :event="event"
          :selected-qty="selectedQty"
          :total-qty="totalQty"
          :total-value="totalValue"
          :submitting="submitting"
          compact
          @update:qty="(priceId, qty) => (selectedQty[priceId] = qty)"
          @select="applyChanges"
        />
      </template>
    </v-card>
  </v-dialog>
</template>
