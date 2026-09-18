import { defineStore } from 'pinia'

/**
 * Multi-tenant affiliate id, resolved from the `:affiliateId` route param —
 * see docs/shared/business-rules.md#affiliate-scoping.
 */
export const useAffiliateStore = defineStore('affiliate', {
  state: () => ({
    affiliateId: null as string | null,
  }),
  actions: {
    setAffiliateId(affiliateId: string): void {
      this.affiliateId = affiliateId
    },
  },
})
