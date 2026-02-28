<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Button from 'primevue/button'
import { curriculumService } from '@/services/curriculum.service'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { CurricularComponent, ExperienceField } from '@/types/curriculum'
import type { Teacher } from '@/types/people'
import type { ClassGroup } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const teachers = ref<(Teacher & { label: string })[]>([])
const classGroups = ref<(ClassGroup & { label: string })[]>([])
const components = ref<CurricularComponent[]>([])
const experienceFields = ref<ExperienceField[]>([])

const form = ref({
  teacher_id: null as number | null,
  class_group_id: null as number | null,
  curricular_component_id: null as number | null,
  experience_field_id: null as number | null,
  start_date: null as Date | null,
  end_date: null as Date | null,
})

const selectedClassGroup = computed(() =>
  classGroups.value.find(cg => cg.id === form.value.class_group_id) ?? null
)

const isEarlyChildhood = computed(() =>
  selectedClassGroup.value?.grade_level?.type === 'early_childhood'
)

const isElementary = computed(() =>
  selectedClassGroup.value?.grade_level?.uses_experience_fields === false
)

const hasClassGroupSelected = computed(() => !!form.value.class_group_id)

watch(() => form.value.class_group_id, (newVal, oldVal) => {
  if (newVal === oldVal) return
  if (isEarlyChildhood.value) {
    form.value.curricular_component_id = null
  }
  if (isElementary.value) {
    form.value.experience_field_id = null
  }
})

function formatPayloadDate(date: Date | null): string | null {
  if (!date) return null
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function parseDate(dateStr: string | null): Date | null {
  if (!dateStr) return null
  const [year, month, day] = dateStr.split('-').map(Number)
  return new Date(year, month - 1, day)
}

async function loadAuxData() {
  try {
    const [teachersRes, classGroupsRes, componentsRes, fieldsRes] = await Promise.all([
      peopleService.getTeachers({ per_page: 100 }),
      schoolStructureService.getClassGroups({ per_page: 100 }),
      curriculumService.getComponents({ per_page: 100 }),
      curriculumService.getExperienceFields({ per_page: 100 }),
    ])
    teachers.value = teachersRes.data.map((t: Teacher) => ({ ...t, label: t.user?.name ?? `Professor #${t.id}` }))
    classGroups.value = classGroupsRes.data.map(cg => ({ ...cg, label: [cg.grade_level?.name, cg.name, cg.shift?.name_label].filter(Boolean).join(' - ') })).sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
    components.value = componentsRes.data
    experienceFields.value = fieldsRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadAssignment() {
  if (!id.value) return
  loading.value = true
  try {
    const assignment = await curriculumService.getAssignment(id.value)
    form.value.teacher_id = assignment.teacher_id
    form.value.class_group_id = assignment.class_group_id
    form.value.curricular_component_id = assignment.curricular_component_id
    form.value.experience_field_id = assignment.experience_field_id
    form.value.start_date = parseDate(assignment.start_date)
    form.value.end_date = parseDate(assignment.end_date)
  } catch {
    toast.error('Erro ao carregar atribuicao')
    router.push('/curriculum/assignments')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    const payload = {
      teacher_id: form.value.teacher_id,
      class_group_id: form.value.class_group_id,
      curricular_component_id: form.value.curricular_component_id,
      experience_field_id: form.value.experience_field_id,
      start_date: formatPayloadDate(form.value.start_date),
      end_date: formatPayloadDate(form.value.end_date),
    }
    if (isEdit.value) {
      await curriculumService.updateAssignment(id.value!, payload)
      toast.success('Atribuicao atualizada')
    } else {
      await curriculumService.createAssignment(payload)
      toast.success('Atribuicao criada')
    }
    router.push('/curriculum/assignments')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar atribuicao'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadAssignment()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{{ isEdit ? 'Editar Atribuicao' : 'Nova Atribuicao' }}</h1>

    <div class="max-w-175 rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Professor *</label>
          <Select v-model="form.teacher_id" :options="teachers" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turma *</label>
          <Select v-model="form.class_group_id" :options="classGroups" optionLabel="label" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>

        <div v-if="hasClassGroupSelected && isElementary" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Componente Curricular *</label>
          <Select v-model="form.curricular_component_id" :options="components" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" showClear />
        </div>

        <div v-if="hasClassGroupSelected && isEarlyChildhood" class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Campo de Experiencia *</label>
          <Select v-model="form.experience_field_id" :options="experienceFields" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" showClear />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Data de Inicio *</label>
            <DatePicker v-model="form.start_date" dateFormat="dd/mm/yy" showIcon class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-[0.8125rem] font-medium">Data de Termino</label>
            <DatePicker v-model="form.end_date" dateFormat="dd/mm/yy" showIcon showButtonBar class="w-full" />
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/curriculum/assignments')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
