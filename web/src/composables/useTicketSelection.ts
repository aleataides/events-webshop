import type { EventDetail } from '@/types/event'

import { addCartItem } from '@/api/cart'
import { useCartStore } from '@/stores/cart'
import { computed, type Ref, ref } from 'vue'

export function useTicketSelection(affiliateId: string, event: Ref<EventDetail | null>) {
  const selectedQty = ref<Record<string, number>>({})
  const submitting = ref(false)

  const totalQty = computed(() =>
    Object.values(selectedQty.value).reduce((sum, qty) => sum + qty, 0),
  )

  const totalValue = computed(() => {
    const prices = event.value?.areas.flatMap((area) => area.prices) ?? []

    return prices.reduce(
      (sum, price) => sum + Number(price.value) * (selectedQty.value[price.id] ?? 0),
      0,
    )
  })

  /**
   * Adds every selected price to the cart. Caller decides what happens next
   * (e.g. navigate to the cart page) — this composable stays router-agnostic.
   */
  async function selectTickets(): Promise<void> {
    if (totalQty.value === 0) {
      return
    }

    submitting.value = true
    try {
      const cartStore = useCartStore()
      for (const [priceId, qty] of Object.entries(selectedQty.value)) {
        if (qty > 0) {
          const cart = await addCartItem(affiliateId, priceId, qty)
          cartStore.setCart(cart)
        }
      }
    } finally {
      submitting.value = false
    }
  }

  return { selectedQty, totalQty, totalValue, submitting, selectTickets }
}
