import { apiGet, apiPost, apiPut, apiDelete } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { Student, Guardian, Teacher } from '@/types/people'

export const peopleService = {
  getStudents(params?: Record<string, unknown>): Promise<PaginatedData<Student>> {
    return apiGet<PaginatedData<Student>>('students', params)
  },
  getStudent(id: number): Promise<Student> {
    return apiGet<Student>(`students/${id}`)
  },
  createStudent(data: Partial<Student>): Promise<Student> {
    return apiPost<Student>('students', data)
  },
  updateStudent(id: number, data: Partial<Student>): Promise<Student> {
    return apiPut<Student>(`students/${id}`, data)
  },
  deleteStudent(id: number): Promise<void> {
    return apiDelete(`students/${id}`)
  },
  attachGuardian(studentId: number, data: { guardian_id: number; relationship: string; is_primary: boolean }): Promise<void> {
    return apiPost(`students/${studentId}/guardians`, data)
  },
  getGuardians(params?: Record<string, unknown>): Promise<PaginatedData<Guardian>> {
    return apiGet<PaginatedData<Guardian>>('guardians', params)
  },
  getGuardian(id: number): Promise<Guardian> {
    return apiGet<Guardian>(`guardians/${id}`)
  },
  createGuardian(data: Partial<Guardian>): Promise<Guardian> {
    return apiPost<Guardian>('guardians', data)
  },
  updateGuardian(id: number, data: Partial<Guardian>): Promise<Guardian> {
    return apiPut<Guardian>(`guardians/${id}`, data)
  },
  deleteGuardian(id: number): Promise<void> {
    return apiDelete(`guardians/${id}`)
  },
  getTeachers(params?: Record<string, unknown>): Promise<PaginatedData<Teacher>> {
    return apiGet<PaginatedData<Teacher>>('teachers', params)
  },
  getTeacher(id: number): Promise<Teacher> {
    return apiGet<Teacher>(`teachers/${id}`)
  },
  createTeacher(data: Record<string, unknown>): Promise<Teacher> {
    return apiPost<Teacher>('teachers', data)
  },
  updateTeacher(id: number, data: Record<string, unknown>): Promise<Teacher> {
    return apiPut<Teacher>(`teachers/${id}`, data)
  },
  deleteTeacher(id: number): Promise<void> {
    return apiDelete(`teachers/${id}`)
  },
}
