import type { AxiosError } from 'axios'

interface ValidationErrorResponse {
  message?: string
  errors?: Record<string, string[]>
  error?: string
}

export function extractApiError(error: unknown, fallback: string): string {
  const axiosError = error as AxiosError<ValidationErrorResponse>
  const data = axiosError?.response?.data
  if (!data) return fallback

  if (data.error) return data.error

  if (data.errors) {
    const firstField = Object.values(data.errors)[0]
    if (firstField?.[0]) return firstField[0]
  }

  if (data.message) return data.message

  return fallback
}
