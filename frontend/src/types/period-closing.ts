import type { PeriodClosingStatus, FinalResultStatus } from './enums'
import type { AssessmentPeriod } from './academic-calendar'
import type { ClassGroup } from './school-structure'
import type { TeacherAssignment } from './curriculum'
import type { Student } from './people'

export interface PeriodClosing {
  id: number
  class_group_id: number
  teacher_assignment_id: number
  assessment_period_id: number
  status: PeriodClosingStatus
  submitted_by: number | null
  submitted_at: string | null
  validated_by: number | null
  validated_at: string | null
  approved_by: number | null
  approved_at: string | null
  rejection_reason: string | null
  all_grades_complete: boolean
  all_attendance_complete: boolean
  all_lesson_records_complete: boolean
  assessment_period?: AssessmentPeriod
  class_group?: ClassGroup
  teacher_assignment?: TeacherAssignment
  created_at: string
  updated_at: string
}

export interface Rectification {
  id: number
  period_closing_id: number
  entity_type: string
  entity_id: number
  field_changed: string
  old_value: string
  new_value: string
  justification: string
  requested_by: number
  approved_by: number | null
  status: string
  created_at: string
  updated_at: string
}

export interface FinalResult {
  id: number
  student_id: number
  class_group_id: number
  academic_year_id: number
  result: FinalResultStatus
  overall_average: number | null
  overall_frequency: number
  council_override: boolean
  observations: string
  determined_by: number
  student?: Student
  created_at: string
  updated_at: string
}
