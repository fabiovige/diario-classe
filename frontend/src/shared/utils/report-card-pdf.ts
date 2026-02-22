import { jsPDF } from 'jspdf'
import { autoTable } from 'jspdf-autotable'
import { formatDate } from '@/shared/utils/formatters'
import type { ReportCardResponse } from '@/types/assessment'

const COLORS = {
  headerBg: [0, 120, 212] as [number, number, number],
  altRowBg: [240, 246, 255] as [number, number, number],
  green: [15, 123, 15] as [number, number, number],
  red: [196, 43, 28] as [number, number, number],
  textDark: [33, 33, 33] as [number, number, number],
  textMuted: [97, 97, 97] as [number, number, number],
}

const STATUS_MAP: Record<string, string> = {
  pending: 'Cursando',
  calculated: 'Calculado',
  approved: 'Aprovado',
  reproved: 'Reprovado',
}

function formatGrade(value: number | null): string {
  if (value === null) return '--'
  return value.toFixed(1)
}

export function generateReportCardPdf(report: ReportCardResponse): void {
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const margin = 14
  let y = margin

  y = drawHeader(doc, report, pageWidth, margin, y)
  y = drawStudentInfo(doc, report, margin, y)
  y = drawGradesTable(doc, report, margin, y)
  y = drawFrequencyTable(doc, report, margin, y)
  drawDescriptiveReports(doc, report, margin, y)

  addPageNumbers(doc)

  const studentName = report.student.name.replace(/\s+/g, '_')
  doc.save(`boletim_${studentName}.pdf`)
}

function drawHeader(
  doc: jsPDF,
  report: ReportCardResponse,
  pageWidth: number,
  margin: number,
  y: number,
): number {
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('BOLETIM ESCOLAR', pageWidth / 2, y, { align: 'center' })
  y += 7

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(...COLORS.textDark)

  const schoolName = report.student.school_name ?? ''
  const academicYear = report.student.academic_year ?? ''
  if (schoolName) {
    doc.text(String(schoolName), pageWidth / 2, y, { align: 'center' })
    y += 5
  }
  if (academicYear) {
    doc.text(`Ano Letivo: ${academicYear}`, pageWidth / 2, y, { align: 'center' })
    y += 5
  }

  doc.setFontSize(8)
  doc.setTextColor(...COLORS.textMuted)
  doc.text(`Gerado em: ${new Date().toLocaleDateString('pt-BR')}`, pageWidth - margin, y, { align: 'right' })
  y += 6

  doc.setDrawColor(...COLORS.headerBg)
  doc.setLineWidth(0.5)
  doc.line(margin, y, pageWidth - margin, y)
  y += 6

  return y
}

function drawStudentInfo(doc: jsPDF, report: ReportCardResponse, margin: number, y: number): number {
  doc.setFontSize(10)
  doc.setTextColor(...COLORS.textDark)

  const student = report.student
  const lines: string[] = []

  lines.push(`Aluno(a): ${student.display_name ?? student.name}`)
  if (student.birth_date) {
    lines.push(`Data de Nascimento: ${formatDate(student.birth_date)}`)
  }
  if (student.class_group) {
    lines.push(`Turma: ${student.class_group.label}`)
  }
  if (student.enrollment_number) {
    lines.push(`Matricula (RA): ${student.enrollment_number}`)
  }

  const col1 = lines.slice(0, 2)
  const col2 = lines.slice(2)

  col1.forEach((line, i) => {
    doc.setFont('helvetica', i === 0 ? 'bold' : 'normal')
    doc.text(line, margin, y + i * 5)
  })

  col2.forEach((line, i) => {
    doc.setFont('helvetica', 'normal')
    doc.text(line, margin + 140, y + i * 5)
  })

  y += Math.max(col1.length, col2.length) * 5 + 4
  return y
}

