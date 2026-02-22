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

export interface ReportCardStudent {
  id: number
  name: string
  display_name: string
  birth_date: string | null
  class_group?: { id: number; label: string }
  enrollment_number?: string
  school_name?: string | null
  academic_year?: number | null
}

export interface ReportCardPeriod {
  id: number
  name: string
  number: number
}

export interface ReportCardPeriodData {
  average: number | null
  conceptual: string | null
  absences: number
}

export interface ReportCardSubject {
  teacher_assignment_id: number
  name: string
  teacher_name: string
  knowledge_area: string | null
  periods: Record<string, ReportCardPeriodData>
  final_average: number | null
  recovery_grade: number | null
  final_grade: number | null
  total_absences: number
  frequency_percentage: number | null
  status: string
}

export interface ReportCardDescriptive {
  experience_field: string
  period: string
  content: string
}

export interface ReportCardSummary {
  total_subjects: number
  passing_grade: number
  scale_max: number
  grade_type: string
}

export interface ReportCardResponse {
  student: ReportCardStudent
  assessment_periods: ReportCardPeriod[]
  subjects: ReportCardSubject[]
  descriptive_reports: ReportCardDescriptive[]
  summary: ReportCardSummary | null
}

export interface BulkGradeRequest {
  class_group_id: number
  teacher_assignment_id: number
  assessment_period_id: number
  assessment_instrument_id: number
  grades: { student_id: number; numeric_value?: number; conceptual_value?: string }[]
}
