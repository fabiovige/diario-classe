import type {
  RoleSlug,
  UserStatus,
  AcademicYearStatus,
  ShiftPeriod,
  EducationLevel,
  EnrollmentStatus,
  MovementType,
  ClassAssignmentStatus,
  EnrollmentType,
  AttendanceStatus,
  GradeType,
  PeriodClosingStatus,
  PeriodType,
  FinalResultStatus,
  DisabilityType,
  DocumentType,
  DocumentStatus,
  KnowledgeArea,
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
  closing: 'Em Encerramento',
  closed: 'Encerrado',
}

const SHIFT_PERIOD_LABELS: Record<ShiftPeriod, string> = {
  morning: 'Manha',
  afternoon: 'Tarde',
  evening: 'Noite',
  full_time: 'Integral',
}

const EDUCATION_LEVEL_LABELS: Record<EducationLevel, string> = {
  early_childhood: 'Ed. Infantil',
  elementary_early: 'Fund. Anos Iniciais',
  elementary_late: 'Fund. Anos Finais',
  high_school: 'Ensino Medio',
}

const ENROLLMENT_STATUS_LABELS: Record<EnrollmentStatus, string> = {
  active: 'Ativa',
  transferred: 'Transferida',
  cancelled: 'Cancelada',
  completed: 'Concluida',
  abandoned: 'Abandonada',
}

const MOVEMENT_TYPE_LABELS: Record<MovementType, string> = {
  matricula_inicial: 'Matricula Inicial',
  transferencia_interna: 'Transferencia Interna',
  transferencia_externa: 'Transferencia Externa',
  abandono: 'Abandono',
  falecimento: 'Falecimento',
  reclassificacao: 'Reclassificacao',
  cancelamento: 'Cancelamento',
}

const CLASS_ASSIGNMENT_STATUS_LABELS: Record<ClassAssignmentStatus, string> = {
  active: 'Ativa',
  transferred: 'Transferida',
  cancelled: 'Cancelada',
}

const ENROLLMENT_TYPE_LABELS: Record<EnrollmentType, string> = {
  new_enrollment: 'Matricula Nova',
  re_enrollment: 'Rematricula',
  transfer_received: 'Transferencia Recebida',
}

const ATTENDANCE_STATUS_LABELS: Record<AttendanceStatus, string> = {
  present: 'Presente',
  absent: 'Ausente',
  justified_absence: 'Falta Justificada',
  excused: 'Dispensado',
}

const GRADE_TYPE_LABELS: Record<GradeType, string> = {
  numeric: 'Numerico',
  conceptual: 'Conceitual',
  descriptive: 'Descritivo',
}

const PERIOD_CLOSING_STATUS_LABELS: Record<PeriodClosingStatus, string> = {
  pending: 'Pendente',
  in_validation: 'Em Validacao',
  approved: 'Aprovado',
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

const DISABILITY_TYPE_LABELS: Record<DisabilityType, string> = {
  visual: 'Deficiencia Visual',
  hearing: 'Deficiencia Auditiva',
  physical: 'Deficiencia Fisica',
  intellectual: 'Deficiencia Intelectual',
  autism: 'Transtorno do Espectro Autista (TEA)',
  gifted_talented: 'Altas Habilidades/Superdotacao',
  multiple: 'Deficiencia Multipla',
  deafblind: 'Surdocegueira',
}

export function classAssignmentStatusLabel(status: ClassAssignmentStatus): string {
  return CLASS_ASSIGNMENT_STATUS_LABELS[status] ?? status
}

export function enrollmentTypeLabel(type: EnrollmentType): string {
  return ENROLLMENT_TYPE_LABELS[type] ?? type
}

export function disabilityTypeLabel(type: DisabilityType): string {
  return DISABILITY_TYPE_LABELS[type] ?? type
}

const DOCUMENT_TYPE_LABELS: Record<DocumentType, string> = {
  birth_certificate: 'Certidao de Nascimento',
  id_card: 'RG / Documento de Identidade',
  proof_of_address: 'Comprovante de Endereco',
  school_transcript: 'Historico Escolar',
  transfer_declaration: 'Declaracao de Transferencia',
  vaccination_card: 'Carteira de Vacinacao',
  photo_3x4: 'Foto 3x4',
  sus_card: 'Cartao SUS',
  nis_number: 'Numero NIS',
  medical_report: 'Laudo Medico',
}

export function documentTypeLabel(type: DocumentType): string {
  return DOCUMENT_TYPE_LABELS[type] ?? type
}

const DOCUMENT_STATUS_LABELS: Record<DocumentStatus, string> = {
  not_uploaded: 'Nao Enviado',
  pending_review: 'Aguardando Revisao',
  approved: 'Aprovado',
  rejected: 'Rejeitado',
}

export function documentStatusLabel(status: DocumentStatus): string {
  return DOCUMENT_STATUS_LABELS[status] ?? status
}

const KNOWLEDGE_AREA_LABELS: Record<KnowledgeArea, string> = {
  linguagens: 'Linguagens',
  matematica: 'Matematica',
  ciencias_natureza: 'Ciencias da Natureza',
  ciencias_humanas: 'Ciencias Humanas',
  ensino_religioso: 'Ensino Religioso',
  parte_diversificada: 'Parte Diversificada',
}

export const KNOWLEDGE_AREA_OPTIONS = Object.entries(KNOWLEDGE_AREA_LABELS).map(([value, label]) => ({ value, label }))

export function knowledgeAreaLabel(area: KnowledgeArea): string {
  return KNOWLEDGE_AREA_LABELS[area] ?? area
}
