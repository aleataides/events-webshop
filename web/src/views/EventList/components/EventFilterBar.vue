<script setup lang="ts">
import type { EventCategory } from '@/types/event'

import { computed } from 'vue'

defineProps<{ categories: EventCategory[] }>()

const search = defineModel<string>('search', { required: true })
const categoryId = defineModel<string | null>('categoryId', { required: true })
const dateFrom = defineModel<string>('dateFrom', { required: true })
const dateTo = defineModel<string>('dateTo', { required: true })

const hasDateFilter = computed(() => dateFrom.value !== '' || dateTo.value !== '')
const activeFilterCount = computed(
  () => Number(categoryId.value !== null) + Number(hasDateFilter.value),
)

function clearFilters(): void {
  categoryId.value = null
  dateFrom.value = ''
  dateTo.value = ''
}
</script>

<template>
  <div class="bg-surface border-b">
    <v-container class="d-flex align-center ga-4 flex-wrap py-4" style="max-width: 1440px">
      <v-menu :close-on-content-click="false">
        <template #activator="{ props: menuProps }">
          <v-badge
            :content="activeFilterCount"
            :model-value="activeFilterCount > 0"
            color="primary"
            floating
          >
            <v-btn
              variant="outlined"
              class="filter-btn"
              prepend-icon="mdi-tune-variant"
              v-bind="menuProps"
              >Filter</v-btn
            >
          </v-badge>
        </template>
        <v-card min-width="280" class="pa-4">
          <v-select
            v-model="categoryId"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Category"
            clearable
            class="mb-2"
          />
          <v-text-field v-model="dateFrom" type="date" label="From" class="mb-2" />
          <v-text-field v-model="dateTo" type="date" label="To" class="mb-2" />
          <v-btn
            v-if="activeFilterCount > 0"
            variant="text"
            size="small"
            block
            @click="clearFilters"
          >
            Clear filters
          </v-btn>
        </v-card>
      </v-menu>

      <v-spacer />

      <v-text-field
        v-model="search"
        label="Search events"
        append-inner-icon="mdi-magnify"
        class="search-field"
        style="max-width: 320px"
      />
    </v-container>
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
