<script setup lang="ts">
import { buildImageUrl } from '@/lib/imageUrl'

const { imageId } = defineProps<{ imageId: string }>()
</script>

<template>
  <v-img :src="buildImageUrl(imageId)">
    <template #error>
      <div class="d-flex align-center justify-center fill-height bg-surface-variant">
        <v-icon icon="mdi-image-off-outline" />
      </div>
    </template>
  </v-img>
</template>
