import EventListPage from '@/views/EventList/EventListPage.vue'
import { describe, expect, it } from 'vitest'

describe('EventListPage', () => {
  it("is named to match App.vue's <keep-alive :include>", () => {
    // App.vue string-matches this name to keep-alive the page across navigation.
    expect(EventListPage.name).toBe('EventListPage')
  })
})
