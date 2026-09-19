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
  <v-container v-if="loading" class="d-flex justify-center py-12">
    <LoadingSpinner />
  </v-container>

  <v-container v-else-if="event" class="py-8" style="max-width: 1440px">
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
