import { getStoredCartId } from '@/lib/cartStorage'
import axios from 'axios'

/**
 * Never sent as a URL param — the cart id is a bearer credential, see
 * docs/shared/business-rules.md#cart-identity.
 */
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
})

apiClient.interceptors.request.use((config) => {
  const cartId = getStoredCartId()
  if (cartId) {
    config.headers['X-Cart-Id'] = cartId
  }

  return config
})

export default apiClient
