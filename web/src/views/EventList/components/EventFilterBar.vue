<script setup lang="ts">
import type { EventCategory } from '@/types/event'

import { computed, ref } from 'vue'

import EventFilterDialog from './EventFilterDialog.vue'

defineProps<{ categories: EventCategory[] }>()

const search = defineModel<string>('search', { required: true })
const categoryIds = defineModel<string[]>('categoryIds', { required: true })
const dateFrom = defineModel<string>('dateFrom', { required: true })
const dateTo = defineModel<string>('dateTo', { required: true })

const hasDateFilter = computed(() => dateFrom.value !== '' || dateTo.value !== '')
const activeFilterCount = computed(() => categoryIds.value.length + Number(hasDateFilter.value))

function clearFilters(): void {
  categoryIds.value = []
  dateFrom.value = ''
  dateTo.value = ''
}

const dialogOpen = ref(false)

function applyFilters(filters: { categoryIds: string[]; dateFrom: string; dateTo: string }): void {
  categoryIds.value = filters.categoryIds
  dateFrom.value = filters.dateFrom
  dateTo.value = filters.dateTo
}
</script>

<template>
  <div class="bg-surface border-b">
    <v-container class="d-flex align-center ga-2 flex-wrap py-4" style="max-width: 1440px">
      <v-badge
        :content="activeFilterCount"
        :model-value="activeFilterCount > 0"
        color="primary"
        floating
        offset-x="6"
        offset-y="6"
      >
        <v-btn
          variant="outlined"
          class="filter-btn"
          prepend-icon="mdi-tune-variant"
          @click="dialogOpen = true"
        >
          Filter
        </v-btn>
      </v-badge>

      <v-btn v-if="activeFilterCount > 0" variant="text" size="small" @click="clearFilters">
        Clear filters
      </v-btn>

      <v-spacer />

      <v-text-field
        v-model="search"
        label="Search events"
        append-inner-icon="mdi-magnify"
        class="search-field"
        style="max-width: 320px"
      />
    </v-container>

    <EventFilterDialog
      v-model="dialogOpen"
      :categories="categories"
      :category-ids="categoryIds"
      :date-from="dateFrom"
      :date-to="dateTo"
      @apply="applyFilters"
    />
  </div>
</template>

<style scoped>
.filter-btn {
  --v-btn-height: 36px;
}
.search-field :deep(.v-field) {
  --v-input-control-height: 36px;
}
</style>
