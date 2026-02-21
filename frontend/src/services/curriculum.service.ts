import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { CurricularComponent, DailyAssignmentSummary, ExperienceField, TeacherAssignment } from '@/types/curriculum'

export const curriculumService = {
  getComponents(params?: Record<string, unknown>): Promise<PaginatedData<CurricularComponent>> {
    return apiGet<PaginatedData<CurricularComponent>>('curricular-components', params)
  },
  getComponent(id: number): Promise<CurricularComponent> {
    return apiGet<CurricularComponent>(`curricular-components/${id}`)
  },
  createComponent(data: Partial<CurricularComponent>): Promise<CurricularComponent> {
    return apiPost<CurricularComponent>('curricular-components', data)
  },
  updateComponent(id: number, data: Partial<CurricularComponent>): Promise<CurricularComponent> {
    return apiPut<CurricularComponent>(`curricular-components/${id}`, data)
  },
  deleteComponent(id: number): Promise<void> {
    return apiDelete(`curricular-components/${id}`)
  },
  getExperienceFields(params?: Record<string, unknown>): Promise<PaginatedData<ExperienceField>> {
    return apiGet<PaginatedData<ExperienceField>>('experience-fields', params)
  },
  getExperienceField(id: number): Promise<ExperienceField> {
    return apiGet<ExperienceField>(`experience-fields/${id}`)
  },
  createExperienceField(data: Partial<ExperienceField>): Promise<ExperienceField> {
    return apiPost<ExperienceField>('experience-fields', data)
  },
  updateExperienceField(id: number, data: Partial<ExperienceField>): Promise<ExperienceField> {
    return apiPut<ExperienceField>(`experience-fields/${id}`, data)
  },
  deleteExperienceField(id: number): Promise<void> {
    return apiDelete(`experience-fields/${id}`)
  },
  getAssignments(params?: Record<string, unknown>): Promise<PaginatedData<TeacherAssignment>> {
    return apiGet<PaginatedData<TeacherAssignment>>('teacher-assignments', params)
  },
  getAssignment(id: number): Promise<TeacherAssignment> {
    return apiGet<TeacherAssignment>(`teacher-assignments/${id}`)
  },
  createAssignment(data: Record<string, unknown>): Promise<TeacherAssignment> {
    return apiPost<TeacherAssignment>('teacher-assignments', data)
  },
  updateAssignment(id: number, data: Record<string, unknown>): Promise<TeacherAssignment> {
    return apiPut<TeacherAssignment>(`teacher-assignments/${id}`, data)
  },
  deleteAssignment(id: number): Promise<void> {
    return apiDelete(`teacher-assignments/${id}`)
  },
  getDailySummary(date: string, teacherId?: number | null): Promise<DailyAssignmentSummary[]> {
    const params: Record<string, unknown> = { date }
    if (teacherId) {
      params.teacher_id = teacherId
    }
    return apiGet<DailyAssignmentSummary[]>('teacher-assignments/daily-summary', params)
  },
}
