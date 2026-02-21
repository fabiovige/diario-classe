<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { enrollmentService } from '@/services/enrollment.service'
import { peopleService } from '@/services/people.service'
import { schoolStructureService } from '@/services/school-structure.service'
import { useToast } from '@/composables/useToast'
import { extractApiError } from '@/shared/utils/api-error'
import type { Student } from '@/types/people'
import type { School, AcademicYear } from '@/types/school-structure'

const router = useRouter()
const toast = useToast()

const loading = ref(false)

const form = ref({
  student_id: null as number | null,
  academic_year_id: null as number | null,
  school_id: null as number | null,
})

const students = ref<Student[]>([])
const schools = ref<School[]>([])
const academicYears = ref<AcademicYear[]>([])

async function loadAuxData() {
  try {
    const [studentsRes, schoolsRes, ayRes] = await Promise.all([
      peopleService.getStudents({ per_page: 100 }),
      schoolStructureService.getSchools({ per_page: 100 }),
      schoolStructureService.getAcademicYears({ per_page: 100 }),
    ])
    students.value = studentsRes.data
    schools.value = schoolsRes.data
    academicYears.value = ayRes.data
  } catch {
    toast.error('Erro ao carregar dados auxiliares')
  }
}

async function handleSubmit() {
  loading.value = true
  try {
    await enrollmentService.createEnrollment(form.value)
    toast.success('Matricula criada')
    router.push('/enrollment/enrollments')
  } catch (error: unknown) {
    toast.error(extractApiError(error, 'Erro ao criar matricula'))
  } finally {
    loading.value = false
  }
}

onMounted(loadAuxData)
</script>

<template>
  <div class="p-6">
    <h1 class="mb-6 text-2xl font-semibold text-[#0078D4]">Nova Matricula</h1>

    <div class="rounded-lg border border-[#E0E0E0] bg-white p-6 shadow-sm max-w-[700px]">
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Aluno *</label>
          <Select v-model="form.student_id" :options="students" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" filter />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Escola *</label>
          <Select v-model="form.school_id" :options="schools" optionLabel="name" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-[0.8125rem] font-medium">Ano Letivo *</label>
          <Select v-model="form.academic_year_id" :options="academicYears" optionLabel="year" optionValue="id" placeholder="Selecione" class="w-full" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button label="Cancelar" severity="secondary" @click="router.push('/enrollment/enrollments')" />
          <Button type="submit" label="Matricular" icon="pi pi-check" :loading="loading" />
        </div>
      </form>
    </div>
  </div>
</template>
