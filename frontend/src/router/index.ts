import { createRouter, createWebHistory } from 'vue-router'
import { authGuard, roleGuard } from './guards'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/modules/auth/pages/LoginPage.vue'),
      meta: { layout: 'auth', requiresAuth: false },
    },
    {
      path: '/',
      redirect: '/dashboard',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/modules/dashboard/pages/DashboardRouter.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Dashboard' },
    },

    // Identity
    {
      path: '/identity/users',
      name: 'users',
      component: () => import('@/modules/identity/pages/UserListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Usuarios' },
    },
    {
      path: '/identity/users/new',
      name: 'user-create',
      component: () => import('@/modules/identity/pages/UserFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Usuario' },
    },
    {
      path: '/identity/users/:id/edit',
      name: 'user-edit',
      component: () => import('@/modules/identity/pages/UserFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Usuario' },
    },
    {
      path: '/identity/roles',
      name: 'roles',
      component: () => import('@/modules/identity/pages/RoleListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Perfis' },
    },
    {
      path: '/identity/roles/new',
      name: 'role-create',
      component: () => import('@/modules/identity/pages/RoleFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Perfil' },
    },
    {
      path: '/identity/roles/:id/edit',
      name: 'role-edit',
      component: () => import('@/modules/identity/pages/RoleFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Perfil' },
    },

    // SchoolStructure
    {
      path: '/school-structure/schools',
      name: 'schools',
      component: () => import('@/modules/school-structure/pages/SchoolListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Escolas' },
    },
    {
      path: '/school-structure/schools/new',
      name: 'school-create',
      component: () => import('@/modules/school-structure/pages/SchoolFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Nova Escola' },
    },
    {
      path: '/school-structure/schools/:id/edit',
      name: 'school-edit',
      component: () => import('@/modules/school-structure/pages/SchoolFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Escola' },
    },
    {
      path: '/school-structure/academic-years',
      name: 'academic-years',
      component: () => import('@/modules/school-structure/pages/AcademicYearListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Anos Letivos' },
    },
    {
      path: '/school-structure/academic-years/new',
      name: 'academic-year-create',
      component: () => import('@/modules/school-structure/pages/AcademicYearFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Ano Letivo' },
    },
    {
      path: '/school-structure/academic-years/:id/edit',
      name: 'academic-year-edit',
      component: () => import('@/modules/school-structure/pages/AcademicYearFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Ano Letivo' },
    },
    {
      path: '/school-structure/shifts',
      name: 'shifts',
      component: () => import('@/modules/school-structure/pages/ShiftListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Turnos' },
    },
    {
      path: '/school-structure/shifts/new',
      name: 'shift-create',
      component: () => import('@/modules/school-structure/pages/ShiftFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Turno' },
    },
    {
      path: '/school-structure/shifts/:id/edit',
      name: 'shift-edit',
      component: () => import('@/modules/school-structure/pages/ShiftFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Turno' },
    },
    {
      path: '/school-structure/grade-levels',
      name: 'grade-levels',
      component: () => import('@/modules/school-structure/pages/GradeLevelListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Niveis de Ensino' },
    },
    {
      path: '/school-structure/class-groups',
      name: 'class-groups',
      component: () => import('@/modules/school-structure/pages/ClassGroupListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director', 'secretary'], breadcrumb: 'Turmas' },
    },
    {
      path: '/school-structure/class-groups/new',
      name: 'class-group-create',
      component: () => import('@/modules/school-structure/pages/ClassGroupFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Nova Turma' },
    },
    {
      path: '/school-structure/class-groups/:id/edit',
      name: 'class-group-edit',
      component: () => import('@/modules/school-structure/pages/ClassGroupFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Turma' },
    },

    // People
    {
      path: '/people/students',
      name: 'students',
      component: () => import('@/modules/people/pages/StudentListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director', 'coordinator', 'secretary'], breadcrumb: 'Alunos' },
    },
    {
      path: '/people/students/new',
      name: 'student-create',
      component: () => import('@/modules/people/pages/StudentFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Novo Aluno' },
    },
    {
      path: '/people/students/:id',
      name: 'student-detail',
      component: () => import('@/modules/people/pages/StudentDetailPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Detalhes do Aluno' },
    },
    {
      path: '/people/students/:id/edit',
      name: 'student-edit',
      component: () => import('@/modules/people/pages/StudentFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Editar Aluno' },
    },
    {
      path: '/people/guardians',
      name: 'guardians',
      component: () => import('@/modules/people/pages/GuardianListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Responsaveis' },
    },
    {
      path: '/people/guardians/new',
      name: 'guardian-create',
      component: () => import('@/modules/people/pages/GuardianFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Novo Responsavel' },
    },
    {
      path: '/people/guardians/:id/edit',
      name: 'guardian-edit',
      component: () => import('@/modules/people/pages/GuardianFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Editar Responsavel' },
    },
    {
      path: '/people/teachers',
      name: 'teachers',
      component: () => import('@/modules/people/pages/TeacherListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director', 'coordinator'], breadcrumb: 'Professores' },
    },
    {
      path: '/people/teachers/new',
      name: 'teacher-create',
      component: () => import('@/modules/people/pages/TeacherFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Professor' },
    },
    {
      path: '/people/teachers/:id/edit',
      name: 'teacher-edit',
      component: () => import('@/modules/people/pages/TeacherFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Professor' },
    },

    // Enrollment
    {
      path: '/enrollment/enrollments',
      name: 'enrollments',
      component: () => import('@/modules/enrollment/pages/EnrollmentListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Matriculas' },
    },
    {
      path: '/enrollment/enrollments/new',
      name: 'enrollment-create',
      component: () => import('@/modules/enrollment/pages/EnrollmentFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'secretary'], breadcrumb: 'Nova Matricula' },
    },
    {
      path: '/enrollment/enrollments/:id',
      name: 'enrollment-detail',
      component: () => import('@/modules/enrollment/pages/EnrollmentDetailPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Detalhes da Matricula' },
    },

    // Curriculum
    {
      path: '/curriculum/components',
      name: 'curricular-components',
      component: () => import('@/modules/curriculum/pages/CurricularComponentListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Componentes Curriculares' },
    },
    {
      path: '/curriculum/experience-fields',
      name: 'experience-fields',
      component: () => import('@/modules/curriculum/pages/ExperienceFieldListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Campos de Experiencia' },
    },
    {
      path: '/curriculum/assignments',
      name: 'teacher-assignments',
      component: () => import('@/modules/curriculum/pages/TeacherAssignmentListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director'], breadcrumb: 'Atribuicoes' },
    },

    // AcademicCalendar
    {
      path: '/academic-calendar/periods',
      name: 'assessment-periods',
      component: () => import('@/modules/academic-calendar/pages/AssessmentPeriodListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Periodos Avaliativos' },
    },
    {
      path: '/academic-calendar/periods/new',
      name: 'assessment-period-create',
      component: () => import('@/modules/academic-calendar/pages/AssessmentPeriodFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Novo Periodo' },
    },
    {
      path: '/academic-calendar/periods/:id/edit',
      name: 'assessment-period-edit',
      component: () => import('@/modules/academic-calendar/pages/AssessmentPeriodFormPage.vue'),
      meta: { requiresAuth: true, roles: ['admin'], breadcrumb: 'Editar Periodo' },
    },

    // Attendance
    {
      path: '/attendance',
      name: 'attendance',
      component: () => import('@/modules/attendance/pages/AttendanceBulkPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director', 'coordinator', 'teacher'], breadcrumb: 'Frequencia' },
    },
    {
      path: '/attendance/class/:classGroupId',
      name: 'attendance-class',
      component: () => import('@/modules/attendance/pages/AttendanceClassGridPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Mapa de Frequencia' },
    },
    {
      path: '/attendance/student/:studentId',
      name: 'attendance-student',
      component: () => import('@/modules/attendance/pages/AttendanceStudentPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Frequencia do Aluno' },
    },
    {
      path: '/attendance/justifications',
      name: 'absence-justifications',
      component: () => import('@/modules/attendance/pages/AbsenceJustificationPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Justificativas' },
    },

    // ClassRecord
    {
      path: '/class-record',
      name: 'class-record',
      component: () => import('@/modules/class-record/pages/LessonRecordListPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'coordinator', 'teacher'], breadcrumb: 'Diario de Classe' },
    },
    {
      path: '/class-record/new',
      name: 'lesson-record-create',
      component: () => import('@/modules/class-record/pages/LessonRecordFormPage.vue'),
      meta: { requiresAuth: true, roles: ['teacher'], breadcrumb: 'Novo Registro' },
    },
    {
      path: '/class-record/:id/edit',
      name: 'lesson-record-edit',
      component: () => import('@/modules/class-record/pages/LessonRecordFormPage.vue'),
      meta: { requiresAuth: true, roles: ['teacher'], breadcrumb: 'Editar Registro' },
    },

    // Assessment
    {
      path: '/assessment',
      name: 'assessment',
      component: () => import('@/modules/assessment/pages/GradeBulkPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'coordinator', 'teacher'], breadcrumb: 'Avaliacao' },
    },
    {
      path: '/assessment/grades',
      name: 'grade-list',
      component: () => import('@/modules/assessment/pages/GradeListPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Notas' },
    },
    {
      path: '/assessment/descriptive',
      name: 'descriptive-reports',
      component: () => import('@/modules/assessment/pages/DescriptiveReportPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Relatorio Descritivo' },
    },
    {
      path: '/assessment/report-card/:studentId',
      name: 'report-card',
      component: () => import('@/modules/assessment/pages/ReportCardPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Boletim' },
    },

    // PeriodClosing
    {
      path: '/period-closing',
      name: 'period-closing',
      component: () => import('@/modules/period-closing/pages/ClosingDashboardPage.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'director', 'coordinator', 'teacher'], breadcrumb: 'Fechamento' },
    },
    {
      path: '/period-closing/:id',
      name: 'closing-detail',
      component: () => import('@/modules/period-closing/pages/ClosingDetailPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Detalhe do Fechamento' },
    },
    {
      path: '/period-closing/final-results',
      name: 'final-results',
      component: () => import('@/modules/period-closing/pages/FinalResultPage.vue'),
      meta: { requiresAuth: true, breadcrumb: 'Resultado Final' },
    },

    // 404
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/modules/auth/pages/LoginPage.vue'),
      meta: { layout: 'auth', requiresAuth: false },
    },
  ],
})

router.beforeEach(authGuard)
router.beforeEach(roleGuard)

export default router
