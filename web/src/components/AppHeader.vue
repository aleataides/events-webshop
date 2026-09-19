<script setup lang="ts">
import { RouteName } from '@/router/routeNames'
import { useAffiliateStore } from '@/stores/affiliate'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { storeToRefs } from 'pinia'

const { isLoading } = storeToRefs(useUiStore())
const { cart, itemCount } = storeToRefs(useCartStore())
const { affiliateId } = storeToRefs(useAffiliateStore())
</script>

<template>
  <v-app-bar flat border="b" color="surface">
    <v-progress-linear v-if="isLoading" indeterminate color="primary" absolute location="bottom" />

    <v-app-bar-title class="font-weight-black">
      <router-link
        v-if="affiliateId !== null"
        :to="{ name: RouteName.EventList, params: { affiliateId } }"
        class="text-decoration-none text-high-emphasis"
      >
        Event Webshop
      </router-link>
      <span v-else>Event Webshop</span>
    </v-app-bar-title>

    <v-spacer />

    <!-- No affiliateId (e.g. root "/", 404) — nothing to link the cart to yet. -->
    <v-btn
      variant="text"
      :disabled="affiliateId === null"
      :to="affiliateId === null ? undefined : { name: RouteName.Cart, params: { affiliateId } }"
      class="mr-4"
    >
      <v-icon icon="mdi-shopping-outline" class="mr-2" />
      <span v-if="cart">{{ itemCount }} items • €{{ cart.total }}</span>
      <span v-else>Cart</span>
    </v-btn>
  </v-app-bar>
</template>
