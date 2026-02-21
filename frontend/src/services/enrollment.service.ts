import { apiGet, apiPatch, apiPost } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { Enrollment, EnrollmentDocument, EnrollmentMovement } from '@/types/enrollment'
import type { DocumentType, MovementType } from '@/types/enums'

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
  assignToClass(enrollmentId: number, data: { class_group_id: number; start_date: string }): Promise<void> {
    return apiPost(`enrollments/${enrollmentId}/assign-class`, data)
  },
  transfer(enrollmentId: number, data: { type: MovementType; movement_date: string; reason?: string; destination_school_id?: number }): Promise<void> {
    return apiPost(`enrollments/${enrollmentId}/transfer`, data)
  },
  getMovements(enrollmentId: number): Promise<EnrollmentMovement[]> {
    return apiGet<EnrollmentMovement[]>(`enrollments/${enrollmentId}/movements`)
  },
  getDocuments(enrollmentId: number): Promise<EnrollmentDocument[]> {
    return apiGet<EnrollmentDocument[]>(`enrollments/${enrollmentId}/documents`)
  },
  toggleDocument(enrollmentId: number, documentType: DocumentType, data: { delivered: boolean; notes?: string }): Promise<EnrollmentDocument> {
    return apiPatch<EnrollmentDocument>(`enrollments/${enrollmentId}/documents/${documentType}`, data)
  },
}
