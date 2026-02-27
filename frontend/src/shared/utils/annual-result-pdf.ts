import { jsPDF } from 'jspdf'
import { autoTable } from 'jspdf-autotable'
import type { AnnualResultResponse } from '@/types/period-closing'

const COLORS = {
  headerBg: [0, 120, 212] as [number, number, number],
  altRowBg: [240, 246, 255] as [number, number, number],
  green: [15, 123, 15] as [number, number, number],
  red: [196, 43, 28] as [number, number, number],
  textDark: [33, 33, 33] as [number, number, number],
  textMuted: [97, 97, 97] as [number, number, number],
}

const RESULT_MAP: Record<string, string> = {
  approved: 'Aprovado',
  retained: 'Retido',
  partial_progression: 'Prog. Parcial',
  transferred: 'Transferido',
  abandoned: 'Abandonou',
}

function formatGrade(value: number | null): string {
  if (value === null) return '--'
  return value.toFixed(1)
}

export function generateAnnualResultPdf(data: AnnualResultResponse): void {
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const margin = 14
  let y = margin

  y = drawHeader(doc, data, pageWidth, margin, y)
  y = drawResultsTable(doc, data, margin, y)
  drawSummary(doc, data, margin, y)
  addPageNumbers(doc)

  const turmaName = data.class_group.name.replace(/\s+/g, '_')
  doc.save(`ata_resultados_${turmaName}_${data.class_group.academic_year.year}.pdf`)
}

function drawHeader(
  doc: jsPDF,
  data: AnnualResultResponse,
  pageWidth: number,
  margin: number,
  y: number,
): number {
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('ATA DE RESULTADOS FINAIS', pageWidth / 2, y, { align: 'center' })
  y += 7

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(...COLORS.textDark)

  const schoolName = data.class_group.academic_year.school_name ?? ''
  if (schoolName) {
    doc.text(schoolName, pageWidth / 2, y, { align: 'center' })
    y += 5
  }

  const turmaLabel = [data.class_group.grade_level, data.class_group.name, data.class_group.shift]
    .filter(Boolean)
    .join(' - ')
  doc.text(`Turma: ${turmaLabel}`, pageWidth / 2, y, { align: 'center' })
  y += 5
  doc.text(`Ano Letivo: ${data.class_group.academic_year.year}`, pageWidth / 2, y, { align: 'center' })
  y += 5

  doc.setFontSize(8)
  doc.setTextColor(...COLORS.textMuted)
  doc.text(`Gerado em: ${new Date().toLocaleDateString('pt-BR')}`, pageWidth - margin, y, { align: 'right' })
  y += 4

  doc.setDrawColor(...COLORS.headerBg)
  doc.setLineWidth(0.5)
  doc.line(margin, y, pageWidth - margin, y)
  y += 6

  return y
}

function drawResultsTable(doc: jsPDF, data: AnnualResultResponse, margin: number, y: number): number {
  const periods = data.assessment_periods
  const passingGrade = data.summary.passing_grade

  const head = [
    'No',
    'Aluno',
    ...periods.map(p => p.name),
    'Media',
    'Freq %',
    'Situacao',
  ]

  const body = data.students.map((student, index) => {
    const periodGrades = periods.map(p => {
      if (student.subjects.length === 0) return '--'
      const avg = student.subjects[0]?.periods?.[String(p.number)]
      return formatGrade(avg ?? null)
    })

    return [
      String(index + 1),
      student.name,
      ...periodGrades,
      formatGrade(student.overall_average),
      student.overall_frequency !== null ? `${student.overall_frequency.toFixed(1)}%` : '--',
      student.result ? (RESULT_MAP[student.result] ?? student.result) : 'Pendente',
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
      fontSize: 7,
      textColor: COLORS.textDark,
    },
    columnStyles: {
      0: { halign: 'center', cellWidth: 10 },
      1: { halign: 'left', cellWidth: 55 },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
    didParseCell(cellData) {
      if (cellData.section !== 'body') return

      const colCount = head.length
      const mediaCol = colCount - 3
      const freqCol = colCount - 2
      const situacaoCol = colCount - 1

      if (cellData.column.index === mediaCol) {
        const val = parseFloat(cellData.cell.raw as string)
        if (!isNaN(val)) {
          cellData.cell.styles.textColor = val >= passingGrade ? COLORS.green : COLORS.red
          cellData.cell.styles.fontStyle = 'bold'
        }
      }

      if (cellData.column.index === freqCol) {
        const val = parseFloat(cellData.cell.raw as string)
        if (!isNaN(val) && val < 75) {
          cellData.cell.styles.textColor = COLORS.red
          cellData.cell.styles.fontStyle = 'bold'
        }
      }

      if (cellData.column.index === situacaoCol) {
        const student = data.students[cellData.row.index]
        if (student?.result === 'approved') {
          cellData.cell.styles.textColor = COLORS.green
          cellData.cell.styles.fontStyle = 'bold'
        }
        if (student?.result === 'retained') {
          cellData.cell.styles.textColor = COLORS.red
          cellData.cell.styles.fontStyle = 'bold'
        }
      }

      if (cellData.column.index >= 2 && cellData.column.index <= mediaCol) {
        cellData.cell.styles.halign = 'center'
      }
      if (cellData.column.index === freqCol || cellData.column.index === situacaoCol) {
        cellData.cell.styles.halign = 'center'
      }
    },
  })

  return (doc as unknown as { lastAutoTable: { finalY: number } }).lastAutoTable.finalY + 8
}

function drawSummary(doc: jsPDF, data: AnnualResultResponse, margin: number, y: number): void {
  const pageHeight = doc.internal.pageSize.getHeight()
  if (y > pageHeight - 30) {
    doc.addPage()
    y = 14
  }

  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.textDark)
  doc.text('Resumo:', margin, y)
  y += 5

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  const lines = [
    `Total de alunos: ${data.summary.total}`,
    `Aprovados: ${data.summary.approved}`,
    `Retidos: ${data.summary.retained}`,
    `Pendentes: ${data.summary.pending}`,
    `Nota minima para aprovacao: ${data.summary.passing_grade}`,
  ]

  lines.forEach(line => {
    doc.text(line, margin, y)
    y += 4.5
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
