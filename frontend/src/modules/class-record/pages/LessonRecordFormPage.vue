<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { classRecordService } from '@/services/class-record.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { curriculumService } from '@/services/curriculum.service'
import { useToast } from '@/composables/useToast'
import type { ClassGroup } from '@/types/school-structure'
import type { TeacherAssignment } from '@/types/curriculum'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  class_group_id: null as number | null,
  teacher_assignment_id: null as number | null,
  date: new Date().toISOString().split('T')[0],
  content: '',
  methodology: '',
  observations: '',
  class_count: 1,
})

const classGroups = ref<ClassGroup[]>([])
const assignments = ref<TeacherAssignment[]>([])

function assignmentLabel(a: TeacherAssignment): string {
  return a.curricular_component?.name ?? a.experience_field?.name ?? `Atribuicao #${a.id}`
}

async function loadAuxData() {
  try {
    const [cgRes, aRes] = await Promise.all([
      schoolStructureService.getClassGroups({ per_page: 100 }),
      curriculumService.getAssignments({ per_page: 100 }),
    ])
    classGroups.value = cgRes.data
    assignments.value = aRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadRecord() {
  if (!id.value) return
  loading.value = true
  try {
    const record = await classRecordService.getRecord(id.value)
    form.value.class_group_id = record.class_group_id
    form.value.teacher_assignment_id = record.teacher_assignment_id
    form.value.date = record.date ?? ''
    form.value.content = record.content
    form.value.methodology = record.methodology
    form.value.observations = record.observations
    form.value.class_count = record.class_count
  } catch {
    toast.error('Erro ao carregar registro')
    router.push('/class-record')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await classRecordService.updateRecord(id.value!, form.value)
      toast.success('Registro atualizado')
    } else {
      await classRecordService.createRecord(form.value)
      toast.success('Registro criado')
    }
    router.push('/class-record')
  } catch (error: any) {
    toast.error(error.response?.data?.error ?? 'Erro ao salvar registro')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadRecord()
})
</script>

<template>
  <div class="page-container">
    <h1 class="page-title">{{ isEdit ? 'Editar Registro de Aula' : 'Novo Registro de Aula' }}</h1>

    <div class="card-section form-card">
      <form @submit.prevent="handleSubmit" class="form-grid">
        <div class="field">
          <label>Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Atribuicao *</label>
          <Select v-model="form.teacher_assignment_id" :options="assignments" :optionLabel="assignmentLabel" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="field">
          <label>Data *</label>
          <InputText v-model="form.date" type="date" required class="w-full" />
        </div>
        <div class="field">
          <label>Conteudo *</label>
          <Textarea v-model="form.content" rows="4" class="w-full" />
        </div>
        <div class="field">
          <label>Metodologia</label>
          <Textarea v-model="form.methodology" rows="3" class="w-full" />
        </div>
        <div class="field">
          <label>Observacoes</label>
          <Textarea v-model="form.observations" rows="3" class="w-full" />
        </div>
        <div class="field">
          <label>Quantidade de Aulas *</label>
          <InputNumber v-model="form.class_count" :min="1" :max="10" class="w-full" />
        </div>

        <div class="form-actions">
          <Button label="Cancelar" severity="secondary" @click="router.push('/class-record')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.form-card { max-width: 700px; }
.form-grid { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8125rem; font-weight: 500; }
.w-full { width: 100%; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem; }
</style>
