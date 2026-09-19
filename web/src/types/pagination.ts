export interface Paginated<T> {
  data: T[]
  meta: {
    next_cursor: string | null
    has_more: boolean
  }
}
