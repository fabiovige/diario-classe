import { apiGet, apiPost, apiPut } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { CurricularComponent, ExperienceField, TeacherAssignment } from '@/types/curriculum'

export const curriculumService = {
  getComponents(params?: Record<string, unknown>): Promise<PaginatedData<CurricularComponent>> {
    return apiGet<PaginatedData<CurricularComponent>>('curricular-components', params)
  },
  createComponent(data: Partial<CurricularComponent>): Promise<CurricularComponent> {
    return apiPost<CurricularComponent>('curricular-components', data)
  },
  getComponent(id: number): Promise<CurricularComponent> {
    return apiGet<CurricularComponent>(`curricular-components/${id}`)
  },
  getExperienceFields(params?: Record<string, unknown>): Promise<PaginatedData<ExperienceField>> {
    return apiGet<PaginatedData<ExperienceField>>('experience-fields', params)
  },
  createExperienceField(data: Partial<ExperienceField>): Promise<ExperienceField> {
    return apiPost<ExperienceField>('experience-fields', data)
  },
  getExperienceField(id: number): Promise<ExperienceField> {
    return apiGet<ExperienceField>(`experience-fields/${id}`)
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
}
