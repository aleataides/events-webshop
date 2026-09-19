import { useAffiliateStore } from '@/stores/affiliate'
import { useUiStore } from '@/stores/ui'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/:affiliateId/events',
      name: 'event-list',
      component: () => import('@/views/EventList/EventListPage.vue'),
    },
    {
      path: '/:affiliateId/events/:eventId',
      name: 'event-detail',
      component: () => import('@/views/EventDetail/EventDetailPage.vue'),
    },
    {
      path: '/:affiliateId/cart',
      name: 'cart',
      component: () => import('@/views/Cart/CartPage.vue'),
    },
  ],
})

router.beforeEach((to) => {
  useUiStore().startLoading()

  const affiliateId = to.params.affiliateId
  if (typeof affiliateId === 'string') {
    useAffiliateStore().setAffiliateId(affiliateId)
  }
})

router.afterEach(() => {
  useUiStore().stopLoading()
})

export default router
