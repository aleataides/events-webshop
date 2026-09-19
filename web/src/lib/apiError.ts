import type { ApiErrorResponse } from '@/types/apiError'

import { isAxiosError } from 'axios'

export function getApiErrorCode(error: unknown): string | null {
  if (isAxiosError<ApiErrorResponse>(error) && error.response) {
    return error.response.data.error.code
  }

  return null
}