function drawGradesTable(doc: jsPDF, report: ReportCardResponse, margin: number, y: number): number {
  if (report.subjects.length === 0) return y

  doc.setFontSize(11)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('Notas', margin, y)
  y += 2

  const periods = report.assessment_periods
  const passingGrade = report.summary?.passing_grade ?? 6

  const head = [
    'Materia',
    'Professor',
    ...periods.map(p => p.name),
    'Media Final',
    'Situacao',
  ]

  const body = report.subjects.map(subject => {
    const periodValues = periods.map(p => formatGrade(subject.periods[String(p.number)]?.average ?? null))
    const finalGrade = subject.final_grade ?? subject.final_average
    return [
      subject.name,
      subject.teacher_name || '--',
      ...periodValues,
      formatGrade(finalGrade),
      STATUS_MAP[subject.status] ?? subject.status,
    ]
  })

  autoTable(doc, {
    startY: y,
    margin: { left: margin, right: margin },
    head: [head],
    body,
    theme: 'grid',
    headStyles: {
      fillColor: COLORS.headerBg,
      textColor: [255, 255, 255],
      fontStyle: 'bold',
      fontSize: 8,
      halign: 'center',
    },
    bodyStyles: {
      fontSize: 8,
      textColor: COLORS.textDark,
    },
    columnStyles: {
      0: { halign: 'left', cellWidth: 50 },
      1: { halign: 'left', cellWidth: 40 },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
    didParseCell(data) {
      if (data.section !== 'body') return

      const colCount = head.length
      const finalAvgCol = colCount - 2
      const statusCol = colCount - 1

      if (data.column.index === finalAvgCol) {
        const val = parseFloat(data.cell.raw as string)
        if (!isNaN(val)) {
          data.cell.styles.textColor = val >= passingGrade ? COLORS.green : COLORS.red
          data.cell.styles.fontStyle = 'bold'
        }
      }

      if (data.column.index === statusCol) {
        const status = report.subjects[data.row.index]?.status
        if (status === 'approved') {
          data.cell.styles.textColor = COLORS.green
          data.cell.styles.fontStyle = 'bold'
        }
        if (status === 'reproved') {
          data.cell.styles.textColor = COLORS.red
          data.cell.styles.fontStyle = 'bold'
        }
      }

      if (data.column.index >= 2 && data.column.index < finalAvgCol) {
        data.cell.styles.halign = 'center'
      }
      if (data.column.index === finalAvgCol || data.column.index === statusCol) {
        data.cell.styles.halign = 'center'
      }
    },
  })

  return (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 8
}

function drawFrequencyTable(doc: jsPDF, report: ReportCardResponse, margin: number, y: number): number {
  if (report.subjects.length === 0) return y

  const pageHeight = doc.internal.pageSize.getHeight()
  if (y > pageHeight - 40) {
    doc.addPage()
    y = 14
  }

  doc.setFontSize(11)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('Frequencia', margin, y)
  y += 2

  const periods = report.assessment_periods

  const head = [
    'Materia',
    ...periods.map(p => `Faltas ${p.name}`),
    'Total Faltas',
    'Frequencia %',
  ]

  const body = report.subjects.map(subject => {
    const periodAbsences = periods.map(p => String(subject.periods[String(p.number)]?.absences ?? 0))
    return [
      subject.name,
      ...periodAbsences,
      String(subject.total_absences),
      subject.frequency_percentage !== null ? `${subject.frequency_percentage.toFixed(1)}%` : '--',
    ]
  })

  autoTable(doc, {
    startY: y,
    margin: { left: margin, right: margin },
    head: [head],
    body,
    theme: 'grid',
    headStyles: {
      fillColor: COLORS.headerBg,
      textColor: [255, 255, 255],
      fontStyle: 'bold',
      fontSize: 8,
      halign: 'center',
    },
    bodyStyles: {
      fontSize: 8,
      textColor: COLORS.textDark,
      halign: 'center',
    },
    columnStyles: {
      0: { halign: 'left', cellWidth: 50 },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
  })

  return (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 8
}

function drawDescriptiveReports(doc: jsPDF, report: ReportCardResponse, margin: number, y: number): void {
  if (report.descriptive_reports.length === 0) return

  const pageHeight = doc.internal.pageSize.getHeight()
  if (y > pageHeight - 40) {
    doc.addPage()
    y = 14
  }

  doc.setFontSize(11)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('Relatorios Descritivos', margin, y)
  y += 2

  const head = ['Campo de Experiencia', 'Periodo', 'Conteudo']

  const body = report.descriptive_reports.map(r => [
    r.experience_field,
    r.period,
    r.content,
  ])

  autoTable(doc, {
    startY: y,
    margin: { left: margin, right: margin },
    head: [head],
    body,
    theme: 'grid',
    headStyles: {
      fillColor: COLORS.headerBg,
      textColor: [255, 255, 255],
      fontStyle: 'bold',
      fontSize: 8,
      halign: 'center',
    },
    bodyStyles: {
      fontSize: 8,
      textColor: COLORS.textDark,
    },
    columnStyles: {
      0: { cellWidth: 55, halign: 'left' },
      1: { cellWidth: 35, halign: 'center' },
      2: { halign: 'left' },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
  })
}

function addPageNumbers(doc: jsPDF): void {
  const totalPages = doc.getNumberOfPages()
  if (totalPages <= 1) return

  const pageWidth = doc.internal.pageSize.getWidth()
  const pageHeight = doc.internal.pageSize.getHeight()

  for (let i = 1; i <= totalPages; i++) {
    doc.setPage(i)
    doc.setFontSize(8)
    doc.setTextColor(...COLORS.textMuted)
    doc.text(`Pagina ${i} de ${totalPages}`, pageWidth / 2, pageHeight - 8, { align: 'center' })
  }
}
