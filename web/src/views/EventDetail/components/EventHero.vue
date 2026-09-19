<script setup lang="ts">
import type { EventDetail } from '@/types/event'

import { buildImageUrl } from '@/lib/imageUrl'

defineProps<{ event: EventDetail }>()
</script>

<template>
  <v-img :src="buildImageUrl(event.image.id)" aspect-ratio="16/9" cover class="mb-2" />
  <p class="font-mono text-caption text-disabled mb-4">Image: {{ event.image.copyright }}</p>

  <h1 class="text-h4 font-weight-bold mb-1">{{ event.title }}</h1>
  <p class="text-subtitle-1 text-medium-emphasis mb-4">{{ event.subtitle }}</p>
</template>
