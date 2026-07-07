```vue
<template>
  <div class="mv-wrap">

    <!-- Header -->
    <div class="mv-header">
      <div>
        <h1>Mouvements de Stock</h1>
        <p>Enregistrez et tracez les flux de matériel entre vos sites logistiques et vos chantiers</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-entree" @click="openModal('entree')">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
          Entrée stock
        </button>
        <button class="btn btn-sortie" @click="openModal('sortie')">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
          Sortie stock
        </button>
        <button class="btn btn-transfert" @click="openModal('transfert')">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14M7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          Transfert
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card stat-entree">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M5 10l7-7 7 7"/></svg></div>
        <div><strong>{{ stats.totalEntrees }}</strong><span>Entrées (qté)</span></div>
      </div>
      <div class="stat-card stat-sortie">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21V3M5 14l7 7 7-7"/></svg></div>
        <div><strong>{{ stats.totalSorties }}</strong><span>Sorties (qté)</span></div>
      </div>
      <div class="stat-card stat-transfert">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17H16M4 17l4 4M4 17l4-4"/></svg></div>
        <div><strong>{{ stats.totalTransferts }}</strong><span>Transferts (qté)</span></div>
      </div>
      <div class="stat-card stat-valeur">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/></svg></div>
        <div><strong>{{ fmtMAD(stats.valeurSorties) }}</strong><span>Valeur sorties</span></div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
      <div class="type-tabs">
        <button v-for="t in typeOptions" :key="t.val" :class="['type-tab', t.val, filtreType === t.val && 'active']" @click="filtreType = t.val; fetchMouvements()">
          {{ t.label }}
        </button>
      </div>
      <select v-model="filtreStock" @change="fetchMouvements" class="filter-select">
        <option value="">Tous les dépôts</option>
        <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }}</option>
      </select>
      <input v-model="dateDebut" type="date" @change="fetchMouvements" class="filter-date" />
      <input v-model="dateFin" type="date" @change="fetchMouvements" class="filter-date" />
    </div>

    <!-- Tableau des mouvements -->
    <div class="table-card">
      <div v-if="loading" class="loading-state"><div class="spinner"></div><span>Chargement des écritures...</span></div>
      <div v-else-if="mouvements.length" class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Produit</th>
              <th>Qté</th>
              <th>Dépôt source</th>
              <th>Affectation / Provenance</th>
              <th>Valeur</th>
              <th>Références & Mémos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in mouvements" :key="m.id" class="mvt-row" :class="m.type">
              <td class="date-cell">{{ m.date_label }}</td>
              <td>
                <span class="badge-type" :class="m.type">{{ m.type_label }}</span>
              </td>
              <td>
                <div class="product-cell">
                  <strong>{{ m.produit }}</strong>
                  <span class="sub">{{ m.unite }}</span>
                </div>
              </td>
              <td>
                <strong :class="m.type === 'entree' ? 'text-green' : m.type === 'sortie' ? 'text-red' : 'text-blue'">
                  {{ m.type === 'entree' ? '+' : m.type === 'sortie' ? '−' : '⇄' }} {{ m.quantite }}
                </strong>
              </td>
              <td class="text-muted">
                {{ m.type === 'transfert' ? m.depot_source : (m.depot || '—') }}
              </td>
              <td class="text-muted">
                <span v-if="m.type === 'entree'">{{ m.fournisseur || '—' }}</span>
                <span v-else-if="m.type === 'sortie'">
                  <div class="destination-layout">
                    <span v-if="m.projet" class="meta-label">Projet : {{ m.projet }}</span>
                    <span v-if="m.chantier" class="meta-site">Site : {{ m.chantier }}</span>
                    <span v-if="!m.projet && !m.chantier">{{ m.beneficiaire || '—' }}</span>
                  </div>
                </span>
                <span v-else>{{ m.depot_destination || '—' }}</span>
              </td>
              <td><strong>{{ fmtMAD(m.valeur) }}</strong></td>
              <td class="text-muted">
                <div class="memo-cell">
                  <span v-if="m.numero_facture" class="facture-tag">Réf : {{ m.numero_facture }}</span>
                  <span v-if="m.observations" class="obs-text">{{ m.observations }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3>Aucune écriture comptable</h3>
        <p>Aucun mouvement de stock n'a été enregistré sur la période ou pour les dépôts sélectionnés.</p>
      </div>
    </div>

    <!-- ───── MODAL ENTREE ───────────────────────────────────── -->
    <div v-if="showModal === 'entree'" class="modal-overlay" @click.self="showModal = null">
      <div class="modal-box">
        <div class="modal-header entree">
          <h3>Nouvelle entrée de stock</h3>
          <button class="modal-close" @click="showModal = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group col-span-2">
              <label>Dépôt de destination *</label>
              <select v-model="entreeForm.stock_id" class="form-input">
                <option value="">-- Sélectionner un dépôt --</option>
                <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }} ({{ s.code }})</option>
              </select>
            </div>

            <!-- Fournisseur avec @change pour charger ses produits -->
            <div class="form-group col-span-2">
              <label>Fournisseur *</label>
              <select v-model="entreeForm.fournisseur_id" @change="loadProduitsByFournisseur(entreeForm.fournisseur_id)" class="form-input">
                <option value="">-- Sélectionner un fournisseur --</option>
                <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.nom }}</option>
              </select>
            </div>

            <!-- Produit filtré selon fournisseur -->
            <div class="form-group col-span-2">
              <label>Produit / Référence *</label>
              <select v-model="entreeForm.produit_id" class="form-input">
                <option value="">-- Sélectionner un produit --</option>
                <option v-if="produitsParFournisseur.length === 0" value="" disabled>Aucun produit associé</option>
                <option v-for="p in produitsParFournisseur" :key="p.id" :value="p.id">
                  {{ p.nom }} ({{ p.unite }}) - {{ fmtMAD(p.prix_unitaire) }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Quantité reçue *</label>
              <input v-model.number="entreeForm.quantite" type="number" min="1" class="form-input" />
            </div>
            <div class="form-group">
              <label>Prix unitaire (MAD)</label>
              <input v-model.number="entreeForm.prix_unitaire" type="number" min="0" step="0.01" class="form-input" placeholder="0.00" />
            </div>
            <div class="form-group">
              <label>Stock de sécurité (Alerte)</label>
              <input v-model.number="entreeForm.stock_minimum" type="number" min="0" class="form-input" placeholder="0" />
            </div>
            <div class="form-group">
              <label>Date de réception *</label>
              <input v-model="entreeForm.date_entree" type="date" class="form-input" />
            </div>
            <div class="form-group">
              <label>N° Facture / Bon de livraison</label>
              <input v-model="entreeForm.numero_facture" class="form-input" placeholder="Ex : BL-2026-001" />
            </div>
            <div class="form-group col-span-2">
              <label>Observations</label>
              <textarea v-model="entreeForm.observations" class="form-input" rows="2" placeholder="Notes de réception, état de la livraison..."></textarea>
            </div>
          </div>
          <p v-if="modalError" class="form-error">{{ modalError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = null">Annuler</button>
          <button class="btn btn-entree-action" @click="saveEntree" :disabled="saving">
            {{ saving ? 'Validation...' : 'Valider l\'entrée de stock' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ───── MODAL SORTIE ───────────────────────────────────── -->
    <div v-if="showModal === 'sortie'" class="modal-overlay" @click.self="showModal = null">
      <div class="modal-box">
        <div class="modal-header sortie">
          <h3>Nouvelle sortie de stock</h3>
          <button class="modal-close" @click="showModal = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group col-span-2">
              <label>Dépôt d'origine *</label>
              <select v-model="sortieForm.stock_id" @change="loadStockProduits" class="form-input">
                <option value="">-- Sélectionner un dépôt source --</option>
                <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }} ({{ s.code }})</option>
              </select>
            </div>
            <div class="form-group col-span-2">
              <label>Produit à sortir *</label>
              <select v-model="sortieForm.produit_id" class="form-input" :disabled="!sortieForm.stock_id">
                <option value="">-- Sélectionner un produit --</option>
                <option v-for="p in stockProduits" :key="p.id" :value="p.id">
                  {{ p.nom }} (Disponible : {{ p.quantite }} {{ p.unite }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Quantité à prélever *</label>
              <input v-model.number="sortieForm.quantite" type="number" min="1" class="form-input" />
            </div>
            <div class="form-group">
              <label>Date de sortie *</label>
              <input v-model="sortieForm.date_sortie" type="date" class="form-input" />
            </div>
            <div class="form-group col-span-2">
              <label>Chantier de destination *</label>
              <select v-model="sortieForm.chantier_id" @change="loadProjets" class="form-input">
                <option value="">-- Sélectionner un chantier --</option>
                <option v-for="c in chantiers" :key="c.id" :value="c.id">
                  {{ c.reference }} - {{ c.nom }}
                </option>
              </select>
            </div>
            <div class="form-group col-span-2">
              <label>Projet rattaché *</label>
              <select v-model="sortieForm.projet_id" class="form-input" :disabled="!sortieForm.chantier_id">
                <option value="">-- Sélectionner un projet --</option>
                <option v-for="p in projets" :key="p.id" :value="p.id">
                  {{ p.nom }}
                </option>
              </select>
            </div>
            <div class="form-group col-span-2">
              <label>Observations / Notes de décharge</label>
              <textarea v-model="sortieForm.observations" class="form-input" rows="2" placeholder="Précisez le nom du conducteur ou du matériel concerné..."></textarea>
            </div>
          </div>
          <p v-if="modalError" class="form-error">{{ modalError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = null">Annuler</button>
          <button class="btn btn-sortie-action" @click="saveSortie" :disabled="saving">
            {{ saving ? 'Validation...' : 'Valider la sortie de stock' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ───── MODAL TRANSFERT ────────────────────────────────── -->
    <div v-if="showModal === 'transfert'" class="modal-overlay" @click.self="showModal = null">
      <div class="modal-box">
        <div class="modal-header transfert">
          <h3>Bon de transfert inter-dépôts</h3>
          <button class="modal-close" @click="showModal = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>Dépôt source *</label>
              <select v-model="transfertForm.stock_source_id" @change="loadStockProduitsTransfert" class="form-input">
                <option value="">-- Source --</option>
                <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Dépôt de destination *</label>
              <select v-model="transfertForm.stock_destination_id" class="form-input">
                <option value="">-- Destination --</option>
                <option v-for="s in stocks.filter(s => s.id !== transfertForm.stock_source_id)" :key="s.id" :value="s.id">{{ s.nom }}</option>
              </select>
            </div>
            <div class="form-group col-span-2">
              <label>Produit à transférer *</label>
              <select v-model="transfertForm.produit_id" class="form-input" :disabled="!transfertForm.stock_source_id">
                <option value="">-- Sélectionner l'article --</option>
                <option v-for="p in transfertProduits" :key="p.id" :value="p.id">
                  {{ p.nom }} (Disponible : {{ p.quantite }} {{ p.unite }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Quantité à déplacer *</label>
              <input v-model.number="transfertForm.quantite" type="number" min="1" class="form-input" />
            </div>
            <div class="form-group">
              <label>Date effective *</label>
              <input v-model="transfertForm.date_transfert" type="date" class="form-input" />
            </div>
            <div class="form-group col-span-2">
              <label>Observations</label>
              <textarea v-model="transfertForm.observations" class="form-input" rows="2" placeholder="Motif du transfert, société de transport..."></textarea>
            </div>
          </div>
          <p v-if="modalError" class="form-error">{{ modalError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = null">Annuler</button>
          <button class="btn btn-transfert-action" @click="saveTransfert" :disabled="saving">
            {{ saving ? 'Exécution...' : 'Exécuter le transfert' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import api from '@/services/api'

// ─── Références ──────────────────────────────────────────────
const mouvements = ref([])
const stocks = ref([])
const produits = ref([])
const chantiers = ref([])
const fournisseurs = ref([])
const stockProduits = ref([])
const transfertProduits = ref([])
const projets = ref([])
const produitsParFournisseur = ref([])

const loading = ref(true)
const saving = ref(false)
const filtreType = ref('')
const filtreStock = ref('')
const dateDebut = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0])
const dateFin = ref(new Date().toISOString().split('T')[0])
const showModal = ref(null)
const modalError = ref('')

// ─── Options ─────────────────────────────────────────────────
const typeOptions = [
  { val: '', label: 'Tous les flux' },
  { val: 'entree', label: 'Entrées' },
  { val: 'sortie', label: 'Sorties' },
  { val: 'transfert', label: 'Transferts' },
]

const today = () => new Date().toISOString().split('T')[0]

// ─── Formulaires ─────────────────────────────────────────────
const entreeForm = reactive({
  stock_id: '',
  fournisseur_id: '',
  produit_id: '',
  quantite: 1,
  prix_unitaire: 0,
  stock_minimum: 0,
  date_entree: today(),
  numero_facture: '',
  observations: '',
})

const sortieForm = reactive({
  stock_id: '',
  produit_id: '',
  chantier_id: '',
  projet_id: '',
  quantite: 1,
  date_sortie: today(),
  beneficiaire: '',
  observations: '',
})

const transfertForm = reactive({
  stock_source_id: '',
  stock_destination_id: '',
  produit_id: '',
  quantite: 1,
  date_transfert: today(),
  observations: '',
})

// ─── Statistiques ────────────────────────────────────────────
const stats = computed(() => ({
  totalEntrees: mouvements.value.filter(m => m.type === 'entree').reduce((s, m) => s + m.quantite, 0),
  totalSorties: mouvements.value.filter(m => m.type === 'sortie').reduce((s, m) => s + m.quantite, 0),
  totalTransferts: mouvements.value.filter(m => m.type === 'transfert').reduce((s, m) => s + m.quantite, 0),
  valeurSorties: mouvements.value.filter(m => m.type === 'sortie').reduce((s, m) => s + (m.valeur || 0), 0),
}))

// ─── API calls ───────────────────────────────────────────────

async function fetchMouvements() {
  loading.value = true
  try {
    const params = { date_debut: dateDebut.value, date_fin: dateFin.value }
    if (filtreType.value) params.type = filtreType.value
    if (filtreStock.value) params.stock_id = filtreStock.value
    const { data } = await api.get('/admin/mouvements', { params })
    mouvements.value = data.data
  } finally {
    loading.value = false
  }
}

async function loadStockProduits() {
  if (!sortieForm.stock_id) { stockProduits.value = []; return }
  try {
    const { data } = await api.get(`/admin/stocks/${sortieForm.stock_id}`)
    stockProduits.value = data.data.produits || []
  } catch {
    stockProduits.value = []
  }
}

async function loadStockProduitsTransfert() {
  if (!transfertForm.stock_source_id) { transfertProduits.value = []; return }
  try {
    const { data } = await api.get(`/admin/stocks/${transfertForm.stock_source_id}`)
    transfertProduits.value = data.data.produits || []
  } catch {
    transfertProduits.value = []
  }
}

async function loadProduitsByFournisseur(fournisseurId) {
  if (!fournisseurId) {
    produitsParFournisseur.value = []
    entreeForm.produit_id = ''
    entreeForm.prix_unitaire = 0
    return
  }
  try {
    const { data } = await api.get(`/admin/fournisseurs/${fournisseurId}/produits`)
    produitsParFournisseur.value = data.data
    entreeForm.produit_id = ''
    entreeForm.prix_unitaire = 0
  } catch (e) {
    produitsParFournisseur.value = []
    console.error('Erreur chargement produits du fournisseur', e)
  }
}

async function loadProjets() {
  sortieForm.projet_id = ''
  projets.value = []
  if (!sortieForm.chantier_id) return
  try {
    const { data } = await api.get(`/admin/chantiers/${sortieForm.chantier_id}/projets`)
    projets.value = data.data
  } catch {
    projets.value = []
  }
}

// ⭐ Nouvelle fonction : récupérer le stock minimum pour un produit dans un dépôt
async function fetchStockMinimum() {
  if (!entreeForm.stock_id || !entreeForm.produit_id) {
    return
  }
  try {
    const { data } = await api.get(`/admin/stocks/${entreeForm.stock_id}/produits/${entreeForm.produit_id}/pivot`)
    entreeForm.stock_minimum = data.stock_minimum ?? 0
  } catch (e) {
    entreeForm.stock_minimum = 0
    console.warn('Produit non trouvé dans ce dépôt ou erreur', e)
  }
}

// ─── Watchers ─────────────────────────────────────────────────
// Préremplir le prix unitaire lorsque le produit est sélectionné
watch(() => entreeForm.produit_id, (newId) => {
  if (!newId) {
    entreeForm.prix_unitaire = 0
    return
  }
  const produit = produitsParFournisseur.value.find(p => p.id === newId)
  if (produit) {
    entreeForm.prix_unitaire = produit.prix_unitaire
  }
})

// ⭐ Watcher combiné pour récupérer le stock minimum
watch(
  () => [entreeForm.stock_id, entreeForm.produit_id],
  ([newStock, newProduit]) => {
    if (newStock && newProduit) {
      fetchStockMinimum()
    } else {
      entreeForm.stock_minimum = 0
    }
  }
)

// ─── Gestion des modals ──────────────────────────────────────
function openModal(type) {
  modalError.value = ''
  if (type === 'entree') {
    Object.assign(entreeForm, {
      stock_id: '',
      fournisseur_id: '',
      produit_id: '',
      quantite: 1,
      prix_unitaire: 0,
      stock_minimum: 0,
      date_entree: today(),
      numero_facture: '',
      observations: '',
    })
    produitsParFournisseur.value = []
  }
  if (type === 'sortie') {
    Object.assign(sortieForm, {
      stock_id: '',
      produit_id: '',
      chantier_id: '',
      projet_id: '',
      quantite: 1,
      date_sortie: today(),
      beneficiaire: '',
      observations: '',
    })
    stockProduits.value = []
  }
  if (type === 'transfert') {
    Object.assign(transfertForm, {
      stock_source_id: '',
      stock_destination_id: '',
      produit_id: '',
      quantite: 1,
      date_transfert: today(),
      observations: '',
    })
    transfertProduits.value = []
  }
  showModal.value = type
}

// ─── Sauvegardes ─────────────────────────────────────────────

async function saveEntree() {
  modalError.value = ''
  if (!entreeForm.stock_id) { modalError.value = 'Sélectionnez un dépôt.'; return }
  if (!entreeForm.fournisseur_id) { modalError.value = 'Sélectionnez un fournisseur.'; return }
  if (!entreeForm.produit_id) { modalError.value = 'Sélectionnez un produit.'; return }
  if (!entreeForm.quantite || entreeForm.quantite < 1) { modalError.value = 'Quantité invalide.'; return }
  saving.value = true
  try {
    await api.post('/admin/mouvements/entree', entreeForm)
    showModal.value = null
    await fetchMouvements()
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Erreur lors de la validation.'
  } finally {
    saving.value = false
  }
}

async function saveSortie() {
  modalError.value = ''
  if (!sortieForm.stock_id) { modalError.value = 'Sélectionnez un dépôt source.'; return }
  if (!sortieForm.produit_id) { modalError.value = 'Sélectionnez un produit.'; return }
  if (!sortieForm.chantier_id) { modalError.value = 'Sélectionnez un chantier.'; return }
  if (!sortieForm.quantite || sortieForm.quantite < 1) { modalError.value = 'Quantité invalide.'; return }
  saving.value = true
  try {
    await api.post('/admin/mouvements/sortie', sortieForm)
    showModal.value = null
    await fetchMouvements()
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Erreur lors de la validation.'
  } finally {
    saving.value = false
  }
}

async function saveTransfert() {
  modalError.value = ''
  if (!transfertForm.stock_source_id || !transfertForm.stock_destination_id) { modalError.value = 'Sélectionnez les deux dépôts.'; return }
  if (!transfertForm.produit_id) { modalError.value = 'Sélectionnez un produit.'; return }
  if (!transfertForm.quantite || transfertForm.quantite < 1) { modalError.value = 'Quantité invalide.'; return }
  saving.value = true
  try {
    await api.post('/admin/mouvements/transfert', transfertForm)
    showModal.value = null
    await fetchMouvements()
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Erreur lors de la validation.'
  } finally {
    saving.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────
const fmtMAD = n => n == null ? '—' : new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)

// ─── Initialisation ──────────────────────────────────────────
onMounted(async () => {
  try {
    const [s, p, c, f] = await Promise.all([
      api.get('/admin/stocks'),
      api.get('/admin/produits'),
      api.get('/admin/chantiers'),
      api.get('/admin/fournisseurs').catch(() => ({ data: { data: [] } })),
    ])
    stocks.value = s.data.data
    produits.value = p.data.data
    chantiers.value = c.data.data
    fournisseurs.value = f.data.data
  } catch (e) {
    console.error('Erreur initialisation de la page', e)
  }
  await fetchMouvements()
})
</script>
<style scoped>
.mv-wrap { min-height: 100vh; background: #f8fafc; margin: -42px !important; padding: 0 2rem; font-family: ui-sans-serif, system-ui, sans-serif; }
.mv-header { display: flex; justify-content: space-between; align-items: center; padding: 2rem 0 1.5rem; flex-wrap: wrap; gap: 1rem; }
.mv-header h1 { margin: 0 0 .25rem; font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.mv-header p { margin: 0; color: #64748b; font-size: .95rem; }
.header-actions { display: flex; gap: .75rem; flex-wrap: wrap; }

.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.01); }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 22px; height: 22px; }
.stat-card.stat-entree .stat-icon { background: #f0fdf4; color: #16a34a; }
.stat-card.stat-sortie .stat-icon { background: #fff1f2; color: #e11d48; }
.stat-card.stat-transfert .stat-icon { background: #eff6ff; color: #2563eb; }
.stat-card.stat-valeur .stat-icon { background: #fff7ed; color: #ea580c; }
.stat-card strong { display: block; font-size: 1.5rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
.stat-card span { font-size: .82rem; color: #64748b; font-weight: 500; }

.filters-bar { display: flex; gap: .75rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center; }
.type-tabs { display: flex; gap: .25rem; background: #f1f5f9; padding: 4px; border-radius: 10px; border: 1px solid #e2e8f0; }
.type-tab { padding: .45rem 1.1rem; border-radius: 7px; font-size: .85rem; font-weight: 600; border: none; cursor: pointer; background: transparent; color: #64748b; transition: all .15s; }
.type-tab.active { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
.filter-select, .filter-date { padding: .6rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .88rem; background: #fff; color: #334155; }
.filter-select:focus, .filter-date:focus { outline: none; border-color: #cbd5e1; }

.table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 2.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
.data-table th { padding: .85rem 1rem; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569; font-weight: 600; font-size: .76rem; text-transform: uppercase; letter-spacing: .05em; background: #f8fafc; }
.data-table td { padding: .85rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
.data-table tbody tr:hover td { background: #f8fafc; }
.mvt-row.entree td:first-child { border-left: 3px solid #16a34a; }
.mvt-row.sortie td:first-child { border-left: 3px solid #e11d48; }
.mvt-row.transfert td:first-child { border-left: 3px solid #2563eb; }
.date-cell { color: #475569; font-size: .82rem; white-space: nowrap; font-weight: 500; }
.product-cell strong { display: block; color: #0f172a; }
.badge-type { padding: 3px 10px; border-radius: 6px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
.badge-type.entree { background: #dcfce7; color: #166534; }
.badge-type.sortie { background: #fee2e2; color: #991b1b; }
.badge-type.transfert { background: #dbeafe; color: #1e40af; }
.sub { font-size: .75rem; color: #94a3b8; }
.destination-layout { display: flex; flex-direction: column; gap: 2px; font-size: .85rem; }
.meta-label { font-weight: 600; color: #334155; }
.meta-site { color: #64748b; font-size: .8rem; }
.memo-cell { display: flex; flex-direction: column; gap: 4px; }
.facture-tag { font-family: monospace; font-size: .75rem; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 4px; width: max-content; }
.obs-text { font-size: .8rem; color: #64748b; font-style: italic; }
.text-green { color: #16a34a; }
.text-red { color: #e11d48; }
.text-blue { color: #2563eb; }
.text-muted { color: #64748b; }

.loading-state { display: flex; align-items: center; gap: 1rem; justify-content: center; min-height: 200px; color: #64748b; font-size: .9rem; }
.spinner { width: 24px; height: 24px; border: 2px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { text-align: center; padding: 4rem 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.empty-icon-wrap { width: 48px; height: 48px; background: #f1f5f9; color: #94a3b8; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
.empty-icon-wrap svg { width: 22px; height: 22px; }
.empty-state h3 { margin: 0 0 .35rem; color: #0f172a; font-size: 1.05rem; }
.empty-state p { color: #64748b; margin: 0; font-size: .88rem; max-width: 360px; line-height: 1.4; }

.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1rem; font-size: .85rem; font-weight: 600; border-radius: 6px; cursor: pointer; border: 1px solid transparent; transition: all .15s ease; }
.btn-secondary { background: #fff; color: #475569; border-color: #e2e8f0; }
.btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
.btn-entree { background: #16a34a; color: #fff; }
.btn-entree:hover { background: #15803d; }
.btn-sortie { background: #e11d48; color: #fff; }
.btn-sortie:hover { background: #be123c; }
.btn-transfert { background: #2563eb; color: #fff; }
.btn-transfert:hover { background: #1d4ed8; }
.btn-icon { width: 14px; height: 14px; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.35); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-box { background: #fff; border-radius: 12px; width: 540px; max-width: 95vw; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-header.entree { border-top: 4px solid #16a34a; }
.modal-header.sortie { border-top: 4px solid #e11d48; }
.modal-header.transfert { border-top: 4px solid #2563eb; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #0f172a; }
.modal-close { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #94a3b8; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .5rem; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
.btn-entree-action { background: #16a34a; color: #fff; border: 1px solid #16a34a; border-radius: 6px; font-size: .85rem; font-weight: 600; padding: .55rem 1rem; cursor: pointer; }
.btn-entree-action:hover { background: #15803d; }
.btn-sortie-action { background: #e11d48; color: #fff; border: 1px solid #e11d48; border-radius: 6px; font-size: .85rem; font-weight: 600; padding: .55rem 1rem; cursor: pointer; }
.btn-sortie-action:hover { background: #be123c; }
.btn-transfert-action { background: #2563eb; color: #fff; border: 1px solid #2563eb; border-radius: 6px; font-size: .85rem; font-weight: 600; padding: .55rem 1rem; cursor: pointer; }
.btn-transfert-action:hover { background: #1d4ed8; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.col-span-2 { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group label { font-size: .82rem; font-weight: 600; color: #475569; }
.form-input { padding: .6rem .75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: .88rem; color: #0f172a; background: #fff; width: 100%; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 1px #2563eb; }
.form-input:disabled { opacity: .5; background: #f1f5f9; cursor: not-allowed; }
.form-error { color: #e11d48; font-size: .82rem; margin-top: .5rem; font-weight: 500; }

@media (max-width: 768px) { .stat-grid { grid-template-columns: 1fr 1fr; } .form-grid { grid-template-columns: 1fr; } .col-span-2 { grid-column: span 1; } }
</style>
```