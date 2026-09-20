<script setup lang="ts">
import { getCart } from '@/api/cart'
import AppFooter from '@/components/AppFooter.vue'
import AppHeader from '@/components/AppHeader.vue'
import { getApiErrorCode } from '@/lib/apiError'
import { getStoredCartId } from '@/lib/cartStorage'
import { RouteName } from '@/router/routeNames'
import { useAffiliateStore } from '@/stores/affiliate'
import { useCartStore } from '@/stores/cart'
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Router's initial navigation has already resolved by the time this
// component mounts — see main.ts's `router.isReady().then(...)`.
onMounted(async () => {
  if (getStoredCartId() === null) {
    return
  }

  const affiliateId = useAffiliateStore().affiliateId
  if (affiliateId === null) {
    return
  }

  try {
    const cart = await getCart(affiliateId)
    if (cart) {
      useCartStore().setCart(cart)
    }
  } catch (error) {
    // A stale/expired cart id must not linger forever — see the 410 loop
    // this used to cause once every retry kept reusing the dead id.
    if (getApiErrorCode(error) !== 'cart_expired') {
      throw error
    }

    useCartStore().clearCart()
  }
})
</script>

<template>
  <v-app>
    <AppHeader v-if="route.name !== RouteName.NotFound" />

    <v-main>
      <router-view />
    </v-main>

    <AppFooter v-if="route.name !== RouteName.NotFound" />
  </v-app>
</template>
