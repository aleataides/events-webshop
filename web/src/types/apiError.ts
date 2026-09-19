export interface ApiErrorResponse {
  error: {
    code: string
    message: string
    request_id: string
  }
}
