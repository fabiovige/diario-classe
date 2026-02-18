import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAppStore } from './app'

describe('useAppStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('has correct initial state', () => {
    const store = useAppStore()
    expect(store.sidebarCollapsed).toBe(false)
    expect(store.selectedSchoolId).toBeNull()
    expect(store.selectedAcademicYearId).toBeNull()
  })

  it('toggleSidebar flips collapsed state', () => {
    const store = useAppStore()
    expect(store.sidebarCollapsed).toBe(false)

    store.toggleSidebar()
    expect(store.sidebarCollapsed).toBe(true)

    store.toggleSidebar()
    expect(store.sidebarCollapsed).toBe(false)
  })

  it('setSchool sets the selected school id', () => {
    const store = useAppStore()
    store.setSchool(42)
    expect(store.selectedSchoolId).toBe(42)
  })

  it('setSchool accepts null to clear selection', () => {
    const store = useAppStore()
    store.setSchool(42)
    store.setSchool(null)
    expect(store.selectedSchoolId).toBeNull()
  })

  it('setAcademicYear sets the selected academic year id', () => {
    const store = useAppStore()
    store.setAcademicYear(2025)
    expect(store.selectedAcademicYearId).toBe(2025)
  })

  it('setAcademicYear accepts null to clear selection', () => {
    const store = useAppStore()
    store.setAcademicYear(2025)
    store.setAcademicYear(null)
    expect(store.selectedAcademicYearId).toBeNull()
  })
})
