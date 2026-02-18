import { apiGet, apiPost } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { AttendanceRecord, AbsenceJustification, AttendanceConfig, BulkAttendanceRequest, StudentFrequency } from '@/types/attendance'

export const attendanceService = {
  bulkRecord(data: BulkAttendanceRequest): Promise<AttendanceRecord[]> {
    return apiPost<AttendanceRecord[]>('attendance/bulk', data)
  },
  getByClass(classGroupId: number, params?: Record<string, unknown>): Promise<PaginatedData<AttendanceRecord>> {
    return apiGet<PaginatedData<AttendanceRecord>>(`attendance/class/${classGroupId}`, params)
  },
  getByStudent(studentId: number, params?: Record<string, unknown>): Promise<PaginatedData<AttendanceRecord>> {
    return apiGet<PaginatedData<AttendanceRecord>>(`attendance/student/${studentId}`, params)
  },
  getStudentFrequency(studentId: number, params?: Record<string, unknown>): Promise<StudentFrequency> {
    return apiGet<StudentFrequency>(`attendance/student/${studentId}/frequency`, params)
  },
  getAlerts(studentId: number, params?: Record<string, unknown>): Promise<any> {
    return apiGet(`attendance/alerts/${studentId}`, params)
  },
  createJustification(data: Record<string, unknown>): Promise<AbsenceJustification> {
    return apiPost<AbsenceJustification>('absence-justifications', data)
  },
  approveJustification(id: number): Promise<AbsenceJustification> {
    return apiPost<AbsenceJustification>(`absence-justifications/${id}/approve`)
  },
  getConfigs(params?: Record<string, unknown>): Promise<PaginatedData<AttendanceConfig>> {
    return apiGet<PaginatedData<AttendanceConfig>>('attendance-configs', params)
  },
  createConfig(data: Partial<AttendanceConfig>): Promise<AttendanceConfig> {
    return apiPost<AttendanceConfig>('attendance-configs', data)
  },
}
