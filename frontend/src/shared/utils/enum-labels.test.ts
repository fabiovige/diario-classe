import { describe, it, expect } from 'vitest'
import {
  roleLabel,
  userStatusLabel,
  enrollmentStatusLabel,
} from './enum-labels'

describe('roleLabel', () => {
  it('returns "Administrador" for admin', () => {
    expect(roleLabel('admin')).toBe('Administrador')
  })

  it('returns "Professor(a)" for teacher', () => {
    expect(roleLabel('teacher')).toBe('Professor(a)')
  })

  it('returns "Diretor(a)" for director', () => {
    expect(roleLabel('director')).toBe('Diretor(a)')
  })

  it('returns "Secretario(a)" for secretary', () => {
    expect(roleLabel('secretary')).toBe('Secretario(a)')
  })

  it('returns "Coordenador(a)" for coordinator', () => {
    expect(roleLabel('coordinator')).toBe('Coordenador(a)')
  })

  it('returns "Responsavel" for guardian', () => {
    expect(roleLabel('guardian')).toBe('Responsavel')
  })

  it('returns the slug itself for unknown value', () => {
    expect(roleLabel('unknown' as any)).toBe('unknown')
  })
})

describe('userStatusLabel', () => {
  it('returns "Ativo" for active', () => {
    expect(userStatusLabel('active')).toBe('Ativo')
  })

  it('returns "Inativo" for inactive', () => {
    expect(userStatusLabel('inactive')).toBe('Inativo')
  })

  it('returns "Bloqueado" for blocked', () => {
    expect(userStatusLabel('blocked')).toBe('Bloqueado')
  })

  it('returns the status itself for unknown value', () => {
    expect(userStatusLabel('unknown' as any)).toBe('unknown')
  })
})

describe('enrollmentStatusLabel', () => {
  it('returns "Ativa" for active', () => {
    expect(enrollmentStatusLabel('active')).toBe('Ativa')
  })

  it('returns "Transferida" for transferred', () => {
    expect(enrollmentStatusLabel('transferred')).toBe('Transferida')
  })

  it('returns "Cancelada" for cancelled', () => {
    expect(enrollmentStatusLabel('cancelled')).toBe('Cancelada')
  })

  it('returns "Concluida" for completed', () => {
    expect(enrollmentStatusLabel('completed')).toBe('Concluida')
  })

  it('returns the status itself for unknown value', () => {
    expect(enrollmentStatusLabel('unknown' as any)).toBe('unknown')
  })
})
