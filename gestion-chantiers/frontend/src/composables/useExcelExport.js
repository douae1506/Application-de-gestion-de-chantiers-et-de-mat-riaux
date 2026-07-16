import * as XLSX from 'xlsx'

/**
 * Exporte un tableau d'objets en fichier Excel (.xlsx).
 *
 * @param {string} filename - nom du fichier sans extension
 * @param {Array<{key: string, label: string}>} columns - colonnes à exporter
 * @param {Array<Object>} rows - données brutes (chaque objet doit contenir les clés de `columns`)
 * @param {string} sheetName - nom de la feuille
 */
export function exportToExcel(filename, columns, rows, sheetName = 'Feuille 1') {
  const data = rows.map((row) => {
    const line = {}
    columns.forEach((col) => {
      line[col.label] = typeof col.value === 'function' ? col.value(row) : row[col.key]
    })
    return line
  })

  const worksheet = XLSX.utils.json_to_sheet(data)

  // Largeur de colonnes auto (approximative, basée sur le contenu le plus long)
  worksheet['!cols'] = columns.map((col) => {
    const maxLen = Math.max(
      col.label.length,
      ...data.map((d) => String(d[col.label] ?? '').length)
    )
    return { wch: Math.min(Math.max(maxLen + 2, 10), 40) }
  })

  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, sheetName)

  const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-')
  XLSX.writeFile(workbook, `${filename}_${stamp}.xlsx`)
}

export function useExcelExport() {
  return { exportToExcel }
}
