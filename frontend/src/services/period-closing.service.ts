import { apiGet, apiPost } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type {
  PeriodClosing,
  Rectification,
  FinalResult,
  AnnualResultResponse,
  BulkCloseResult,
  TeacherPendency,
  ClassGroupClosingStatus,
} from '@/types/period-closing'

export const periodClosingService = {
  getClosings(params?: Record<string, unknown>): Promise<PaginatedData<PeriodClosing>> {
    return apiGet<PaginatedData<PeriodClosing>>('period-closings', params)
  },
  getClosing(id: number): Promise<PeriodClosing> {
    return apiGet<PeriodClosing>(`period-closings/${id}`)
  },
  check(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/check`)
  },
  submit(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/submit`)
  },
  validate(id: number, data: { approve: boolean; rejection_reason?: string }): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/validate`, data)
  },
  close(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/close`)
  },
  teacherClose(id: number): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/teacher-close`)
  },
  bulkTeacherClose(data: { class_group_id: number; teacher_assignment_id: number }): Promise<BulkCloseResult> {
    return apiPost<BulkCloseResult>('period-closings/bulk-teacher-close', data)
  },
  reopen(id: number, reason: string): Promise<PeriodClosing> {
    return apiPost<PeriodClosing>(`period-closings/${id}/reopen`, { reason })
  },
  getPendencies(params: { school_id: number; academic_year_id: number }): Promise<TeacherPendency[]> {
    return apiGet<TeacherPendency[]>('period-closings/pendencies', params)
  },
  getMyClosings(params?: Record<string, unknown>): Promise<PeriodClosing[]> {
    return apiGet<PeriodClosing[]>('period-closings/my-closings', params)
  },
  getClassGroupStatus(params: { academic_year_id: number }): Promise<ClassGroupClosingStatus[]> {
    return apiGet<ClassGroupClosingStatus[]>('period-closings/class-group-status', params)
  },
  getDashboard(params?: Record<string, unknown>): Promise<any> {
    return apiGet('period-closings/dashboard', params)
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
  getStudentFinalResult(studentId: number): Promise<FinalResult[]> {
    return apiGet<FinalResult[]>(`final-results/student/${studentId}`)
  },
  getClassGroupResults(classGroupId: number): Promise<AnnualResultResponse> {
    return apiGet<AnnualResultResponse>(`final-results/class-group/${classGroupId}`)
  },
  calculateBulkResults(data: { class_group_id: number; academic_year_id: number }): Promise<FinalResult[]> {
    return apiPost<FinalResult[]>('final-results/calculate-bulk', data)
  },
}
