import { getStoredCartId } from '@/lib/cartStorage'
import axios from 'axios'

declare module 'axios' {
  interface AxiosRequestConfig {
    _retriedAfterNetworkError?: boolean
  }
}

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

/**
 * The local dev API runs on PHP's single-threaded built-in server, which
 * occasionally drops a request under concurrent load — retry once.
 */
apiClient.interceptors.response.use(undefined, async (error: unknown) => {
  if (
    !axios.isAxiosError(error) ||
    error.response ||
    !error.config ||
    error.config._retriedAfterNetworkError
  ) {
    throw error
  }

  error.config._retriedAfterNetworkError = true

  return apiClient(error.config)
})

export default apiClient
