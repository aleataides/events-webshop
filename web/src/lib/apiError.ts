import type { ApiErrorResponse } from '@/types/apiError'
import type { ErrorCode } from '@/types/errorCode'

import { isAxiosError } from 'axios'

export function getApiErrorCode(error: unknown): ErrorCode | null {
  if (isAxiosError<ApiErrorResponse>(error) && error.response) {
    return error.response.data.error.code
  }

  return null
}
