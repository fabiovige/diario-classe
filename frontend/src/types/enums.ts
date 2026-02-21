export type RoleSlug = 'admin' | 'director' | 'secretary' | 'coordinator' | 'teacher' | 'guardian'

export type UserStatus = 'active' | 'inactive' | 'blocked'

export type AcademicYearStatus = 'planning' | 'active' | 'closing' | 'closed'

export type ShiftPeriod = 'morning' | 'afternoon' | 'evening' | 'full_time'

export type EducationLevel = 'early_childhood' | 'elementary_early' | 'elementary_late' | 'high_school'

export type EnrollmentStatus = 'active' | 'transferred' | 'cancelled' | 'completed' | 'abandoned'

export type MovementType = 'matricula_inicial' | 'transferencia_interna' | 'transferencia_externa' | 'abandono' | 'falecimento' | 'reclassificacao' | 'cancelamento'

export type ClassAssignmentStatus = 'active' | 'transferred' | 'cancelled'

export type EnrollmentType = 'new_enrollment' | 're_enrollment' | 'transfer_received'

export type AttendanceStatus = 'present' | 'absent' | 'justified_absence' | 'excused'

export type GradeType = 'numeric' | 'conceptual' | 'descriptive'

export type PeriodClosingStatus = 'pending' | 'in_validation' | 'approved' | 'closed'

export type PeriodType = 'bimester' | 'trimester' | 'semester'

export type FinalResultStatus = 'approved' | 'retained' | 'retained_by_absence' | 'council_approved'

export type DisabilityType = 'visual' | 'hearing' | 'physical' | 'intellectual' | 'autism' | 'gifted_talented' | 'multiple' | 'deafblind'

export type DocumentType = 'birth_certificate' | 'id_card' | 'proof_of_address' | 'school_transcript' | 'transfer_declaration' | 'vaccination_card' | 'photo_3x4' | 'sus_card' | 'nis_number' | 'medical_report'

export type DocumentStatus = 'not_uploaded' | 'pending_review' | 'approved' | 'rejected'

export type KnowledgeArea = 'linguagens' | 'matematica' | 'ciencias_natureza' | 'ciencias_humanas' | 'ensino_religioso' | 'parte_diversificada'
