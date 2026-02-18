<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import Paginator from 'primevue/paginator'
import StatusBadge from '@/shared/components/StatusBadge.vue'
import EmptyState from '@/shared/components/EmptyState.vue'
import FormDialog from '@/shared/components/FormDialog.vue'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import type { TeacherAssignment, CurricularComponent, ExperienceField } from '@/types/curriculum'
import type { Teacher } from '@/types/people'
import type { ClassGroup } from '@/types/school-structure'

const toast = useToast()

const items = ref<TeacherAssignment[]>([])
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const perPage = ref(15)
const search = ref('')

const dialogVisible = ref(false)
const dialogLoading = ref(false)

const teachers = ref<Teacher[]>([])
const classGroups = ref<ClassGroup[]>([])
const components = ref<CurricularComponent[]>([])
const experienceFields = ref<ExperienceField[]>([])

const form = ref({
  teacher_id: null as number | null,
  class_group_id: null as number | null,
  curricular_component_id: null as number | null,
  experience_field_id: null as number | null,
})

async function loadData() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: currentPage.value, per_page: perPage.value }
    if (search.value) params.search = search.value
    const response = await curriculumService.getAssignments(params)
    items.value = response.data
    totalRecords.value = response.meta.total
  } catch {
    toast.error('Erro ao carregar atribuicoes')
  } finally {
    loading.value = false
  }
}

async function loadAuxData() {
  try {
    const [teachersRes, classGroupsRes, componentsRes, fieldsRes] = await Promise.all([
      peopleService.getTeachers({ per_page: 100 }),
      schoolStructureService.getClassGroups({ per_page: 100 }),
      curriculumService.getComponents({ per_page: 100 }),
      curriculumService.getExperienceFields({ per_page: 100 }),
    ])
    teachers.value = teachersRes.data
    classGroups.value = classGroupsRes.data
    components.value = componentsRes.data
    experienceFields.value = fieldsRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

function openDialog() {
  form.value = { teacher_id: null, class_group_id: null, curricular_component_id: null, experience_field_id: null }
  dialogVisible.value = true
}

async function handleSave() {
  dialogLoading.value = true
  try {
    await curriculumService.createAssignment(form.value)
    toast.success('Atribuicao criada')
    dialogVisible.value = false
    loadData()
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao criar atribuicao')
  } finally {
    dialogLoading.value = false
  }
}

function onPageChange(event: { page: number; rows: number }) {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadData()
}

function onSearch() {
  currentPage.value = 1
  loadData()
}

function teacherDisplayName(teacher: Teacher): string {
  return teacher.user?.name ?? `Professor #${teacher.id}`
}

onMounted(async () => {
  await Promise.all([loadData(), loadAuxData()])
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">Atribuicoes de Professores</h1>

    <div class="card-section">
      <Toolbar class="mb-3">
        <template #start>
          <InputText v-model="search" placeholder="Buscar atribuicao..." @keyup.enter="onSearch" />
          <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
        </template>
        <template #end>
          <Button label="Nova Atribuicao" icon="pi pi-plus" @click="openDialog" />
        </template>
      </Toolbar>

      <EmptyState v-if="!loading && items.length === 0" message="Nenhuma atribuicao encontrada" />

      <DataTable v-if="items.length > 0" :value="items" :loading="loading" stripedRows responsiveLayout="scroll">
        <Column header="Professor">
          <template #body="{ data }">
            {{ data.teacher?.user?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Turma">
          <template #body="{ data }">
            {{ data.class_group?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Componente/Campo">
          <template #body="{ data }">
            {{ data.curricular_component?.name ?? data.experience_field?.name ?? '--' }}
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.active ? 'active' : 'inactive'" :label="data.active ? 'Ativo' : 'Inativo'" />
          </template>
        </Column>
      </DataTable>

      <Paginator
        v-if="totalRecords > perPage"
        :rows="perPage"
        :totalRecords="totalRecords"
        :first="(currentPage - 1) * perPage"
        :rowsPerPageOptions="[10, 15, 25, 50]"
        @page="onPageChange"
      />
    </div>

    <FormDialog v-model:visible="dialogVisible" title="Nova Atribuicao" :loading="dialogLoading" @save="handleSave" width="600px">
      <div class="dialog-form">
        <div class="field">
          <label>Professor *</label>
          <Select v-model="form.teacher_id" :options="teachers" :optionLabel="teacherDisplayName" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="field">
          <label>Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="field">
          <label>Componente Curricular</label>
          <Select v-model="form.curricular_component_id" :options="components" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" showClear />
        </div>
        <div class="field">
          <label>Campo de Experiencia</label>
          <Select v-model="form.experience_field_id" :options="experienceFields" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" showClear />
        </div>
      </div>
    </FormDialog>
  </div>
</template>

<style scoped>
.mb-3 { margin-bottom: 1rem; }
.ml-2 { margin-left: 0.5rem; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
</style>
