import type { ClassAssignmentStatus, DocumentStatus, DocumentType, EnrollmentStatus, EnrollmentType, MovementType } from './enums'
import type { Student } from './people'
import type { AcademicYear, ClassGroup, School } from './school-structure'

export interface Enrollment {
  id: number
  student_id: number
  academic_year_id: number
  school_id: number
  enrollment_number: string
  enrollment_type: EnrollmentType
  enrollment_type_label: string
  status: EnrollmentStatus
  enrollment_date: string | null
  exit_date: string | null
  student?: Student
  academic_year?: AcademicYear
  school?: School
  class_assignments?: ClassAssignment[]
  movements?: EnrollmentMovement[]
  documents?: EnrollmentDocument[]
}

export interface ClassAssignment {
  id: number
  enrollment_id: number
  class_group_id: number
  status: ClassAssignmentStatus
  start_date: string | null
  end_date: string | null
  class_group?: ClassGroup
}

export interface EnrollmentMovement {
  id: number
  enrollment_id: number
  type: MovementType
  movement_date: string | null
  reason: string
  origin_school_id: number | null
  destination_school_id: number | null
  origin_school?: School
  destination_school?: School
  created_by: number
  created_at: string
}

export interface EnrollmentDocument {
  id?: number
  enrollment_id: number
  document_type: DocumentType
  document_type_label: string
  status: DocumentStatus
  status_label: string
  has_file: boolean
  original_filename: string | null
  mime_type: string | null
  file_size: number | null
  notes: string | null
  reviewed_by: number | null
  reviewed_at: string | null
  rejection_reason: string | null
  can_upload: boolean
  can_review: boolean
  created_at: string | null
  updated_at: string | null
}
