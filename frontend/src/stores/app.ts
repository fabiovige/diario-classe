import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const sidebarCollapsed = ref(false)
  const selectedSchoolId = ref<number | null>(null)
  const selectedAcademicYearId = ref<number | null>(null)

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  function setSchool(id: number | null) {
    selectedSchoolId.value = id
  }

  function setAcademicYear(id: number | null) {
    selectedAcademicYearId.value = id
  }

  return {
    sidebarCollapsed,
    selectedSchoolId,
    selectedAcademicYearId,
    toggleSidebar,
    setSchool,
    setAcademicYear,
  }
})
