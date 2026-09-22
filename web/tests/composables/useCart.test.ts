import { getCart } from '@/api/cart'
import { useCart } from '@/composables/useCart'
import { useCartStore } from '@/stores/cart'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@/api/cart', () => ({ getCart: vi.fn() }))

// Mounted via a host component: useCart relies on onMounted (lifecycle hooks
// only work inside an active component instance).
function setup(affiliateId: string) {
  let result!: ReturnType<typeof useCart>
  const wrapper = mount({
    setup() {
      result = useCart(affiliateId)

      return () => null
    },
  })

  return { wrapper, ...result }
}

describe('useCart', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.useFakeTimers()
  })

  it('marks the cart expired once its own countdown reaches zero', async () => {
    const cart = {
      id: 'cart-1',
      expiresAt: new Date(Date.now() + 1000).toISOString(),
      items: [],
      total: '0.00',
    }
    vi.mocked(getCart).mockResolvedValue(cart)

    const { expired } = setup('affiliate-1')
    await vi.advanceTimersByTimeAsync(0)

    expect(expired.value).toBe(false)
    expect(useCartStore().cart).toEqual(cart)

    await vi.advanceTimersByTimeAsync(2000)

    expect(expired.value).toBe(true)
    expect(useCartStore().cart).toBeNull()
  })
})
