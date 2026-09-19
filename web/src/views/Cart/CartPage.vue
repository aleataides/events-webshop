<script setup lang="ts">
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { useCart } from '@/composables/useCart'
import { useCountdown } from '@/composables/useCountdown'
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import CartItemRow from './components/CartItemRow.vue'
import ExpiredCartDialog from './components/ExpiredCartDialog.vue'
import OrderConfirmation from './components/OrderConfirmation.vue'

const route = useRoute()
const affiliateId = route.params.affiliateId as string

const { cart, loading, expired, order, updateQty, removeItem, buy, expireLocally } =
  useCart(affiliateId)
const submitting = ref(false)

const { remainingSeconds, isExpired, formatted } = useCountdown(() => cart.value?.expiresAt ?? null)

// The BE re-checks expiry on every request regardless — this just avoids
// waiting for the next mutation to surface an already-expired cart.
watch(isExpired, (value) => {
  if (value) {
    expireLocally()
  }
})

const showExpiredDialog = computed(() => expired.value)

function dismissExpired(): void {
  expired.value = false
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
  <v-container v-if="loading" class="d-flex justify-center py-12">
    <LoadingSpinner />
  </v-container>

  <v-container v-else-if="order" class="py-8" style="max-width: 720px">
    <OrderConfirmation :order="order" />
  </v-container>

  <v-container v-else class="py-8" style="max-width: 720px">
    <h1 class="text-h5 font-weight-bold mb-2">Shopping cart</h1>

    <template v-if="cart">
      <p class="text-body-2 text-medium-emphasis mb-4">
        <v-icon icon="mdi-clock-outline" size="16" />
        {{ formatted }} minutes reserved for you
      </p>
      <v-progress-linear
        :model-value="(remainingSeconds / (15 * 60)) * 100"
        color="primary"
        class="mb-6"
      />

      <v-card variant="outlined" class="pa-4 mb-4">
        <CartItemRow
          v-for="item in cart.items"
          :key="item.id"
          :item="item"
          @update:qty="(qty) => updateQty(item.id, qty)"
          @remove="removeItem(item.id)"
        />
      </v-card>

      <div class="d-flex justify-space-between text-h6 font-weight-bold mb-2">
        <span>Grand total</span>
        <span>{{ cart.total }} €</span>
      </div>
      <p class="text-caption text-medium-emphasis mb-6">incl. VAT</p>

      <v-btn
        block
        size="x-large"
        color="primary"
        class="text-white"
        :loading="submitting"
        @click="handleBuy"
      >
        Buy
      </v-btn>
    </template>

    <p v-else class="text-body-2 text-medium-emphasis">Your cart is empty.</p>
  </v-container>

  <ExpiredCartDialog :model-value="showExpiredDialog" @close="dismissExpired" />
</template>
