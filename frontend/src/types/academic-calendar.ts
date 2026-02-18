import type { PeriodType } from './enums'
import type { AcademicYear } from './school-structure'

export interface AssessmentPeriod {
  id: number
  academic_year_id: number
  type: PeriodType
  number: number
  name: string
  start_date: string | null
  end_date: string | null
  status: string
  academic_year?: AcademicYear
  created_at: string
  updated_at: string
}
