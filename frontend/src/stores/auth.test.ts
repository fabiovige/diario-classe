import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from './auth'
import type { User } from '@/types/auth'

const mockUser: User = {
  id: 1,
  name: 'Admin User',
  email: 'admin@example.com',
  cpf: '52998224725',
  status: 'active',
  school_id: null,
  role: {
    id: 1,
    name: 'Administrador',
    slug: 'admin',
    permissions: ['users.list', 'users.create'],
  },
  created_at: '2025-01-01T00:00:00',
  updated_at: '2025-01-01T00:00:00',
}

const storageMock = (() => {
  let store: Record<string, string> = {}
  return {
    getItem: vi.fn((key: string) => store[key] ?? null),
    setItem: vi.fn((key: string, value: string) => { store[key] = value }),
    removeItem: vi.fn((key: string) => { delete store[key] }),
    clear: vi.fn(() => { store = {} }),
  }
})()

Object.defineProperty(globalThis, 'localStorage', { value: storageMock })

describe('useAuthStore', () => {
  beforeEach(() => {
    storageMock.clear()
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  it('has initial state with no authentication', () => {
    const store = useAuthStore()
    expect(store.isAuthenticated).toBe(false)
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
  })

  it('sets auth with user and token', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token-123')

    expect(store.user).toEqual(mockUser)
    expect(store.token).toBe('test-token-123')
    expect(store.isAuthenticated).toBe(true)
    expect(storageMock.setItem).toHaveBeenCalledWith('token', 'test-token-123')
    expect(storageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(mockUser))
  })

  it('clears auth and resets state', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token-123')
    store.clearAuth()

    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(storageMock.removeItem).toHaveBeenCalledWith('token')
    expect(storageMock.removeItem).toHaveBeenCalledWith('user')
  })

  it('returns correct roleSlug', () => {
    const store = useAuthStore()
    expect(store.roleSlug).toBeNull()

    store.setAuth(mockUser, 'test-token')
    expect(store.roleSlug).toBe('admin')
  })

  it('hasRole returns true when user has the role', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    expect(store.hasRole('admin')).toBe(true)
    expect(store.hasRole('admin', 'teacher')).toBe(true)
  })

  it('hasRole returns false when user does not have the role', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    expect(store.hasRole('teacher')).toBe(false)
  })

  it('hasRole returns false when no user is set', () => {
    const store = useAuthStore()
    expect(store.hasRole('admin')).toBe(false)
  })

  it('hasPermission returns true for existing permission', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    expect(store.hasPermission('users.list')).toBe(true)
  })

  it('hasPermission returns false for non-existing permission', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    expect(store.hasPermission('schools.delete')).toBe(false)
  })

  it('userName returns user name when set', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    expect(store.userName).toBe('Admin User')
  })

  it('userName returns empty string when no user', () => {
    const store = useAuthStore()
    expect(store.userName).toBe('')
  })

  it('updateUser updates user data', () => {
    const store = useAuthStore()
    store.setAuth(mockUser, 'test-token')

    const updatedUser = { ...mockUser, name: 'Updated Name' }
    store.updateUser(updatedUser)

    expect(store.user?.name).toBe('Updated Name')
    expect(storageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(updatedUser))
  })
})
