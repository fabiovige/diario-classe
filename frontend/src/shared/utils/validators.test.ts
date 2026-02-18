import { describe, it, expect } from 'vitest'
import { isValidCpf, isValidEmail, isRequired } from './validators'

describe('isValidCpf', () => {
  it('returns true for a valid CPF', () => {
    expect(isValidCpf('52998224725')).toBe(true)
  })

  it('returns true for valid CPF with formatting', () => {
    expect(isValidCpf('529.982.247-25')).toBe(true)
  })

  it('returns false for invalid CPF (wrong check digits)', () => {
    expect(isValidCpf('12345678900')).toBe(false)
  })

  it('returns false for all same digits', () => {
    expect(isValidCpf('11111111111')).toBe(false)
    expect(isValidCpf('00000000000')).toBe(false)
    expect(isValidCpf('99999999999')).toBe(false)
  })

  it('returns false for empty string', () => {
    expect(isValidCpf('')).toBe(false)
  })

  it('returns false for string with wrong length', () => {
    expect(isValidCpf('123')).toBe(false)
    expect(isValidCpf('123456789012')).toBe(false)
  })
})

describe('isValidEmail', () => {
  it('returns true for valid email', () => {
    expect(isValidEmail('user@example.com')).toBe(true)
  })

  it('returns true for email with subdomain', () => {
    expect(isValidEmail('user@mail.example.com')).toBe(true)
  })

  it('returns false for email without @', () => {
    expect(isValidEmail('userexample.com')).toBe(false)
  })

  it('returns false for email without domain', () => {
    expect(isValidEmail('user@')).toBe(false)
  })

  it('returns false for email with spaces', () => {
    expect(isValidEmail('user @example.com')).toBe(false)
  })

  it('returns false for empty string', () => {
    expect(isValidEmail('')).toBe(false)
  })
})

describe('isRequired', () => {
  it('returns false for null', () => {
    expect(isRequired(null)).toBe(false)
  })

  it('returns false for undefined', () => {
    expect(isRequired(undefined)).toBe(false)
  })

  it('returns false for empty string', () => {
    expect(isRequired('')).toBe(false)
  })

  it('returns false for whitespace-only string', () => {
    expect(isRequired('   ')).toBe(false)
  })

  it('returns true for non-empty string', () => {
    expect(isRequired('hello')).toBe(true)
  })

  it('returns true for number zero', () => {
    expect(isRequired(0)).toBe(true)
  })

  it('returns true for boolean false', () => {
    expect(isRequired(false)).toBe(true)
  })

  it('returns true for object', () => {
    expect(isRequired({})).toBe(true)
  })
})
