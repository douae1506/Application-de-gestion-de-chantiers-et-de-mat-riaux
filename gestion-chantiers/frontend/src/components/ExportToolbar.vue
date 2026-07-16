<template>
  <div class="export-toolbar" ref="rootRef">
    <button type="button" class="export-trigger" @click="open = !open" :disabled="loading">
      <FileDown :size="16" :stroke-width="2.2" />
      <span>{{ loading ? 'Génération…' : 'Exporter' }}</span>
      <ChevronDown :size="14" :stroke-width="2.4" class="chev" :class="{ rotated: open }" />
    </button>

    <div v-if="open" class="export-menu" @click.stop>
      <button type="button" class="export-item" @click="handlePdf">
        <FileText :size="15" :stroke-width="2.1" />
        <span>Exporter en PDF</span>
      </button>
      <button type="button" class="export-item" @click="handleExcel">
        <FileSpreadsheet :size="15" :stroke-width="2.1" />
        <span>Exporter en Excel</span>
      </button>
      <template v-if="printLabel">
        <div class="export-sep"></div>
        <button type="button" class="export-item" @click="handlePrint">
          <Printer :size="15" :stroke-width="2.1" />
          <span>{{ printLabel }}</span>
        </button>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { FileDown, FileText, FileSpreadsheet, Printer, ChevronDown } from 'lucide-vue-next'
import { usePdfExport } from '@/composables/usePdfExport'
import { exportToExcel } from '@/composables/useExcelExport'

const props = defineProps({
  // Endpoint backend pour le PDF (ex: '/exports/chantiers')
  pdfUrl: { type: String, required: true },
  // Query params envoyés au backend PDF (filtres actifs de la vue)
  pdfParams: { type: Object, default: () => ({}) },
  // Nom du fichier PDF (sans extension)
  pdfFilename: { type: String, required: true },
  // Colonnes Excel : [{ key, label, value?(row) }]
  excelColumns: { type: Array, required: true },
  // Données à exporter en Excel (déjà chargées côté vue, pas besoin de re-fetch)
  excelRows: { type: Array, required: true },
  excelFilename: { type: String, required: true },
  // Optionnel : libellé du bouton d'impression (si absent, pas de bouton impression)
  printLabel: { type: String, default: '' },
  // Endpoint d'impression (par défaut = pdfUrl, en mode "stream" plutôt que "download")
  printUrl: { type: String, default: '' },
  printParams: { type: Object, default: () => ({}) },
})

const { loading, downloadPdf, printPdf } = usePdfExport()
const open = ref(false)
const rootRef = ref(null)

function handleClickOutside(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

async function handlePdf() {
  open.value = false
  await downloadPdf(props.pdfUrl, props.pdfFilename, props.pdfParams)
}

function handleExcel() {
  open.value = false
  exportToExcel(props.excelFilename, props.excelColumns, props.excelRows)
}

async function handlePrint() {
  open.value = false
  await printPdf(props.printUrl || props.pdfUrl, props.printParams)
}
</script>

<style scoped>
.export-toolbar { position: relative; display: inline-block; }
.export-trigger {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: #ffffff;
  border: 1px solid #e7ecf3;
  color: #0f172a;
  font-size: 0.82rem;
  font-weight: 600;
  padding: 0.6rem 1rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s;
}
.export-trigger:hover:not(:disabled) {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59,130,246,0.12);
}
.export-trigger:disabled { opacity: 0.6; cursor: wait; }
.chev { color: #64748b; transition: transform 0.15s; }
.chev.rotated { transform: rotate(180deg); }

.export-menu {
  position: absolute;
  top: calc(100% + 0.4rem);
  right: 0;
  z-index: 30;
  min-width: 200px;
  background: #ffffff;
  border: 1px solid #e7ecf3;
  border-radius: 12px;
  box-shadow: 0 12px 32px rgba(15,23,42,0.14);
  padding: 0.4rem;
  animation: menuFade 0.12s ease-out;
}
@keyframes menuFade {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
.export-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  font-size: 0.82rem;
  font-weight: 600;
  color: #334155;
  padding: 0.55rem 0.6rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.12s;
}
.export-item:hover { background: #eff6ff; color: #1d4ed8; }
.export-sep { height: 1px; background: #e7ecf3; margin: 0.3rem 0.2rem; }
</style>
