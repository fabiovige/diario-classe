import type { RoleSlug } from '@/types/enums'

interface MenuItem {
  label: string
  icon: string
  to?: string
  items?: MenuItem[]
}

const MENU_MAP: Record<RoleSlug, MenuItem[]> = {
  admin: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Identidade', icon: 'pi pi-users', items: [
        { label: 'Usuarios', icon: 'pi pi-user', to: '/identity/users' },
        { label: 'Perfis', icon: 'pi pi-shield', to: '/identity/roles' },
      ],
    },
    {
      label: 'Estrutura Escolar', icon: 'pi pi-building', items: [
        { label: 'Escolas', icon: 'pi pi-building', to: '/school-structure/schools' },
        { label: 'Anos Letivos', icon: 'pi pi-calendar', to: '/school-structure/academic-years' },
        { label: 'Turnos', icon: 'pi pi-clock', to: '/school-structure/shifts' },
        { label: 'Niveis de Ensino', icon: 'pi pi-list', to: '/school-structure/grade-levels' },
        { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
      ],
    },
    {
      label: 'Pessoas', icon: 'pi pi-id-card', items: [
        { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
        { label: 'Responsaveis', icon: 'pi pi-users', to: '/people/guardians' },
        { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
      ],
    },
    {
      label: 'Matricula', icon: 'pi pi-file-edit', items: [
        { label: 'Matriculas', icon: 'pi pi-file', to: '/enrollment/enrollments' },
      ],
    },
    {
      label: 'Curriculo', icon: 'pi pi-book', items: [
        { label: 'Componentes', icon: 'pi pi-bookmark', to: '/curriculum/components' },
        { label: 'Campos Experiencia', icon: 'pi pi-star', to: '/curriculum/experience-fields' },
        { label: 'Atribuicoes', icon: 'pi pi-link', to: '/curriculum/assignments' },
      ],
    },
    {
      label: 'Calendario', icon: 'pi pi-calendar-plus', items: [
        { label: 'Periodos Avaliativos', icon: 'pi pi-calendar', to: '/academic-calendar/periods' },
      ],
    },
    { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
    { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
    {
      label: 'Avaliacao', icon: 'pi pi-chart-bar', items: [
        { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
        { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
        { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
      ],
    },
    { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
  ],
  director: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Estrutura Escolar', icon: 'pi pi-building', items: [
        { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
      ],
    },
    {
      label: 'Pessoas', icon: 'pi pi-id-card', items: [
        { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
        { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
      ],
    },
    { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
    {
      label: 'Avaliacao', icon: 'pi pi-chart-bar', items: [
        { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
        { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
        { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
      ],
    },
    { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
  ],
  coordinator: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Pessoas', icon: 'pi pi-id-card', items: [
        { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
        { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
      ],
    },
    { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
    { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
    {
      label: 'Avaliacao', icon: 'pi pi-chart-bar', items: [
        { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
        { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
        { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
      ],
    },
    { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
  ],
  secretary: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Estrutura Escolar', icon: 'pi pi-building', items: [
        { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
      ],
    },
    {
      label: 'Pessoas', icon: 'pi pi-id-card', items: [
        { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
        { label: 'Responsaveis', icon: 'pi pi-users', to: '/people/guardians' },
      ],
    },
    {
      label: 'Matricula', icon: 'pi pi-file-edit', items: [
        { label: 'Matriculas', icon: 'pi pi-file', to: '/enrollment/enrollments' },
      ],
    },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
  ],
  teacher: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    { label: 'Minhas Aulas', icon: 'pi pi-book', to: '/my-classes' },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
    { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
    {
      label: 'Avaliacao', icon: 'pi pi-chart-bar', items: [
        { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
        { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
        { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
      ],
    },
    { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
  ],
  guardian: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
  ],
}

export function getMenuByRole(role: RoleSlug): MenuItem[] {
  return MENU_MAP[role] ?? []
}
