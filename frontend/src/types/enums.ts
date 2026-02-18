export type RoleSlug = 'admin' | 'director' | 'secretary' | 'coordinator' | 'teacher' | 'guardian'

export type UserStatus = 'active' | 'inactive' | 'blocked'

export type AcademicYearStatus = 'planning' | 'active' | 'closed'

export type ShiftPeriod = 'morning' | 'afternoon' | 'evening' | 'full_time'

export type EducationLevel = 'infantil' | 'fundamental_1' | 'fundamental_2'

export type EnrollmentStatus = 'active' | 'transferred' | 'cancelled' | 'completed'

export type MovementType = 'enrollment' | 'transfer_in' | 'transfer_out' | 'class_assignment' | 'cancellation'

export type AttendanceStatus = 'present' | 'absent' | 'justified' | 'dispensed'

export type GradeType = 'numeric' | 'conceptual' | 'descriptive'

export type PeriodClosingStatus = 'pending' | 'in_validation' | 'approved' | 'closed'

export type PeriodType = 'bimester' | 'trimester' | 'semester'

export type FinalResultStatus = 'approved' | 'retained' | 'retained_by_absence' | 'council_approved'
