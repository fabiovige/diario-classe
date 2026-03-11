<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { logout } = useAuth()

const userMenu = ref()

const userMenuItems = ref([
  {
    label: 'Meu Perfil',
    icon: 'pi pi-user',
    command: () => router.push('/profile'),
  },
  { separator: true },
  {
    label: 'Sair',
    icon: 'pi pi-sign-out',
    command: () => logout(),
  },
])

const SEGMENT_LABELS: Record<string, string> = {
  'dashboard': 'Dashboard',
  'identity': 'Identidade',
  'users': 'Usuarios',
  'roles': 'Perfis',
  'school-structure': 'Estrutura Escolar',
  'schools': 'Escolas',
  'academic-years': 'Anos Letivos',
  'shifts': 'Turnos',
  'grade-levels': 'Niveis de Ensino',
  'class-groups': 'Turmas',
  'people': 'Pessoas',
  'students': 'Alunos',
  'guardians': 'Responsaveis',
  'teachers': 'Professores',
  'enrollment': 'Matricula',
  'enrollments': 'Matriculas',
  'curriculum': 'Curriculo',
  'components': 'Componentes',
  'experience-fields': 'Campos de Experiencia',
  'assignments': 'Atribuicoes',
  'time-slots': 'Horarios de Aula',
  'schedules': 'Grade de Aulas',
  'academic-calendar': 'Calendario',
  'periods': 'Periodos Avaliativos',
  'my-classes': 'Minhas Aulas',
  'my-schedule': 'Minha Grade',
  'my-agenda': 'Agenda',
  'attendance': 'Frequencia',
  'justifications': 'Justificativas',
  'class-record': 'Diario de Classe',
  'assessment': 'Avaliacao',
  'grades': 'Notas',
  'descriptive': 'Relatorios Descritivos',
  'report-card': 'Boletim',
  'period-closing': 'Fechamento',
  'final-results': 'Resultado Final',
  'annual-results': 'Resultado Anual',
  'reports': 'Relatorios',
  'teacher-classes': 'Aulas do Professor',
  'new': 'Novo',
  'edit': 'Editar',
  'session': 'Sessao',
  'profile': 'Meu Perfil',
}

interface BreadcrumbItem {
  label: string
  to?: string
}

const crumbs = computed((): BreadcrumbItem[] => {
  const path = route.path
  if (path === '/dashboard' || path === '/') return []

  const segments = path.split('/').filter(Boolean)
  const result: BreadcrumbItem[] = []
  let accumulated = ''

  for (let i = 0; i < segments.length; i++) {
    const seg = segments[i]
    accumulated += '/' + seg

    if (/^\d+$/.test(seg)) continue

    const label = SEGMENT_LABELS[seg]
    if (!label) continue

    const isLast = i === segments.length - 1
    const resolved = router.resolve(accumulated)
    const hasRoute = resolved.matched.length > 0 && resolved.name !== 'not-found'

    result.push({
      label,
      to: isLast ? undefined : (hasRoute ? accumulated : undefined),
    })
  }

  return result
})

const pageTitle = computed(() => {
  const breadcrumb = route.meta.breadcrumb as string | undefined
  if (breadcrumb) return breadcrumb
  if (crumbs.value.length > 0) return crumbs.value[crumbs.value.length - 1].label
  return ''
})
</script>

<template>
  <div class="page-header">
    <div class="page-header-top">
      <nav v-if="crumbs.length > 0" class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-item">
          <i class="pi pi-home" />
        </router-link>
        <template v-for="(crumb, i) in crumbs" :key="i">
          <i class="pi pi-chevron-right breadcrumb-separator" />
          <router-link v-if="crumb.to" :to="crumb.to" class="breadcrumb-item">
            {{ crumb.label }}
          </router-link>
          <span v-else class="breadcrumb-item breadcrumb-current">
            {{ crumb.label }}
          </span>
        </template>
      </nav>

      <button class="user-dropdown" @click="userMenu.toggle($event)">
        <i class="pi pi-user user-dropdown-avatar" />
        <span class="user-dropdown-name">{{ authStore.userName }}</span>
        <i class="pi pi-chevron-down user-dropdown-chevron" />
      </button>
      <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
    </div>
    <h1 class="page-title">{{ pageTitle }}</h1>
  </div>
</template>
