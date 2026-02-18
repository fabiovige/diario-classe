import { apiGet, apiPost } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { Enrollment, EnrollmentMovement } from '@/types/enrollment'

export const enrollmentService = {
  getEnrollments(params?: Record<string, unknown>): Promise<PaginatedData<Enrollment>> {
    return apiGet<PaginatedData<Enrollment>>('enrollments', params)
  },
  getEnrollment(id: number): Promise<Enrollment> {
    return apiGet<Enrollment>(`enrollments/${id}`)
  },
  createEnrollment(data: Record<string, unknown>): Promise<Enrollment> {
    return apiPost<Enrollment>('enrollments', data)
  },
  assignToClass(enrollmentId: number, data: { class_group_id: number }): Promise<void> {
    return apiPost(`enrollments/${enrollmentId}/assign-class`, data)
  },
  transfer(enrollmentId: number, data: { target_school_id: number; reason: string }): Promise<void> {
    return apiPost(`enrollments/${enrollmentId}/transfer`, data)
  },
  getMovements(enrollmentId: number): Promise<EnrollmentMovement[]> {
    return apiGet<EnrollmentMovement[]>(`enrollments/${enrollmentId}/movements`)
  },
}
