import type { RoleSlug } from '@/types/enums'

interface MenuItem {
  label: string
  icon: string
  to?: string
  path?: string
  items?: MenuItem[]
}

const MENU_MAP: Record<RoleSlug, MenuItem[]> = {
  admin: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Administrativo', icon: 'pi pi-cog', path: '/admin', items: [
        {
          label: 'Identidade', icon: 'pi pi-users', path: '/identity', items: [
            { label: 'Usuarios', icon: 'pi pi-user', to: '/identity/users' },
            { label: 'Perfis', icon: 'pi pi-shield', to: '/identity/roles' },
          ],
        },
        {
          label: 'Estrutura Escolar', icon: 'pi pi-building', path: '/school-structure', items: [
            { label: 'Escolas', icon: 'pi pi-building', to: '/school-structure/schools' },
            { label: 'Anos Letivos', icon: 'pi pi-calendar', to: '/school-structure/academic-years' },
            { label: 'Turnos', icon: 'pi pi-clock', to: '/school-structure/shifts' },
            { label: 'Niveis de Ensino', icon: 'pi pi-list', to: '/school-structure/grade-levels' },
            { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
          ],
        },
        {
          label: 'Pessoas', icon: 'pi pi-id-card', path: '/people', items: [
            { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
            { label: 'Responsaveis', icon: 'pi pi-users', to: '/people/guardians' },
            { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
          ],
        },
        {
          label: 'Matricula', icon: 'pi pi-file-edit', path: '/enrollment', items: [
            { label: 'Matriculas', icon: 'pi pi-file', to: '/enrollment/enrollments' },
          ],
        },
        {
          label: 'Curriculo', icon: 'pi pi-book', path: '/curriculum', items: [
            { label: 'Componentes', icon: 'pi pi-bookmark', to: '/curriculum/components' },
            { label: 'Campos Experiencia', icon: 'pi pi-star', to: '/curriculum/experience-fields' },
            { label: 'Atribuicoes', icon: 'pi pi-link', to: '/curriculum/assignments' },
            { label: 'Horarios de Aula', icon: 'pi pi-clock', to: '/curriculum/time-slots' },
            { label: 'Grade de Aulas', icon: 'pi pi-table', to: '/curriculum/schedules' },
          ],
        },
        {
          label: 'Calendario', icon: 'pi pi-calendar-plus', path: '/calendar', items: [
            { label: 'Agenda', icon: 'pi pi-calendar-clock', to: '/my-agenda' },
            { label: 'Periodos Avaliativos', icon: 'pi pi-calendar', to: '/academic-calendar/periods' },
          ],
        },
      ],
    },
    {
      label: 'Pedagogico', icon: 'pi pi-graduation-cap', path: '/pedagogico', items: [
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
        {
          label: 'Avaliacao', icon: 'pi pi-chart-bar', path: '/assessment', items: [
            { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
            { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
            { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
          ],
        },
        { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
        { label: 'Resultado Anual', icon: 'pi pi-chart-line', to: '/period-closing/annual-results' },
      ],
    },
    {
      label: 'Relatorios', icon: 'pi pi-file-export', path: '/reports', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/reports/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/reports/class-record' },
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/reports/teacher-classes' },
      ],
    },
  ],
  director: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Administrativo', icon: 'pi pi-cog', path: '/admin', items: [
        {
          label: 'Estrutura Escolar', icon: 'pi pi-building', path: '/school-structure', items: [
            { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
          ],
        },
        {
          label: 'Pessoas', icon: 'pi pi-id-card', path: '/people', items: [
            { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
            { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
          ],
        },
      ],
    },
    {
      label: 'Pedagogico', icon: 'pi pi-graduation-cap', path: '/pedagogico', items: [
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
        { label: 'Grade de Aulas', icon: 'pi pi-table', to: '/curriculum/schedules' },
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
        {
          label: 'Avaliacao', icon: 'pi pi-chart-bar', path: '/assessment', items: [
            { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
            { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
            { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
          ],
        },
        { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
        { label: 'Resultado Anual', icon: 'pi pi-chart-line', to: '/period-closing/annual-results' },
      ],
    },
    {
      label: 'Relatorios', icon: 'pi pi-file-export', path: '/reports', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/reports/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/reports/class-record' },
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/reports/teacher-classes' },
      ],
    },
  ],
  coordinator: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Administrativo', icon: 'pi pi-cog', path: '/admin', items: [
        {
          label: 'Pessoas', icon: 'pi pi-id-card', path: '/people', items: [
            { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
            { label: 'Professores', icon: 'pi pi-briefcase', to: '/people/teachers' },
          ],
        },
      ],
    },
    {
      label: 'Pedagogico', icon: 'pi pi-graduation-cap', path: '/pedagogico', items: [
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/my-classes' },
        { label: 'Grade de Aulas', icon: 'pi pi-table', to: '/curriculum/schedules' },
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
        {
          label: 'Avaliacao', icon: 'pi pi-chart-bar', path: '/assessment', items: [
            { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
            { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
            { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
          ],
        },
        { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
        { label: 'Resultado Anual', icon: 'pi pi-chart-line', to: '/period-closing/annual-results' },
      ],
    },
    {
      label: 'Relatorios', icon: 'pi pi-file-export', path: '/reports', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/reports/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/reports/class-record' },
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/reports/teacher-classes' },
      ],
    },
  ],
  secretary: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    {
      label: 'Administrativo', icon: 'pi pi-cog', path: '/admin', items: [
        {
          label: 'Estrutura Escolar', icon: 'pi pi-building', path: '/school-structure', items: [
            { label: 'Turmas', icon: 'pi pi-th-large', to: '/school-structure/class-groups' },
          ],
        },
        {
          label: 'Pessoas', icon: 'pi pi-id-card', path: '/people', items: [
            { label: 'Alunos', icon: 'pi pi-user', to: '/people/students' },
            { label: 'Responsaveis', icon: 'pi pi-users', to: '/people/guardians' },
          ],
        },
        {
          label: 'Matricula', icon: 'pi pi-file-edit', path: '/enrollment', items: [
            { label: 'Matriculas', icon: 'pi pi-file', to: '/enrollment/enrollments' },
          ],
        },
      ],
    },
    {
      label: 'Pedagogico', icon: 'pi pi-graduation-cap', path: '/pedagogico', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
      ],
    },
    {
      label: 'Relatorios', icon: 'pi pi-file-export', path: '/reports', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/reports/attendance' },
      ],
    },
  ],
  teacher: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
    { label: 'Minhas Aulas', icon: 'pi pi-book', to: '/my-classes' },
    { label: 'Minha Grade', icon: 'pi pi-table', to: '/my-schedule' },
    { label: 'Agenda', icon: 'pi pi-calendar-clock', to: '/my-agenda' },
    { label: 'Frequencia', icon: 'pi pi-check-square', to: '/attendance' },
    { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/class-record' },
    {
      label: 'Avaliacao', icon: 'pi pi-chart-bar', path: '/assessment', items: [
        { label: 'Lancamento de Notas', icon: 'pi pi-pencil', to: '/assessment' },
        { label: 'Relatorios Descritivos', icon: 'pi pi-file-edit', to: '/assessment/descriptive' },
        { label: 'Consulta de Notas', icon: 'pi pi-list', to: '/assessment/grades' },
      ],
    },
    { label: 'Fechamento', icon: 'pi pi-lock', to: '/period-closing' },
    {
      label: 'Relatorios', icon: 'pi pi-file-export', path: '/reports', items: [
        { label: 'Frequencia', icon: 'pi pi-check-square', to: '/reports/attendance' },
        { label: 'Diario de Classe', icon: 'pi pi-pencil', to: '/reports/class-record' },
        { label: 'Aulas do Professor', icon: 'pi pi-book', to: '/reports/teacher-classes' },
      ],
    },
  ],
  guardian: [
    { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
  ],
}

export function getMenuByRole(role: RoleSlug): MenuItem[] {
  return MENU_MAP[role] ?? []
}
