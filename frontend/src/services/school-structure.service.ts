import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { School, AcademicYear, Shift, GradeLevel, ClassGroup } from '@/types/school-structure'

export const schoolStructureService = {
  getSchools(params?: Record<string, unknown>): Promise<PaginatedData<School>> {
    return apiGet<PaginatedData<School>>('schools', params)
  },
  getSchool(id: number): Promise<School> {
    return apiGet<School>(`schools/${id}`)
  },
  createSchool(data: Partial<School>): Promise<School> {
    return apiPost<School>('schools', data)
  },
  updateSchool(id: number, data: Partial<School>): Promise<School> {
    return apiPut<School>(`schools/${id}`, data)
  },
  deleteSchool(id: number): Promise<void> {
    return apiDelete(`schools/${id}`)
  },
  getAcademicYears(params?: Record<string, unknown>): Promise<PaginatedData<AcademicYear>> {
    return apiGet<PaginatedData<AcademicYear>>('academic-years', params)
  },
  getAcademicYear(id: number): Promise<AcademicYear> {
    return apiGet<AcademicYear>(`academic-years/${id}`)
  },
  createAcademicYear(data: Record<string, unknown>): Promise<AcademicYear> {
    return apiPost<AcademicYear>('academic-years', data)
  },
  updateAcademicYear(id: number, data: Record<string, unknown>): Promise<AcademicYear> {
    return apiPut<AcademicYear>(`academic-years/${id}`, data)
  },
  deleteAcademicYear(id: number): Promise<void> {
    return apiDelete(`academic-years/${id}`)
  },
  getShifts(params?: Record<string, unknown>): Promise<PaginatedData<Shift>> {
    return apiGet<PaginatedData<Shift>>('shifts', params)
  },
  getShift(id: number): Promise<Shift> {
    return apiGet<Shift>(`shifts/${id}`)
  },
  createShift(data: Record<string, unknown>): Promise<Shift> {
    return apiPost<Shift>('shifts', data)
  },
  updateShift(id: number, data: Record<string, unknown>): Promise<Shift> {
    return apiPut<Shift>(`shifts/${id}`, data)
  },
  deleteShift(id: number): Promise<void> {
    return apiDelete(`shifts/${id}`)
  },
  getGradeLevels(params?: Record<string, unknown>): Promise<PaginatedData<GradeLevel>> {
    return apiGet<PaginatedData<GradeLevel>>('grade-levels', params)
  },
  getGradeLevel(id: number): Promise<GradeLevel> {
    return apiGet<GradeLevel>(`grade-levels/${id}`)
  },
  createGradeLevel(data: Record<string, unknown>): Promise<GradeLevel> {
    return apiPost<GradeLevel>('grade-levels', data)
  },
  updateGradeLevel(id: number, data: Record<string, unknown>): Promise<GradeLevel> {
    return apiPut<GradeLevel>(`grade-levels/${id}`, data)
  },
  deleteGradeLevel(id: number): Promise<void> {
    return apiDelete(`grade-levels/${id}`)
  },
  getClassGroups(params?: Record<string, unknown>): Promise<PaginatedData<ClassGroup>> {
    return apiGet<PaginatedData<ClassGroup>>('class-groups', params)
  },
  getClassGroup(id: number): Promise<ClassGroup> {
    return apiGet<ClassGroup>(`class-groups/${id}`)
  },
  createClassGroup(data: Record<string, unknown>): Promise<ClassGroup> {
    return apiPost<ClassGroup>('class-groups', data)
  },
  updateClassGroup(id: number, data: Record<string, unknown>): Promise<ClassGroup> {
    return apiPut<ClassGroup>(`class-groups/${id}`, data)
  },
  deleteClassGroup(id: number): Promise<void> {
    return apiDelete(`class-groups/${id}`)
  },
  closeAcademicYear(id: number): Promise<AcademicYear> {
    return apiPost<AcademicYear>(`academic-years/${id}/close`)
  },
}
