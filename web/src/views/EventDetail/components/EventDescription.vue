<script setup lang="ts">
import DOMPurify from 'dompurify'
import { computed, ref } from 'vue'

const { description } = defineProps<{ description: string }>()

const showFull = ref(false)
const safeDescription = computed(() => DOMPurify.sanitize(description))
</script>

<template>
  <div class="description-wrapper mb-2" :class="{ 'description-wrapper--clamped': !showFull }">
    <!-- eslint-disable-next-line vue/no-v-html -- sanitized via DOMPurify above -->
    <div class="text-body-2" v-html="safeDescription" />
  </div>
  <v-btn variant="text" size="small" @click="showFull = !showFull">
    {{ showFull ? 'Show less' : 'Show more' }}
  </v-btn>
</template>

<style scoped>
.description-wrapper--clamped {
  position: relative;
  max-height: 6.4em;
  overflow: hidden;
}

.description-wrapper--clamped::after {
  content: '';
  position: absolute;
  inset: auto 0 0 0;
  height: 2.4em;
  background: linear-gradient(to bottom, transparent, rgb(var(--v-theme-surface)));
}
</style>
