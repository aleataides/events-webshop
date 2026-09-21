import type { EventDetail } from '@/types/event'

import TicketSelector from '@/views/EventDetail/components/TicketSelector.vue'
import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

function eventWith(areas: EventDetail['areas']): EventDetail {
  return {
    id: 'event-1',
    title: 'Test Event',
    subtitle: '',
    start: '2026-01-01T20:00:00Z',
    end: '2026-01-01T22:00:00Z',
    salesEnd: '2026-01-01T20:00:00Z',
    doorsOpen: null,
    doorsClose: null,
    status: 'PUBLISHED',
    eventType: 'CONCERT',
    soldout: areas.every((area) => area.available <= 0),
    image: { id: 'img-1', copyright: '' },
    minPrice: null,
    maxPrice: null,
    currency: null,
    venue: {
      id: 'venue-1',
      name: '',
      street: '',
      zipCode: '',
      city: '',
      country: '',
      geo: { latitude: '0', longitude: '0' },
    },
    categories: [],
    description: '',
    priceInfo: '',
    areas,
  }
}

// Renders unresolved v-btn/v-card as their bare tag (attrs/slot content
// intact) — no need for the real Vuetify runtime just to check this text.
function mountSelector(event: EventDetail) {
  return mount(TicketSelector, {
    props: {
      event,
      selectedQty: {},
      totalQty: 0,
      totalValue: 0,
      submitting: false,
    },
  })
}

describe('TicketSelector', () => {
  it('shows "Sold out" and keeps the CTA disabled when every area is sold out', () => {
    const wrapper = mountSelector(
      eventWith([{ id: 'area-1', name: 'Main Floor', capacity: 10, available: 0, prices: [] }]),
    )

    const button = wrapper.get('v-btn')
    expect(button.text()).toBe('Sold out')
    expect(button.attributes('disabled')).toBeDefined()
  })

  it('shows "Select tickets" when at least one area has availability', () => {
    const wrapper = mountSelector(
      eventWith([{ id: 'area-1', name: 'Main Floor', capacity: 10, available: 5, prices: [] }]),
    )

    expect(wrapper.get('v-btn').text()).toBe('Select tickets')
  })
})
