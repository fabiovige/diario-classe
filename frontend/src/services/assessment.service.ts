import { apiGet, apiPost, apiPut } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { Grade, PeriodAverage, DescriptiveReport, AssessmentConfig, BulkGradeRequest, ReportCardResponse } from '@/types/assessment'

export const assessmentService = {
  bulkGrades(data: BulkGradeRequest): Promise<Grade[]> {
    return apiPost<Grade[]>('grades/bulk', data)
  },
  getGrades(params?: Record<string, unknown>): Promise<PaginatedData<Grade>> {
    return apiGet<PaginatedData<Grade>>('grades', params)
  },
  updateGrade(id: number, data: Partial<Grade>): Promise<Grade> {
    return apiPut<Grade>(`grades/${id}`, data)
  },
  recoveryGrade(data: Partial<Grade>): Promise<Grade> {
    return apiPost<Grade>('grades/recovery', data)
  },
  calculatePeriodAverage(data: Record<string, unknown>): Promise<PeriodAverage> {
    return apiPost<PeriodAverage>('period-averages/calculate', data)
  },
  getDescriptiveReports(params?: Record<string, unknown>): Promise<PaginatedData<DescriptiveReport>> {
    return apiGet<PaginatedData<DescriptiveReport>>('descriptive-reports', params)
  },
  getDescriptiveReport(id: number): Promise<DescriptiveReport> {
    return apiGet<DescriptiveReport>(`descriptive-reports/${id}`)
  },
  createDescriptiveReport(data: Record<string, unknown>): Promise<DescriptiveReport> {
    return apiPost<DescriptiveReport>('descriptive-reports', data)
  },
  updateDescriptiveReport(id: number, data: Record<string, unknown>): Promise<DescriptiveReport> {
    return apiPut<DescriptiveReport>(`descriptive-reports/${id}`, data)
  },
  getReportCard(studentId: number): Promise<ReportCardResponse> {
    return apiGet<ReportCardResponse>(`report-cards/student/${studentId}`)
  },
  getConfigs(params?: Record<string, unknown>): Promise<PaginatedData<AssessmentConfig>> {
    return apiGet<PaginatedData<AssessmentConfig>>('assessment-configs', params)
  },
  createConfig(data: Partial<AssessmentConfig>): Promise<AssessmentConfig> {
    return apiPost<AssessmentConfig>('assessment-configs', data)
  },
}
