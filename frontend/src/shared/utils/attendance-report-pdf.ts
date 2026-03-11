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

interface AttendanceReportFilters {
  schoolName: string
  classGroupName: string
  dateFrom: string
  dateTo: string
}

interface AttendanceReportRow {
  studentName: string
  totalClasses: number
  present: number
  absent: number
  justified: number
  excused: number
  frequencyPercentage: number
}

export function generateAttendanceReportPdf(
  rows: AttendanceReportRow[],
  filters: AttendanceReportFilters,
): void {
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const margin = 14
  let y = margin

  y = drawHeader(doc, filters, pageWidth, margin, y)
  drawTable(doc, rows, margin, y)
  addPageNumbers(doc)

  const filename = `frequencia_${filters.classGroupName.replace(/\s+/g, '_')}.pdf`
  doc.save(filename)
}

function drawHeader(
  doc: jsPDF,
  filters: AttendanceReportFilters,
  pageWidth: number,
  margin: number,
  y: number,
): number {
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(...COLORS.headerBg)
  doc.text('RELATORIO DE FREQUENCIA', pageWidth / 2, y, { align: 'center' })
  y += 7

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(...COLORS.textDark)

  if (filters.schoolName) {
    doc.text(filters.schoolName, pageWidth / 2, y, { align: 'center' })
    y += 5
  }
  if (filters.classGroupName) {
    doc.text(`Turma: ${filters.classGroupName}`, pageWidth / 2, y, { align: 'center' })
    y += 5
  }

  const period = [filters.dateFrom, filters.dateTo].filter(Boolean).join(' a ')
  if (period) {
    doc.text(`Periodo: ${period}`, pageWidth / 2, y, { align: 'center' })
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

function drawTable(doc: jsPDF, rows: AttendanceReportRow[], margin: number, y: number): void {
  const head = ['No', 'Aluno', 'Total Aulas', 'Presencas', 'Faltas', 'Justificadas', 'Dispensas', 'Frequencia %']

  const body = rows.map((row, index) => [
    String(index + 1),
    row.studentName,
    String(row.totalClasses),
    String(row.present),
    String(row.absent),
    String(row.justified),
    String(row.excused),
    `${row.frequencyPercentage.toFixed(1)}%`,
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
      0: { halign: 'center', cellWidth: 10 },
      1: { halign: 'left', cellWidth: 60 },
      2: { halign: 'center' },
      3: { halign: 'center' },
      4: { halign: 'center' },
      5: { halign: 'center' },
      6: { halign: 'center' },
      7: { halign: 'center' },
    },
    alternateRowStyles: {
      fillColor: COLORS.altRowBg,
    },
    didParseCell(cellData) {
      if (cellData.section !== 'body') return
      if (cellData.column.index !== 7) return

      const val = parseFloat(cellData.cell.raw as string)
      if (isNaN(val)) return
      cellData.cell.styles.fontStyle = 'bold'
      cellData.cell.styles.textColor = val >= 75 ? COLORS.green : COLORS.red
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
