import type { AcademicYearStatus, EducationLevel, ShiftPeriod } from './enums'

export interface School {
  id: number
  name: string
  inep_code: string
  type: string
  address: string
  phone: string
  email: string
  active: boolean
  created_at: string
}

export interface AcademicYear {
  id: number
  school_id: number
  year: number
  status: AcademicYearStatus
  start_date: string | null
  end_date: string | null
  school?: School
}

export interface Shift {
  id: number
  school_id: number
  name: ShiftPeriod
  name_label: string
  start_time: string | null
  end_time: string | null
  school?: School
}

export interface GradeLevel {
  id: number
  name: string
  type: EducationLevel
  type_label: string
  order: number
}

export interface ClassGroup {
  id: number
  academic_year_id: number
  grade_level_id: number
  shift_id: number
  name: string
  max_students: number
  academic_year?: AcademicYear
  grade_level?: GradeLevel
  shift?: Shift
}
