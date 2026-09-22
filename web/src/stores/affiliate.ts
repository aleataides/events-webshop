import { getAffiliate } from '@/api/affiliate'
import { defineStore } from 'pinia'

/**
 * Multi-tenant affiliate id, resolved from the `:affiliateId` route param —
 * see docs/shared/business-rules.md#affiliate-scoping.
 */
export const useAffiliateStore = defineStore('affiliate', {
  state: () => ({
    affiliateId: null as string | null,
    affiliateName: null as string | null,
  }),
  actions: {
    async setAffiliateId(affiliateId: string): Promise<void> {
      if (this.affiliateId === affiliateId) return

      this.affiliateId = affiliateId
      this.affiliateName = null
      this.affiliateName = (await getAffiliate(affiliateId)).name
    },
  },
})
