<script setup lang="ts">
import { formatMoney } from '@/lib/formatMoney'
import { RouteName } from '@/router/routeNames'
import { useAffiliateStore } from '@/stores/affiliate'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { storeToRefs } from 'pinia'

const { isLoading } = storeToRefs(useUiStore())
const { cart, itemCount } = storeToRefs(useCartStore())
const { affiliateId, affiliateName } = storeToRefs(useAffiliateStore())
</script>

<template>
  <v-app-bar flat border="b" color="surface" class="app-header">
    <v-progress-linear v-if="isLoading" indeterminate color="primary" absolute location="bottom" />

    <v-container class="d-flex align-center" style="max-width: 1440px">
      <span class="text-h6 font-weight-black">
        <router-link v-if="affiliateId !== null" :to="{ name: RouteName.EventList, params: { affiliateId } }"
          class="text-decoration-none text-high-emphasis d-flex align-center">
          <v-icon icon="mdi-ticket-confirmation-outline" class="mr-2" />
          Event Webshop
          <span v-if="affiliateName" class="text-body-2 text-medium-emphasis font-weight-regular ml-2">
            · {{ affiliateName }}
          </span>
        </router-link>
        <span v-else class="d-flex align-center">
          <v-icon icon="mdi-ticket-confirmation-outline" class="mr-2" />
          Event Webshop
        </span>
      </span>

      <v-spacer />

      <v-btn v-if="affiliateId !== null && cart && itemCount > 0" variant="outlined" rounded="pill" size="small"
        :to="{ name: RouteName.Cart, params: { affiliateId } }">
        <v-icon icon="mdi-shopping-outline" class="mr-2" />
        {{ itemCount }} items • {{ formatMoney(cart.total) }}
      </v-btn>
    </v-container>
  </v-app-bar>
</template>

<style scoped>
.app-header {
  border-block-end-color: #eeeeee !important;
}
</style>
