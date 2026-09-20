<script setup lang="ts">
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { useEventDetail } from '@/composables/useEventDetail'
import { useTicketSelection } from '@/composables/useTicketSelection'
import { RouteName } from '@/router/routeNames'
import { useRoute, useRouter } from 'vue-router'

import EventDescription from './components/EventDescription.vue'
import EventHero from './components/EventHero.vue'
import EventLocation from './components/EventLocation.vue'
import EventSchedule from './components/EventSchedule.vue'
import TicketSelector from './components/TicketSelector.vue'

const route = useRoute()
const router = useRouter()
const affiliateId = route.params.affiliateId as string
const eventId = route.params.eventId as string

const { event, loading } = useEventDetail(affiliateId, eventId)
const { selectedQty, totalQty, totalValue, submitting, selectTickets } = useTicketSelection(
  affiliateId,
  event,
)

async function handleSelect(): Promise<void> {
  await selectTickets()
  await router.push({ name: RouteName.Cart, params: { affiliateId } })
}
</script>

<template>
  <v-container v-if="loading" class="d-flex align-center justify-center" style="min-height: 60vh">
    <LoadingSpinner />
  </v-container>

  <v-container v-else-if="event" class="py-8" style="max-width: 1440px">
    <router-link
      :to="{ name: RouteName.EventList, params: { affiliateId } }"
      class="d-flex align-center ga-1 text-body-2 font-weight-medium text-decoration-none text-high-emphasis mb-4 back-link"
      style="width: fit-content"
    >
      <v-icon icon="mdi-arrow-left" size="18" />
      <span class="back-link__text">Back to events</span>
    </router-link>

    <v-row>
      <v-col cols="12" md="7">
        <EventHero :event="event" />
        <v-divider class="mb-4" />
        <EventLocation :venue="event.venue" />
        <v-divider class="mb-4" />
        <EventSchedule :event="event" />
        <EventDescription :description="event.description" />
      </v-col>

      <v-col cols="12" md="5">
        <TicketSelector
          :event="event"
          :selected-qty="selectedQty"
          :total-qty="totalQty"
          :total-value="totalValue"
          :submitting="submitting"
          @update:qty="(priceId, qty) => (selectedQty[priceId] = qty)"
          @select="handleSelect"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.back-link:hover .back-link__text {
  text-decoration: underline !important;
}
</style>
