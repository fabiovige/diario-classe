import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { AssessmentPeriod } from '@/types/academic-calendar'

export const academicCalendarService = {
  getPeriods(params?: Record<string, unknown>): Promise<PaginatedData<AssessmentPeriod>> {
    return apiGet<PaginatedData<AssessmentPeriod>>('assessment-periods', params)
  },
  getPeriod(id: number): Promise<AssessmentPeriod> {
    return apiGet<AssessmentPeriod>(`assessment-periods/${id}`)
  },
  createPeriod(data: Record<string, unknown>): Promise<AssessmentPeriod> {
    return apiPost<AssessmentPeriod>('assessment-periods', data)
  },
  updatePeriod(id: number, data: Record<string, unknown>): Promise<AssessmentPeriod> {
    return apiPut<AssessmentPeriod>(`assessment-periods/${id}`, data)
  },
  deletePeriod(id: number): Promise<void> {
    return apiDelete(`assessment-periods/${id}`)
  },
}
