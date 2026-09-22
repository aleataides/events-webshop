<script setup lang="ts">
withDefaults(defineProps<{ icon?: string; message: string }>(), {
  icon: 'mdi-calendar-blank-outline',
})
</script>

<template>
  <div class="d-flex flex-column align-center justify-center text-center" style="min-height: 60vh">
    <v-icon :icon="icon" size="48" class="mb-4 text-medium-emphasis" />
    <p class="text-body-1 text-medium-emphasis">{{ message }}</p>
  </div>
</template>
