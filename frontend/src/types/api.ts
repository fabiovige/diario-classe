export interface ApiResponse<T> {
  data: T
}

export interface PaginatedData<T> {
  data: T[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    from: number | null
    last_page: number
    per_page: number
    to: number | null
    total: number
    path: string
  }
}

export interface ApiError {
  error: string
  errors?: Record<string, string[]>
}
