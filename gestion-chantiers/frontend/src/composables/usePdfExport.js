import { ref } from 'vue'
import api from '@/services/api'

/**
 * Récupère un PDF généré côté backend (DomPDF) via l'instance axios
 * authentifiée (le token Bearer est nécessaire, donc on ne peut pas
 * simplement faire un <a href="..."> vers l'API).
 */
export function usePdfExport() {
  const loading = ref(false)
  const error = ref(null)

  async function fetchPdfBlob(url, params = {}) {
    const { data } = await api.get(url, { params, responseType: 'blob' })
    return new Blob([data], { type: 'application/pdf' })
  }

  /** Télécharge directement le PDF sur le disque de l'utilisateur */
  async function downloadPdf(url, filename, params = {}) {
    loading.value = true
    error.value = null
    try {
      const blob = await fetchPdfBlob(url, params)
      const objectUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = objectUrl
      link.download = filename.endsWith('.pdf') ? filename : `${filename}.pdf`
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(objectUrl)
    } catch (e) {
      console.error('Erreur export PDF', e)
      error.value = "Impossible de générer le PDF."
      alert(error.value)
    } finally {
      loading.value = false
    }
  }

  /** Ouvre le PDF dans un nouvel onglet et déclenche l'impression */
  async function printPdf(url, params = {}) {
    loading.value = true
    error.value = null
    try {
      const blob = await fetchPdfBlob(url, params)
      const objectUrl = window.URL.createObjectURL(blob)
      const printWindow = window.open(objectUrl, '_blank')
      if (printWindow) {
        printWindow.addEventListener('load', () => {
          printWindow.focus()
          printWindow.print()
        })
      } else {
        alert("Le navigateur a bloqué l'ouverture de la fenêtre d'impression. Autorisez les pop-ups pour ce site.")
      }
    } catch (e) {
      console.error('Erreur impression PDF', e)
      error.value = "Impossible de générer le document à imprimer."
      alert(error.value)
    } finally {
      loading.value = false
    }
  }

  return { loading, error, downloadPdf, printPdf }
}
