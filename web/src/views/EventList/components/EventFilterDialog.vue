<script setup lang="ts">
import type { EventCategory } from '@/types/event'

import { ref, watch } from 'vue'

const props = defineProps<{
  categories: EventCategory[]
  categoryIds: string[]
  dateFrom: string
  dateTo: string
}>()

const emit = defineEmits<{
  apply: [{ categoryIds: string[]; dateFrom: string; dateTo: string }]
}>()

const open = defineModel<boolean>({ required: true })

// Draft copies so edits only take effect on "Apply" — closing/cancelling
// the modal must not silently change the already-applied filters.
const draftCategoryIds = ref<string[]>([])
const draftDateFrom = ref('')
const draftDateTo = ref('')

watch(open, (isOpen) => {
  if (isOpen) {
    draftCategoryIds.value = [...props.categoryIds]
    draftDateFrom.value = props.dateFrom
    draftDateTo.value = props.dateTo
  }
})

function clearDraft(): void {
  draftCategoryIds.value = []
  draftDateFrom.value = ''
  draftDateTo.value = ''
}

function apply(): void {
  emit('apply', {
    categoryIds: draftCategoryIds.value,
    dateFrom: draftDateFrom.value,
    dateTo: draftDateTo.value,
  })
  open.value = false
}
</script>

<template>
  <v-dialog v-model="open" max-width="480">
    <v-card>
      <v-card-title
        class="d-flex align-center justify-space-between text-subtitle-1 font-weight-bold"
      >
        Filter events
        <v-btn icon="mdi-close" variant="text" size="small" @click="open = false" />
      </v-card-title>

      <v-card-text>
        <p class="text-caption text-medium-emphasis mb-2">Category</p>
        <div class="d-flex flex-wrap ga-2 mb-4">
          <v-checkbox
            v-for="category in categories"
            :key="category.id"
            v-model="draftCategoryIds"
            :value="category.id"
            :label="category.name"
            density="compact"
            hide-details
            class="flex-0-0"
          />
        </div>

        <v-divider class="mb-4" />

        <p class="text-caption text-medium-emphasis mb-2">Date</p>
        <div class="d-flex ga-2">
          <v-text-field v-model="draftDateFrom" type="date" label="From" />
          <v-text-field v-model="draftDateTo" type="date" label="To" />
        </div>
      </v-card-text>

      <v-card-actions class="d-flex justify-space-between px-4 pb-4">
        <v-btn variant="text" @click="clearDraft">Clear filters</v-btn>
        <v-btn variant="flat" color="primary" class="text-white" @click="apply">
          Apply filters
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
