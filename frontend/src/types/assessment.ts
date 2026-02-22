import type { GradeType } from './enums'
import type { Student } from './people'

export interface Grade {
  id: number
  student_id: number
  class_group_id: number
  teacher_assignment_id: number
  assessment_period_id: number
  assessment_instrument_id: number | null
  numeric_value: number | null
  conceptual_value: string | null
  observations: string
  is_recovery: boolean
  recovery_type: string | null
  recorded_by: number
  student?: Student
  created_at: string
  updated_at: string
}

export interface PeriodAverage {
  id: number
  student_id: number
  class_group_id: number
  teacher_assignment_id: number
  assessment_period_id: number
  numeric_average: number | null
  conceptual_average: string | null
  total_absences: number
  frequency_percentage: number
  calculated_at: string
}

export interface DescriptiveReport {
  id: number
  student_id: number
  class_group_id: number
  experience_field_id: number
  assessment_period_id: number
  content: string
  recorded_by: number
  student?: { id: number; name: string }
  class_group?: { id: number; name: string; label: string }
  experience_field?: { id: number; name: string }
  assessment_period?: { id: number; name: string }
  created_at: string
  updated_at: string
}

export interface AssessmentConfig {
  id: number
  school_id: number
  academic_year_id: number
  grade_level_id: number
  grade_type: GradeType
  scale_min: number
  scale_max: number
  passing_grade: number
  average_formula: string
  rounding_precision: number
  recovery_enabled: boolean
  recovery_replaces: string
  conceptual_scales?: ConceptualScale[]
  instruments?: AssessmentInstrument[]
  created_at: string
  updated_at: string
}

export interface AssessmentInstrument {
  id: number
  name: string
  weight: number
  max_value: number
  order: number
}

export interface ConceptualScale {
  id: number
  code: string
  label: string
  numeric_equivalent: number
  passing: boolean
  order: number
}

export interface BulkGradeRequest {
  class_group_id: number
  teacher_assignment_id: number
  assessment_period_id: number
  assessment_instrument_id: number
  grades: { student_id: number; numeric_value?: number; conceptual_value?: string }[]
}
