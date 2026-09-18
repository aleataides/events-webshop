<script setup lang="ts">
import { useUiStore } from '@/stores/ui'
import { storeToRefs } from 'pinia'

const { isLoading } = storeToRefs(useUiStore())
</script>

<template>
  <v-app>
    <v-app-bar flat border="b" color="surface">
      <v-progress-linear
        v-if="isLoading"
        indeterminate
        color="primary"
        absolute
        location="bottom"
      />
      <v-app-bar-title class="font-weight-black"> Event Webshop </v-app-bar-title>
    </v-app-bar>

    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
