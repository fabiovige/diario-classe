import type {
  RoleSlug,
  UserStatus,
  AcademicYearStatus,
  ShiftPeriod,
  EducationLevel,
  EnrollmentStatus,
  MovementType,
  AttendanceStatus,
  GradeType,
  PeriodClosingStatus,
  PeriodType,
  FinalResultStatus,
} from '@/types/enums'

const ROLE_LABELS: Record<RoleSlug, string> = {
  admin: 'Administrador',
  director: 'Diretor(a)',
  secretary: 'Secretario(a)',
  coordinator: 'Coordenador(a)',
  teacher: 'Professor(a)',
  guardian: 'Responsavel',
}

const USER_STATUS_LABELS: Record<UserStatus, string> = {
  active: 'Ativo',
  inactive: 'Inativo',
  blocked: 'Bloqueado',
}

const ACADEMIC_YEAR_STATUS_LABELS: Record<AcademicYearStatus, string> = {
  planning: 'Planejamento',
  active: 'Ativo',
  closed: 'Encerrado',
}

const SHIFT_PERIOD_LABELS: Record<ShiftPeriod, string> = {
  morning: 'Manha',
  afternoon: 'Tarde',
  evening: 'Noite',
  full_time: 'Integral',
}

const EDUCATION_LEVEL_LABELS: Record<EducationLevel, string> = {
  infantil: 'Ed. Infantil',
  fundamental_1: 'Fundamental I',
  fundamental_2: 'Fundamental II',
}

const ENROLLMENT_STATUS_LABELS: Record<EnrollmentStatus, string> = {
  active: 'Ativa',
  transferred: 'Transferida',
  cancelled: 'Cancelada',
  completed: 'Concluida',
}

const MOVEMENT_TYPE_LABELS: Record<MovementType, string> = {
  enrollment: 'Matricula',
  transfer_in: 'Transferencia (entrada)',
  transfer_out: 'Transferencia (saida)',
  class_assignment: 'Enturmacao',
  cancellation: 'Cancelamento',
}

const ATTENDANCE_STATUS_LABELS: Record<AttendanceStatus, string> = {
  present: 'Presente',
  absent: 'Ausente',
  justified: 'Justificado',
  dispensed: 'Dispensado',
}

const GRADE_TYPE_LABELS: Record<GradeType, string> = {
  numeric: 'Numerico',
  conceptual: 'Conceitual',
  descriptive: 'Descritivo',
}

const PERIOD_CLOSING_STATUS_LABELS: Record<PeriodClosingStatus, string> = {
  pending: 'Pendente',
  submitted: 'Enviado',
  validated: 'Validado',
  rejected: 'Rejeitado',
  closed: 'Fechado',
}

const PERIOD_TYPE_LABELS: Record<PeriodType, string> = {
  bimester: 'Bimestre',
  trimester: 'Trimestre',
  semester: 'Semestre',
}

const FINAL_RESULT_STATUS_LABELS: Record<FinalResultStatus, string> = {
  approved: 'Aprovado',
  retained: 'Retido',
  retained_by_absence: 'Retido por Falta',
  council_approved: 'Aprovado pelo Conselho',
}

export function roleLabel(slug: RoleSlug): string {
  return ROLE_LABELS[slug] ?? slug
}

export function userStatusLabel(status: UserStatus): string {
  return USER_STATUS_LABELS[status] ?? status
}

export function academicYearStatusLabel(status: AcademicYearStatus): string {
  return ACADEMIC_YEAR_STATUS_LABELS[status] ?? status
}

export function shiftPeriodLabel(period: ShiftPeriod): string {
  return SHIFT_PERIOD_LABELS[period] ?? period
}

export function educationLevelLabel(level: EducationLevel): string {
  return EDUCATION_LEVEL_LABELS[level] ?? level
}

export function enrollmentStatusLabel(status: EnrollmentStatus): string {
  return ENROLLMENT_STATUS_LABELS[status] ?? status
}

export function movementTypeLabel(type: MovementType): string {
  return MOVEMENT_TYPE_LABELS[type] ?? type
}

export function attendanceStatusLabel(status: AttendanceStatus): string {
  return ATTENDANCE_STATUS_LABELS[status] ?? status
}

export function gradeTypeLabel(type: GradeType): string {
  return GRADE_TYPE_LABELS[type] ?? type
}

export function periodClosingStatusLabel(status: PeriodClosingStatus): string {
  return PERIOD_CLOSING_STATUS_LABELS[status] ?? status
}

export function periodTypeLabel(type: PeriodType): string {
  return PERIOD_TYPE_LABELS[type] ?? type
}

export function finalResultStatusLabel(status: FinalResultStatus): string {
  return FINAL_RESULT_STATUS_LABELS[status] ?? status
}
