import type { AttendanceStatus } from './enums'
import type { Student } from './people'

export interface AttendanceRecord {
  id: number
  class_group_id: number
  teacher_assignment_id: number
  student_id: number
  date: string | null
  status: AttendanceStatus
  recorded_by: number
  student?: Student
  created_at: string
  updated_at: string
}

export interface AbsenceJustification {
  id: number
  student_id: number
  start_date: string | null
  end_date: string | null
  reason: string
  document_path: string
  approved: boolean
  approved_by: number | null
  approved_at: string | null
  created_by: number
  created_at: string
}

export interface AttendanceConfig {
  id: number
  school_id: number
  academic_year_id: number
  consecutive_absences_alert: number
  monthly_absences_alert: number
  period_absence_percentage_alert: number
  annual_minimum_frequency: number
  created_at: string
  updated_at: string
}

export interface BulkAttendanceRequest {
  class_group_id: number
  teacher_assignment_id: number
  date: string
  records: { student_id: number; status: AttendanceStatus }[]
}

export interface StudentFrequency {
  total_classes: number
  present: number
  absent: number
  justified: number
  excused: number
  frequency_percentage: number
}
