import type { ErrorCode } from './errorCode'

export interface ApiErrorResponse {
  error: {
    code: ErrorCode
    message: string
    request_id: string
  }
}
