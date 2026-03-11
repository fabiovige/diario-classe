import { jsPDF } from 'jspdf'
import { autoTable } from 'jspdf-autotable'

const COLORS = {
  headerBg: [0, 120, 212] as [number, number, number],
  altRowBg: [240, 246, 255] as [number, number, number],
  green: [15, 123, 15] as [number, number, number],
  red: [196, 43, 28] as [number, number, number],
  textDark: [33, 33, 33] as [number, number, number],
  textMuted: [97, 97, 97] as [number, number, number],
}

interface TeacherClassesReportFilters {
  schoolName: string
  teacherName: string
  period: string
}

interface TeacherClassesReportRow {
  date: string
  classGroup: string
  component: string
  hasAttendance: boolean
  hasLessonRecord: boolean
}

export function generateTeacherClassesReportPdf(
  rows: TeacherClassesReportRow[],
  filters: TeacherClassesReportFilters,
): void {
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const margin = 14
  let y = margin

  y = drawHeader(doc, filters, pageWidth, margin, y)
  drawTable(doc, rows, margin, y)
  addPageNumbers(doc)

  const filename = `aulas_professor_${filters.teacherName.replace(/\s+/g, '_')}.pdf`
  doc.save(filename)
}

function drawHeader(
  doc: jsPDF,
  filters: TeacherClassesReportFilters,
  pageWidth: number,
  margin: number,
  y: number,
): number {
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('RESUMO DE AULAS DO PROFESSOR', pageWidth / 2, y, { align: 'center' })
  y += 7

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(...COLORS.textDark)

  if (filters.schoolName) {
    doc.text(filters.schoolName, pageWidth / 2, y, { align: 'center' })
    y += 5
  }
  if (filters.teacherName) {
    doc.text(`Professor(a): ${filters.teacherName}`, pageWidth / 2, y, { align: 'center' })
    y += 5
  }
  if (filters.period) {
    doc.text(`Periodo: ${filters.period}`, pageWidth / 2, y, { align: 'center' })
    y += 5
  }

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

function drawTable(doc: jsPDF, rows: TeacherClassesReportRow[], margin: number, y: number): void {
  const head = ['Data', 'Turma', 'Disciplina', 'Chamada', 'Diario']

  const body = rows.map(row => [
    row.date,
    row.classGroup,
    row.component,
    row.hasAttendance ? 'Sim' : 'Nao',
    row.hasLessonRecord ? 'Sim' : 'Nao',
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
      0: { halign: 'center', cellWidth: 25 },
      1: { halign: 'left', cellWidth: 60 },
      2: { halign: 'left', cellWidth: 60 },
      3: { halign: 'center', cellWidth: 25 },
      4: { halign: 'center', cellWidth: 25 },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
    didParseCell(cellData) {
      if (cellData.section !== 'body') return
      if (cellData.column.index !== 3 && cellData.column.index !== 4) return

      const val = cellData.cell.raw as string
      cellData.cell.styles.fontStyle = 'bold'
      cellData.cell.styles.textColor = val === 'Sim' ? COLORS.green : COLORS.red
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
