<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { AcademicYear, GradeLevel, Shift } from '@/types/school-structure'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const id = computed(() => route.params.id ? Number(route.params.id) : null)
const isEdit = computed(() => !!id.value)
const loading = ref(false)

const form = ref({
  academic_year_id: null as number | null,
  grade_level_id: null as number | null,
  shift_id: null as number | null,
  name: '',
  max_students: 35,
})

const academicYears = ref<AcademicYear[]>([])
const gradeLevels = ref<GradeLevel[]>([])
const shifts = ref<Shift[]>([])

async function loadAuxData() {
  try {
    const [ayRes, glRes, shRes] = await Promise.all([
      schoolStructureService.getAcademicYears({ per_page: 100 }),
      schoolStructureService.getGradeLevels(),
      schoolStructureService.getShifts({ per_page: 100 }),
    ])
    academicYears.value = ayRes.data
    gradeLevels.value = glRes.data
    shifts.value = shRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function loadClassGroup() {
  if (!id.value) return
  loading.value = true
  try {
    const item = await schoolStructureService.getClassGroup(id.value)
    form.value.academic_year_id = item.academic_year_id
    form.value.grade_level_id = item.grade_level_id
    form.value.shift_id = item.shift_id
    form.value.name = item.name
    form.value.max_students = item.max_students
  } catch {
    toast.error('Erro ao carregar turma')
    router.push('/school-structure/class-groups')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    if (isEdit.value) {
      await schoolStructureService.updateClassGroup(id.value!, form.value)
      toast.success('Turma atualizada')
    } else {
      await schoolStructureService.createClassGroup(form.value)
      toast.success('Turma criada')
    }
    router.push('/school-structure/class-groups')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao salvar turma'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAuxData()
  await loadClassGroup()
})
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">{{ isEdit ? 'Editar Turma' : 'Nova Turma' }}</h1>

    <div class="max-w-[700px] rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Ano Letivo *</label>
          <Select v-model="form.academic_year_id" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nivel de Ensino *</label>
          <Select v-model="form.grade_level_id" :options="gradeLevels" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Turno *</label>
          <Select v-model="form.shift_id" :options="shifts" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Nome *</label>
          <InputText v-model="form.name" required class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Max. Alunos *</label>
          <InputNumber v-model="form.max_students" :min="1" :max="100" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/school-structure/class-groups')" />
          <Button type="submit" :label="isEdit ? 'Atualizar' : 'Criar'" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
