<script setup lang="ts">
import { getCart } from '@/api/cart'
import AppHeader from '@/components/AppHeader.vue'
import { getStoredCartId } from '@/lib/cartStorage'
import { useAffiliateStore } from '@/stores/affiliate'
import { useCartStore } from '@/stores/cart'
import { onMounted } from 'vue'

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

  const cart = await getCart(affiliateId)
  if (cart) {
    useCartStore().setCart(cart)
  }
})
</script>

<template>
  <v-app>
    <AppHeader />

    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
