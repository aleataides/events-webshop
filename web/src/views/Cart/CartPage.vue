<script setup lang="ts">
import type { CartItem } from '@/types/cart'

import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { useCart } from '@/composables/useCart'
import { useCountdown } from '@/composables/useCountdown'
import { formatMoney } from '@/lib/formatMoney'
import { RouteName } from '@/router/routeNames'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import CartEventSection from './components/CartEventSection.vue'
import ExpiredCartDialog from './components/ExpiredCartDialog.vue'
import ExpressCheckout from './components/ExpressCheckout.vue'
import OrderConfirmation from './components/OrderConfirmation.vue'

const route = useRoute()
const affiliateId = route.params.affiliateId as string

const { cart, loading, expired, order, removeItem, buy } = useCart(affiliateId)
const submitting = ref(false)
const orderDetailsOpen = ref(true)

// Own display-only countdown for the progress bar — expiry itself is
// already watched inside useCart, shared with every other page.
const CART_EXPIRY_SECONDS = Number(import.meta.env.VITE_CART_EXPIRY_MINUTES ?? 15) * 60
const { formatted, progressPercent } = useCountdown(
  () => cart.value?.expiresAt ?? null,
  CART_EXPIRY_SECONDS,
)

const eventGroups = computed(() => {
  const groups: { eventId: string; items: CartItem[] }[] = []

  for (const item of cart.value?.items ?? []) {
    const group = groups.find((g) => g.eventId === item.event.id)
    if (group) {
      group.items.push(item)
    } else {
      groups.push({ eventId: item.event.id, items: [item] })
    }
  }

  return groups
})

const showExpiredDialog = computed(() => expired.value)

function dismissExpired(): void {
  expired.value = false
}

async function removeEvent(items: CartItem[]): Promise<void> {
  for (const item of items) {
    await removeItem(item.id)
  }
}

async function handleBuy(): Promise<void> {
  submitting.value = true
  try {
    await buy()
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <v-container v-if="loading" class="d-flex align-center justify-center" style="min-height: 60vh">
    <LoadingSpinner />
  </v-container>

  <v-container v-else-if="order" class="py-8" style="max-width: 720px">
    <OrderConfirmation :order="order" />
  </v-container>

  <v-container v-else class="py-8" style="max-width: 1440px">
    <router-link
      v-if="cart && cart.items.length > 0"
      :to="{ name: RouteName.EventList, params: { affiliateId } }"
      class="d-flex align-center ga-1 text-body-2 font-weight-medium text-decoration-none text-high-emphasis mb-2 back-link"
      style="width: fit-content"
    >
      <v-icon icon="mdi-arrow-left" size="18" />
      <span class="back-link__text">Continue shopping</span>
    </router-link>

    <h1 class="text-h5 font-weight-bold mb-2">Shopping cart</h1>

    <template v-if="cart && cart.items.length > 0">
      <v-row>
        <v-col cols="12" md="7">
          <p class="text-body-2 text-medium-emphasis mb-4">
            <v-icon icon="mdi-clock-outline" size="16" />
            {{ formatted }} minutes reserved for you
          </p>
          <v-progress-linear :model-value="progressPercent" color="primary" class="mb-6" />

          <button
            type="button"
            class="d-flex align-center justify-space-between mb-4 order-details-toggle"
            @click="orderDetailsOpen = !orderDetailsOpen"
          >
            <span class="d-flex align-center ga-2 text-subtitle-1 font-weight-bold">
              <v-icon :icon="orderDetailsOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" />
              Order details
            </span>
            <span class="text-subtitle-1 font-weight-bold">{{ formatMoney(cart.total) }}</span>
          </button>

          <template v-if="orderDetailsOpen">
            <CartEventSection
              v-for="group in eventGroups"
              :key="group.eventId"
              :affiliate-id="affiliateId"
              :items="group.items"
              @remove-event="removeEvent(group.items)"
            />

            <div class="d-flex justify-space-between text-h6 font-weight-bold mb-2">
              <span>Grand total</span>
              <span>{{ formatMoney(cart.total) }}</span>
            </div>
            <p class="text-caption text-medium-emphasis mb-6">incl. VAT</p>

            <v-card variant="flat" color="surface-variant" class="pa-4 mb-4">
              <p class="text-body-2 text-medium-emphasis">
                <v-icon icon="mdi-information-outline" size="18" class="mr-1" />
                The tickets will be available for download immediately after sending the payment
                details and will also be sent to you by e-mail.
              </p>
            </v-card>
          </template>

          <v-btn variant="outlined" :to="{ name: RouteName.EventList, params: { affiliateId } }">
            Continue shopping
          </v-btn>
        </v-col>

        <v-col cols="12" md="5">
          <ExpressCheckout :submitting="submitting" @buy="handleBuy" />
        </v-col>
      </v-row>
    </template>

    <div
      v-else
      class="d-flex flex-column align-center justify-center text-center"
      style="min-height: 60vh"
    >
      <v-icon icon="mdi-cart-outline" size="48" class="text-medium-emphasis mb-4" />
      <p class="text-h6 font-weight-bold mb-2">Your cart is empty</p>
      <p class="text-body-2 text-medium-emphasis mb-6">
        Browse our events and add tickets to get started.
      </p>
      <v-btn
        color="primary"
        class="text-white"
        :to="{ name: RouteName.EventList, params: { affiliateId } }"
      >
        Browse events
      </v-btn>
    </div>
  </v-container>

  <ExpiredCartDialog :model-value="showExpiredDialog" @close="dismissExpired" />
</template>

<style scoped>
.back-link:hover .back-link__text {
  text-decoration: underline !important;
}

.order-details-toggle {
  width: 100%;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}
</style>
