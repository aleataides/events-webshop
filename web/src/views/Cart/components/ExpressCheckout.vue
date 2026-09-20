<script setup lang="ts">
import { ref } from 'vue'

defineProps<{ submitting: boolean }>()
const emit = defineEmits<{ buy: [] }>()

const paymentMethod = ref('paypal')

const paymentMethods = [
  { value: 'paypal', label: 'PayPal' },
  { value: 'klarna', label: 'Klarna' },
  { value: 'bank', label: 'Pay by Bank' },
  { value: 'card', label: 'Credit card' },
]
</script>

<template>
  <v-card variant="flat" color="surface" class="pa-4">
    <p class="text-h6 font-weight-bold mb-4">Express Checkout</p>

    <v-btn block size="x-large" color="primary" class="text-white mb-4" @click="emit('buy')">
      <v-icon icon="mdi-credit-card-outline" class="mr-2" />
      PayPal Checkout
    </v-btn>

    <div class="d-flex align-center ga-3 mb-4">
      <v-divider />
      <span class="text-caption text-medium-emphasis">Or</span>
      <v-divider />
    </div>

    <p class="text-subtitle-1 font-weight-bold mb-2">Payment method</p>

    <v-radio-group v-model="paymentMethod" hide-details class="mb-2">
      <v-card
        v-for="method in paymentMethods"
        :key="method.value"
        variant="flat"
        color="surface"
        :class="[
          'pa-3 mb-2 d-flex align-center payment-method',
          { 'payment-method--selected': paymentMethod === method.value },
        ]"
        @click="paymentMethod = method.value"
      >
        <v-radio :value="method.value" :label="method.label" density="compact" hide-details />
      </v-card>
    </v-radio-group>

    <p class="text-caption text-medium-emphasis mb-4">
      After clicking on Buy, you will be forwarded to the selected payment provider.
    </p>

    <v-btn
      block
      size="x-large"
      color="primary"
      class="text-white"
      :loading="submitting"
      @click="emit('buy')"
    >
      Buy
    </v-btn>
  </v-card>
</template>

<style scoped>
.payment-method {
  cursor: pointer;
}

.payment-method--selected {
  border-color: rgb(var(--v-theme-primary)) !important;
}
</style>
