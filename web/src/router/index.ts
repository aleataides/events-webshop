import { RouteName } from '@/router/routeNames'
import { useAffiliateStore } from '@/stores/affiliate'
import { useUiStore } from '@/stores/ui'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/:affiliateId/events',
      name: RouteName.EventList,
      component: () => import('@/views/EventList/EventListPage.vue'),
    },
    {
      path: '/:affiliateId/events/:eventId',
      name: RouteName.EventDetail,
      component: () => import('@/views/EventDetail/EventDetailPage.vue'),
    },
    {
      path: '/:affiliateId/cart',
      name: RouteName.Cart,
      component: () => import('@/views/Cart/CartPage.vue'),
    },
    {
      path: '/:pathMatch(.*)*',
      name: RouteName.NotFound,
      component: () => import('@/views/NotFound/NotFoundPage.vue'),
    },
  ],
})

router.beforeEach((to) => {
  useUiStore().startLoading()

  const affiliateId = to.params.affiliateId
  if (typeof affiliateId === 'string') {
    void useAffiliateStore().setAffiliateId(affiliateId)
  }
})

router.afterEach(() => {
  useUiStore().stopLoading()
})

export default router
