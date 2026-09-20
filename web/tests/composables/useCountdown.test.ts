import { useCountdown } from '@/composables/useCountdown'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

// onUnmounted needs an active component instance.
function setup(expiresAt: () => string | null, totalSeconds?: number) {
  let result!: ReturnType<typeof useCountdown>
  mount({
    setup() {
      result = useCountdown(expiresAt, totalSeconds)

      return () => null
    },
  })

  return result
}

describe('useCountdown', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  it('derives progress from the given total, not from time-remaining-at-mount', () => {
    // Entering 30s into a known 60s window — read as half gone, not "full".
    const expiresAt = new Date(Date.now() + 30_000).toISOString()

    const { progressPercent } = setup(() => expiresAt, 60)

    expect(progressPercent.value).toBeCloseTo(50, 0)
  })

  it('reports 0% progress when no total window is known', () => {
    const expiresAt = new Date(Date.now() + 30_000).toISOString()

    const { progressPercent } = setup(() => expiresAt)

    expect(progressPercent.value).toBe(0)
  })
})
