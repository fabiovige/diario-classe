import { describe, it, expect } from 'vitest'
import { formatDate, formatDateTime, formatCpf, formatPercentage, formatPhone } from './formatters'

describe('formatDate', () => {
  it('returns formatted date for valid date string', () => {
    const result = formatDate('2025-03-15T12:00:00')
    expect(result).toMatch(/15\/03\/2025/)
  })

  it('returns "--" for null', () => {
    expect(formatDate(null)).toBe('--')
  })

  it('returns "--" for undefined', () => {
    expect(formatDate(undefined)).toBe('--')
  })

  it('returns "--" for empty string', () => {
    expect(formatDate('')).toBe('--')
  })
})

describe('formatDateTime', () => {
  it('returns formatted date and time for valid date string', () => {
    const result = formatDateTime('2025-06-10T14:30:00')
    expect(result).toMatch(/10\/06\/2025/)
    expect(result).toMatch(/14:30/)
  })

  it('returns "--" for null', () => {
    expect(formatDateTime(null)).toBe('--')
  })

  it('returns "--" for undefined', () => {
    expect(formatDateTime(undefined)).toBe('--')
  })
})

describe('formatCpf', () => {
  it('formats valid 11-digit CPF string', () => {
    expect(formatCpf('12345678901')).toBe('123.456.789-01')
  })

  it('returns "--" for null', () => {
    expect(formatCpf(null)).toBe('--')
  })

  it('returns "--" for undefined', () => {
    expect(formatCpf(undefined)).toBe('--')
  })

  it('returns original string when length is not 11', () => {
    expect(formatCpf('123')).toBe('123')
  })

  it('formats CPF that contains dots and dashes (strips non-digits first)', () => {
    expect(formatCpf('123.456.789-01')).toBe('123.456.789-01')
  })
})

describe('formatPercentage', () => {
  it('formats number with default 1 decimal', () => {
    expect(formatPercentage(85.5)).toBe('85.5%')
  })

  it('formats number with custom decimals', () => {
    expect(formatPercentage(92.123, 2)).toBe('92.12%')
  })

  it('formats zero', () => {
    expect(formatPercentage(0)).toBe('0.0%')
  })

  it('returns "--" for null', () => {
    expect(formatPercentage(null)).toBe('--')
  })

  it('returns "--" for undefined', () => {
    expect(formatPercentage(undefined)).toBe('--')
  })
})

describe('formatPhone', () => {
  it('formats 11-digit mobile phone', () => {
    expect(formatPhone('11987654321')).toBe('(11) 98765-4321')
  })

  it('formats 10-digit landline phone', () => {
    expect(formatPhone('1134567890')).toBe('(11) 3456-7890')
  })

  it('returns original string for other lengths', () => {
    expect(formatPhone('123')).toBe('123')
  })

  it('returns "--" for null', () => {
    expect(formatPhone(null)).toBe('--')
  })

  it('returns "--" for undefined', () => {
    expect(formatPhone(undefined)).toBe('--')
  })

  it('strips non-digit characters before formatting', () => {
    expect(formatPhone('(11) 98765-4321')).toBe('(11) 98765-4321')
  })
})
