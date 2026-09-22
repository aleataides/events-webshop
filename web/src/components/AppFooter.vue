<script setup lang="ts">
const year = new Date().getFullYear()
</script>

<template>
  <v-footer border="t" color="surface" height="72" class="pa-0 flex-grow-0">
    <v-container class="d-flex align-center" style="max-width: 1440px">
      <div class="d-flex align-center">
        <v-icon icon="mdi-ticket-confirmation-outline" size="18" class="mr-2" />
        <span class="font-mono text-caption text-medium-emphasis">
          © {{ year }} Event Webshop. All rights reserved.
        </span>
      </div>

      <v-spacer />

      <div class="d-flex ga-4">
        <a href="#" class="text-body-2 text-medium-emphasis text-decoration-none">Terms</a>
        <a href="#" class="text-body-2 text-medium-emphasis text-decoration-none">Privacy</a>
        <a href="#" class="text-body-2 text-medium-emphasis text-decoration-none">Support</a>
      </div>
    </v-container>
  </v-footer>
</template>
