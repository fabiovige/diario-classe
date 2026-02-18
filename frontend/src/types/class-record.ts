import type { ClassGroup } from './school-structure'
import type { TeacherAssignment } from './curriculum'

export interface LessonRecord {
  id: number
  class_group_id: number
  teacher_assignment_id: number
  date: string | null
  content: string
  methodology: string
  observations: string
  class_count: number
  recorded_by: number
  class_group?: ClassGroup
  teacher_assignment?: TeacherAssignment
  created_at: string
  updated_at: string
}
