import { apiDelete, apiDownload, apiGet, apiPatch, apiPost, apiPreview, apiPut, apiUpload } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { ClassAssignment, Enrollment, EnrollmentDocument, EnrollmentMovement } from '@/types/enrollment'

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
  transfer(enrollmentId: number, data: { type: string; movement_date: string; reason?: string; destination_school_id?: number }): Promise<void> {
    return apiPost(`enrollments/${enrollmentId}/transfer`, data)
  },
  getMovements(enrollmentId: number): Promise<EnrollmentMovement[]> {
    return apiGet<EnrollmentMovement[]>(`enrollments/${enrollmentId}/movements`)
  },
  getDocuments(enrollmentId: number): Promise<EnrollmentDocument[]> {
    return apiGet<EnrollmentDocument[]>(`enrollments/${enrollmentId}/documents`)
  },
  uploadDocument(enrollmentId: number, documentType: string, file: File, notes?: string): Promise<EnrollmentDocument> {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('document_type', documentType)
    if (notes) {
      formData.append('notes', notes)
    }
    return apiUpload<EnrollmentDocument>(`enrollments/${enrollmentId}/documents/upload`, formData)
  },
  downloadDocument(enrollmentId: number, documentType: string, filename: string): Promise<void> {
    return apiDownload(`enrollments/${enrollmentId}/documents/${documentType}/download`, filename)
  },
  previewDocument(enrollmentId: number, documentType: string): Promise<string> {
    return apiPreview(`enrollments/${enrollmentId}/documents/${documentType}/download`)
  },
  reviewDocument(enrollmentId: number, documentType: string, action: 'approve' | 'reject', rejectionReason?: string): Promise<EnrollmentDocument> {
    return apiPatch<EnrollmentDocument>(`enrollments/${enrollmentId}/documents/${documentType}/review`, {
      action,
      rejection_reason: rejectionReason,
    })
  },
  cancelEnrollment(id: number): Promise<void> {
    return apiDelete(`enrollments/${id}`)
  },
  reactivateEnrollment(id: number): Promise<Enrollment> {
    return apiPatch<Enrollment>(`enrollments/${id}/reactivate`)
  },
  updateClassAssignment(enrollmentId: number, assignmentId: number, data: Record<string, unknown>): Promise<ClassAssignment> {
    return apiPut<ClassAssignment>(`enrollments/${enrollmentId}/class-assignments/${assignmentId}`, data)
  },
  deleteClassAssignment(enrollmentId: number, assignmentId: number): Promise<void> {
    return apiDelete(`enrollments/${enrollmentId}/class-assignments/${assignmentId}`)
  },
}
