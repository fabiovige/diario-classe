import type { Teacher } from './people'
import type { ClassGroup } from './school-structure'

export interface CurricularComponent {
  id: number
  name: string
  knowledge_area: string
  knowledge_area_label: string
  code: string
  active: boolean
  created_at: string
  updated_at: string
}

export interface ExperienceField {
  id: number
  name: string
  code: string
  active: boolean
  created_at: string
  updated_at: string
}

export interface TeacherAssignment {
  id: number
  teacher_id: number
  class_group_id: number
  curricular_component_id: number | null
  experience_field_id: number | null
  start_date: string | null
  end_date: string | null
  active: boolean
  teacher?: Teacher
  class_group?: ClassGroup
  curricular_component?: CurricularComponent
  experience_field?: ExperienceField
  created_at: string
  updated_at: string
}

export interface TimeSlot {
  id: number
  shift_id: number
  number: number
  start_time: string
  end_time: string
  type: string
  type_label: string
  shift?: import('./school-structure').Shift
  created_at: string
  updated_at: string
}

export interface ClassSchedule {
  id: number
  teacher_assignment_id: number
  time_slot_id: number
  day_of_week: number
  day_of_week_label: string
  day_of_week_short: string
  time_slot?: TimeSlot
  teacher_assignment?: TeacherAssignment
  created_at: string
  updated_at: string
}

export type DayOfWeek = 1 | 2 | 3 | 4 | 5

export interface DailyAssignmentSummary extends TeacherAssignment {
  has_attendance: boolean
  has_lesson_record: boolean
  has_open_period: boolean
}
