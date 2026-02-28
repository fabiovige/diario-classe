import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const selectedSchoolId = ref<number | null>(null)
  const selectedAcademicYearId = ref<number | null>(null)

  function setSchool(id: number | null) {
    selectedSchoolId.value = id
  }

  function setAcademicYear(id: number | null) {
    selectedAcademicYearId.value = id
  }

  return {
    selectedSchoolId,
    selectedAcademicYearId,
    setSchool,
    setAcademicYear,
  }
})
