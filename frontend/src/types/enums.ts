export type RoleSlug = 'admin' | 'director' | 'secretary' | 'coordinator' | 'teacher' | 'guardian'

export type UserStatus = 'active' | 'inactive' | 'blocked'

export type AcademicYearStatus = 'planning' | 'active' | 'closing' | 'closed'

export type ShiftPeriod = 'morning' | 'afternoon' | 'evening' | 'full_time'

export type EducationLevel = 'early_childhood' | 'elementary' | 'high_school'

export type EnrollmentStatus = 'active' | 'transferred' | 'cancelled' | 'completed'

export type MovementType = 'enrollment' | 'transfer_in' | 'transfer_out' | 'class_assignment' | 'cancellation'

export type AttendanceStatus = 'present' | 'absent' | 'justified_absence' | 'excused'

export type GradeType = 'numeric' | 'conceptual' | 'descriptive'

export type PeriodClosingStatus = 'pending' | 'in_validation' | 'approved' | 'closed'

export type PeriodType = 'bimester' | 'trimester' | 'semester'

export type FinalResultStatus = 'approved' | 'retained' | 'retained_by_absence' | 'council_approved'

export type DisabilityType = 'visual' | 'hearing' | 'physical' | 'intellectual' | 'autism' | 'gifted_talented' | 'multiple' | 'deafblind'
