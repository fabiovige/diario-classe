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

const classGroups = ref<(ClassGroup & { label: string })[]>([])
const assignments = ref<(TeacherAssignment & { label: string })[]>([])
const loadingAssignments = ref(false)

const assignmentPlaceholder = computed(() => {
  if (!form.value.class_group_id) return 'Selecione a turma primeiro'
  if (loadingAssignments.value) return 'Carregando...'
  if (assignments.value.length === 0) return 'Nenhuma disciplina vinculada'
  return 'Selecione'
})

async function loadClassGroups() {
  try {
    const response = await schoolStructureService.getClassGroups({ per_page: 100 })
    classGroups.value = response.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name].filter(Boolean).join(' - ') }))
  } catch {
    toast.error('Erro ao carregar turmas')
  }
}

async function loadAssignments() {
  if (!form.value.class_group_id) return
  loadingAssignments.value = true
  try {
    const response = await curriculumService.getAssignments({ class_group_id: form.value.class_group_id, per_page: 100 })
    assignments.value = response.data.map(a => ({ ...a, label: a.curricular_component?.name ?? a.experience_field?.name ?? `Disciplina #${a.id}` }))
  } catch {
    toast.error('Erro ao carregar disciplinas')
  } finally {
    loadingAssignments.value = false
  }
}

function onClassGroupChange() {
  assignments.value = []
  form.value.teacher_assignment_id = null
  loadAssignments()
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
    await loadAssignments()
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
  await loadClassGroups()
  await loadRecord()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Registro de Aula' : 'Novo Registro de Aula' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter @change="onClassGroupChange" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Disciplina *</label>
          <Select v-model="form.teacher_assignment_id" :options="assignments" optionLabel="label" optionValue="id" :placeholder="assignmentPlaceholder" :disabled="!form.class_group_id || loadingAssignments || assignments.length === 0" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Data *</label>
          <InputText v-model="form.date" type="date" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Conteudo *</label>
          <Textarea v-model="form.content" rows="4" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Metodologia</label>
          <Textarea v-model="form.methodology" rows="3" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Observacoes</label>
          <Textarea v-model="form.observations" rows="3" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Quantidade de Aulas *</label>
          <InputNumber v-model="form.class_count" :min="1" :max="10" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/class-record')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
