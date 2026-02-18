export function formatDate(dateStr: string | null | undefined): string {
  if (!dateStr) return '--'
  const date = new Date(dateStr)
  return date.toLocaleDateString('pt-BR')
}

export function formatDateTime(dateStr: string | null | undefined): string {
  if (!dateStr) return '--'
  const date = new Date(dateStr)
  return date.toLocaleString('pt-BR')
}

export function formatCpf(cpf: string | null | undefined): string {
  if (!cpf) return '--'
  const cleaned = cpf.replace(/\D/g, '')
  if (cleaned.length !== 11) return cpf
  return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

export function formatPercentage(value: number | null | undefined, decimals = 1): string {
  if (value === null || value === undefined) return '--'
  return `${value.toFixed(decimals)}%`
}

export function formatPhone(phone: string | null | undefined): string {
  if (!phone) return '--'
  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length === 11) {
    return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
  if (cleaned.length === 10) {
    return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  }
  return phone
}
