<script setup lang="ts">
withDefaults(defineProps<{ size?: number }>(), { size: 32 })
</script>

<template>
  <div
    class="loading-spinner"
    :style="{ width: `${size}px`, height: `${size}px` }"
    aria-label="Loading"
  />
</template>

<style scoped>
.loading-spinner {
  border: 3px solid #e5e5e5;
  border-top-color: #0a0a0a;
  border-radius: 50%;
  animation: loading-spinner-spin 0.8s linear infinite;
}

@keyframes loading-spinner-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
