import { apiGet, apiPost } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { PeriodClosing, Rectification, FinalResult } from '@/types/period-closing'

export const periodClosingService = {
  getClosings(params?: Record<string, unknown>): Promise<PaginatedData<PeriodClosing>> {
    return apiGet<PaginatedData<PeriodClosing>>('period-closings', params)
  },
  check(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/check`)
  },
  submit(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/submit`)
  },
  validate(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/validate`)
  },
  close(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/close`)
  },
  getDashboard(): Promise<any> {
    return apiGet('period-closings/dashboard')
  },
  createRectification(data: Partial<Rectification>): Promise<Rectification> {
    return apiPost<Rectification>('rectifications', data)
  },
  approveRectification(id: number): Promise<Rectification> {
    return apiPost<Rectification>(`rectifications/${id}/approve`)
  },
  calculateFinalResult(data: Record<string, unknown>): Promise<FinalResult> {
    return apiPost<FinalResult>('final-results/calculate', data)
  },
  getStudentFinalResult(studentId: number): Promise<FinalResult> {
    return apiGet<FinalResult>(`final-results/student/${studentId}`)
  },
}
