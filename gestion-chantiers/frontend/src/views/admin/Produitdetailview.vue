<template>
  <div class="app-layout-clean">
    <main class="main-content-full">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div><span>Chargement...</span>
      </div>
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchProduit" class="btn btn-primary">Réessayer</button>
      </div>
      <template v-else-if="produit">
        <!-- Top Bar -->
        <div class="top-bar">
          <div class="breadcrumb">
            <span @click="$router.push({ name: 'AdminProduits' })" style="cursor:pointer;">Produits</span>
            &gt; <span class="active">{{ produit.nom }}</span>
          </div>
          <div class="action-buttons">
            <button class="btn btn-secondary" @click="$router.push({ name: 'AdminProduits' })">← Retour</button>
            <button class="btn btn-primary" @click="showEntreeModal = true">📥 Entrée stock</button>
          </div>
        </div>

        <!-- Header produit -->
        <div class="produit-header">
          <div class="produit-avatar-lg">{{ produit.nom[0].toUpperCase() }}</div>
          <div class="produit-header-info">
            <h1>{{ produit.nom }}</h1>
            <div class="meta-row">
              <span class="cat-badge">{{ produit.categorie }}</span>
              <span class="status-badge" :class="statutClass(produit.statut)">{{ statutLabel(produit.statut) }}</span>
              <span v-if="produit.alerte_stock" class="alerte-badge">⚠️ Stock bas</span>
            </div>
            <p v-if="produit.description" class="produit-desc-full">{{ produit.description }}</p>
          </div>
          <div class="produit-header-kpis">
            <div class="kpi-mini"><span>Prix unitaire</span><strong>{{ formatMAD(produit.prix_unitaire) }} / {{ produit.unite }}</strong></div>
            <div class="kpi-mini"><span>Stock actuel</span><strong :class="produit.alerte_stock ? 'text-red' : 'text-green'">{{ produit.stock_actuel }} {{ produit.unite }}</strong></div>
            <div class="kpi-mini"><span>Stock minimum</span><strong>{{ produit.stock_minimum }} {{ produit.unite }}</strong></div>
            <div class="kpi-mini"><span>Valeur stock</span><strong class="text-blue">{{ formatMAD(produit.valeur_stock) }}</strong></div>
          </div>
        </div>

        <!-- Onglets -->
        <div class="tabs-container">
          <button v-for="t in tabs" :key="t" class="tab-link" :class="{ active: activeTab === t.key }" @click="activeTab = t.key">{{ t.label }}</button>
        </div>

        <!-- Infos générales -->
        <div v-if="activeTab === 'info'" class="tab-card">
          <div class="info-grid">
            <div class="data-row"><span>Nom</span><strong>{{ produit.nom }}</strong></div>
            <div class="data-row"><span>Catégorie</span><strong>{{ produit.categorie }}</strong></div>
            <div class="data-row"><span>Unité de mesure</span><strong>{{ produit.unite }}</strong></div>
            <div class="data-row"><span>Prix unitaire</span><strong>{{ formatMAD(produit.prix_unitaire) }}</strong></div>
            <div class="data-row"><span>Stock actuel</span><strong>{{ produit.stock_actuel }} {{ produit.unite }}</strong></div>
            <div class="data-row"><span>Stock minimum</span><strong>{{ produit.stock_minimum }} {{ produit.unite }}</strong></div>
            <div class="data-row"><span>Emplacement</span><strong>{{ produit.emplacement || '—' }}</strong></div>
            <div class="data-row"><span>Valeur totale stock</span><strong class="text-blue">{{ formatMAD(produit.valeur_stock) }}</strong></div>
            <div class="data-row"><span>Total entrées</span><strong>{{ produit.total_entrees }} {{ produit.unite }}</strong></div>
            <div class="data-row"><span>Total sorties</span><strong>{{ produit.total_sorties }} {{ produit.unite }}</strong></div>
            <div class="data-row"><span>Ajouté le</span><strong>{{ produit.created_at }}</strong></div>
          </div>
        </div>

        <!-- Historique entrées -->
        <div v-else-if="activeTab === 'entrees'" class="tab-card">
          <div class="section-header">
            <h3>📥 Historique des entrées ({{ produit.entrees?.length || 0 }})</h3>
            <button class="btn btn-primary btn-sm" @click="showEntreeModal = true">+ Nouvelle entrée</button>
          </div>
          <div v-if="produit.entrees?.length" class="table-responsive">
            <table class="data-table">
              <thead>
                <tr><th>Date</th><th>Quantité</th><th>Prix unitaire</th><th>N° Facture</th><th>Fournisseur</th><th>Observations</th></tr>
              </thead>
              <tbody>
                <tr v-for="e in produit.entrees" :key="e.id">
                  <td>{{ e.date_entree }}</td>
                  <td><strong class="text-green">+{{ e.quantite }} {{ produit.unite }}</strong></td>
                  <td>{{ formatMAD(e.prix_unitaire) }}</td>
                  <td>{{ e.numero_facture || '—' }}</td>
                  <td>{{ e.fournisseur || '—' }}</td>
                  <td>{{ e.observations || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state-sm">Aucune entrée enregistrée.</div>
        </div>

        <!-- Historique sorties / transferts -->
        <div v-else-if="activeTab === 'sorties'" class="tab-card">
          <h3>📤 Historique des sorties ({{ produit.sorties?.length || 0 }})</h3>
          <div v-if="produit.sorties?.length" class="table-responsive">
            <table class="data-table">
              <thead>
                <tr><th>Date</th><th>Quantité</th><th>Chantier</th><th>Bénéficiaire</th><th>Observations</th></tr>
              </thead>
              <tbody>
                <tr v-for="s in produit.sorties" :key="s.id">
                  <td>{{ s.date_sortie }}</td>
                  <td><strong class="text-red">-{{ s.quantite }} {{ produit.unite }}</strong></td>
                  <td>{{ s.chantier || '—' }}</td>
                  <td>{{ s.beneficiaire || '—' }}</td>
                  <td>{{ s.observations || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state-sm">Aucune sortie enregistrée.</div>
        </div>

      </template>
    </main>

    <!-- Modal Entrée Stock -->
    <div v-if="showEntreeModal" class="modal-overlay" @click.self="showEntreeModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>📥 Entrée de stock – {{ produit?.nom }}</h3>
          <button class="modal-close" @click="showEntreeModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Quantité *</label>
            <input v-model.number="entreeForm.quantite" type="number" min="1" class="form-input" />
          </div>
          <div class="form-group">
            <label>Date d'entrée *</label>
            <input v-model="entreeForm.date_entree" type="date" class="form-input" />
          </div>
          <div class="form-group">
            <label>Prix unitaire (MAD)</label>
            <input v-model.number="entreeForm.prix_unitaire" type="number" min="0" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label>N° Facture</label>
            <input v-model="entreeForm.numero_facture" class="form-input" />
          </div>
          <p v-if="entreeError" class="form-error">{{ entreeError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showEntreeModal = false">Annuler</button>
          <button class="btn btn-primary" @click="saveEntree" :disabled="saving">
            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const produitId = route.params.id

const produit = ref(null)
const loading = ref(true)
const error = ref(null)
const activeTab = ref('info')
const showEntreeModal = ref(false)
const saving = ref(false)
const entreeError = ref('')

const tabs = [
  { key: 'info', label: 'ℹ️ Informations' },
  { key: 'entrees', label: '📥 Entrées' },
  { key: 'sorties', label: '📤 Sorties / Transferts' },
]

const entreeForm = reactive({
  quantite: 1, prix_unitaire: 0, date_entree: new Date().toISOString().split('T')[0],
  numero_facture: '', observations: ''
})

async function fetchProduit() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get(`/admin/produits/${produitId}`)
    produit.value = data.data
    entreeForm.prix_unitaire = produit.value.prix_unitaire
  } catch (e) {
    error.value = 'Impossible de charger le produit.'
  } finally {
    loading.value = false
  }
}

async function saveEntree() {
  entreeError.value = ''
  if (!entreeForm.quantite || entreeForm.quantite < 1) { entreeError.value = 'Quantité ≥ 1 requise.'; return }
  saving.value = true
  try {
    await api.post(`/admin/produits/${produitId}/entree`, entreeForm)
    showEntreeModal.value = false
    await fetchProduit()
  } catch (e) {
    entreeError.value = e.response?.data?.message || 'Erreur.'
  } finally {
    saving.value = false
  }
}

function formatMAD(n) {
  if (n === null || n === undefined) return '—'
  return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
}
function statutClass(s) { return { disponible: 'st-disponible', rupture: 'st-rupture', archive: 'st-archive' }[s] || '' }
function statutLabel(s) { return { disponible: 'Disponible', rupture: 'Rupture', archive: 'Archivé' }[s] || s }

onMounted(fetchProduit)
</script>

<style scoped>
.app-layout-clean { min-height: 100vh; background: #f4f7fc; font-family: ui-sans-serif, system-ui, sans-serif; margin: -42px !important; }
.main-content-full { padding: 0 1.5rem; max-width: 100%; }
.loading-state { display: flex; align-items: center; gap: 1rem; justify-content: center; min-height: 300px; color: #64748b; }
.spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.error-state { text-align: center; padding: 2rem; color: #e11d48; }

.top-bar { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0 0.5rem; }
.breadcrumb { font-size: 0.85rem; color: #64748b; }
.breadcrumb .active { color: #0f172a; font-weight: 600; }
.action-buttons { display: flex; gap: 0.5rem; }

.produit-header { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid #e2e8f0; display: flex; align-items: flex-start; gap: 1.5rem; margin: 1rem 0 1.5rem; }
.produit-avatar-lg { width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #2563eb, #7c3aed); color: #fff; font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.produit-header-info { flex: 1; }
.produit-header-info h1 { margin: 0 0 0.5rem; font-size: 1.6rem; font-weight: 800; color: #0f172a; }
.meta-row { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.produit-desc-full { color: #64748b; font-size: 0.9rem; margin: 0.5rem 0 0; }
.produit-header-kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; min-width: 260px; }
.kpi-mini { background: #f8fafc; border-radius: 10px; padding: 0.75rem; border: 1px solid #e2e8f0; }
.kpi-mini span { display: block; font-size: 0.72rem; color: #64748b; margin-bottom: 0.2rem; }
.kpi-mini strong { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
.text-green { color: #059669 !important; }
.text-red { color: #e11d48 !important; }
.text-blue { color: #2563eb !important; }

.tabs-container { display: flex; gap: 0.25rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; }
.tab-link { padding: 0.75rem 1.25rem; font-size: 0.85rem; font-weight: 600; color: #64748b; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; margin-bottom: -2px; transition: all 0.2s; }
.tab-link.active { color: #2563eb; border-bottom-color: #2563eb; }

.tab-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
.info-grid { display: flex; flex-direction: column; }
.data-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.data-row:last-child { border-bottom: none; }
.data-row span { color: #64748b; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.section-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.empty-state-sm { text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.9rem; }

.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.data-table th { padding: 0.6rem 0.75rem; border-bottom: 2px solid #e2e8f0; text-align: left; color: #475569; font-weight: 600; }
.data-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f1f5f9; }

.cat-badge { background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
.alerte-badge { font-size: 0.75rem; color: #b45309; font-weight: 600; background: #fef3c7; padding: 2px 8px; border-radius: 10px; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; }
.st-disponible { background: #d1fae5; color: #065f46; }
.st-rupture { background: #fee2e2; color: #991b1b; }
.st-archive { background: #f1f5f9; color: #64748b; }

.btn { padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: all 0.2s; }
.btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn-sm { padding: 0.3rem 0.75rem; font-size: 0.8rem; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-box { background: #fff; border-radius: 16px; width: 440px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.modal-header h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }
.modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
.form-input { padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; background: #f8fafc; width: 100%; box-sizing: border-box; }
.form-error { color: #e11d48; font-size: 0.85rem; }
</style>