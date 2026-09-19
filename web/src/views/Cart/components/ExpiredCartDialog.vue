<script setup lang="ts">
defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ close: [] }>()
</script>

<template>
  <v-dialog :model-value="modelValue" max-width="400" persistent>
    <v-card class="pa-4">
      <v-card-title>Your cart expired</v-card-title>
      <v-card-text>
        The 15-minute reservation window ran out and your tickets were released back into stock.
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn color="primary" @click="emit('close')">Continue shopping</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
