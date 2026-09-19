<script setup lang="ts">
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'

const { isLoading } = storeToRefs(useUiStore())
const { cart, itemCount } = storeToRefs(useCartStore())
const route = useRoute()
</script>

<template>
  <v-app-bar flat border="b" color="surface">
    <v-progress-linear v-if="isLoading" indeterminate color="primary" absolute location="bottom" />

    <v-app-bar-title class="font-weight-black">Event Webshop</v-app-bar-title>

    <v-spacer />

    <v-btn
      variant="text"
      :to="{ name: 'cart', params: { affiliateId: route.params.affiliateId } }"
      class="mr-4"
    >
      <v-icon icon="mdi-shopping-outline" class="mr-2" />
      <span v-if="cart">{{ itemCount }} items • €{{ cart.total }}</span>
      <span v-else>Cart</span>
    </v-btn>
  </v-app-bar>
</template>
