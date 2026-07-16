<template>
  <div class="sv-wrap">

    <div class="sv-header">
      <div>
        <h1>Gestion des Stocks</h1>
        <p>Gérez vos dépôts et visualisez le stock par emplacement</p>
      </div>
      <div class="sv-header-actions">
        <button class="btn btn-primary" @click="openCreateDepot">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Nouveau dépôt
        </button>
        <button class="btn btn-outline" @click="showCustomPrint = true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Liste stock personnalisée
        </button>
        <ExportToolbar
          pdf-url="/admin/exports/stocks"
          :pdf-params="{}"
          pdf-filename="stocks"
          :excel-columns="excelColumns"
          :excel-rows="flatStockLines"
          excel-filename="stocks"
        />
      </div>
    </div>

    <!-- ─── MODAL IMPRESSION PERSONNALISÉE ─── -->
    <div v-if="showCustomPrint" class="modal-overlay" @click.self="showCustomPrint = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>Liste de stock personnalisée</h3>
          <button class="modal-close" @click="showCustomPrint = false">✕</button>
        </div>
        <div class="modal-body">
          <p class="text-muted" style="font-size:.85rem; margin-bottom:.75rem;">Choisissez les colonnes à inclure sur le document imprimé.</p>
          <div class="custom-print-columns">
            <label v-for="col in customPrintColumnOptions" :key="col.key" class="checkbox-pill">
              <input type="checkbox" :value="col.key" v-model="customPrintColumns" />
              {{ col.label }}
            </label>
          </div>
          <div class="form-group" style="margin-top:1rem;">
            <label>Dépôt (optionnel)</label>
            <select v-model="customPrintStockId" class="form-input">
              <option value="">Tous les dépôts</option>
              <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }}</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCustomPrint = false">Annuler</button>
          <button class="btn btn-primary" :disabled="!customPrintColumns.length" @click="launchCustomPrint">Imprimer</button>
        </div>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        </div>
        <div><strong>{{ stats.nbDepots }}</strong><span>Dépôts</span></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
        </div>
        <div><strong>{{ stats.nbProduits }}</strong><span>Références</span></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <div><strong>{{ stats.nbAlertes }}</strong><span>Alertes stock</span></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
        <div><strong>{{ fmtMAD(stats.valeurTotale) }}</strong><span>Valeur totale</span></div>
      </div>
    </div>

    <div class="tabs">
      <button :class="['tab', activeTab === 'depots' && 'active']" @click="activeTab = 'depots'">Dépôts</button>
      <button :class="['tab', activeTab === 'produits' && 'active']" @click="activeTab = 'produits'">Stock par produit</button>
    </div>

    <div v-if="activeTab === 'depots'">
      <div v-if="loading" class="loading-state"><div class="spinner"></div><span>Chargement...</span></div>
      <div v-else-if="stocks.length" class="depots-grid">
        <div v-for="s in stocks" :key="s.id" class="depot-card" @click="voirDepot(s)">
          <div class="depot-top">
            <div class="depot-icon" :class="s.type">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div>
              <strong>{{ s.nom }}</strong>
              <span class="depot-code">{{ s.code }}</span>
            </div>
            <span class="badge-type" :class="s.type">{{ s.type_label }}</span>
          </div>
          <div v-if="s.adresse" class="depot-adresse">
            <svg class="inline-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            {{ s.adresse }}
          </div>
          <div class="depot-stats">
            <div class="depot-stat">
              <span>{{ s.nb_produits }}</span>
              <label>Références</label>
            </div>
          </div>
          <div class="depot-actions" @click.stop>
            <button class="btn btn-sm btn-outline" @click="voirDepot(s)">Voir le stock</button>
            <!-- Nouveaux boutons d'action style modernisé -->
            <button class="btn-action edit" @click="openEditDepot(s)" title="Modifier">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="btn-action delete" @click="supprimerDepot(s.id)" title="Supprimer">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <h3>Aucun dépôt créé</h3>
        <p>Créez votre premier dépôt pour commencer à organiser vos emplacements de stockage.</p>
        <button class="btn btn-primary" @click="openCreateDepot">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Créer un dépôt
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'produits'">
      <div class="filters-bar">
        <div class="search-wrap">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="searchProduit" class="search-input" placeholder="Filtrer par produit..." @input="fetchProduits" />
        </div>
        <select v-model="filtreDepot" @change="fetchProduits" class="filter-select">
          <option value="">Tous les dépôts</option>
          <option v-for="s in stocks" :key="s.id" :value="s.id">{{ s.nom }}</option>
        </select>
      </div>
      <div class="table-card">
        <div v-if="loadingProduits" class="loading-state"><div class="spinner"></div><span>Chargement...</span></div>
        <div v-else-if="produits.length" class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Produit</th>
                <th>Catégorie</th>
                <th v-for="s in stocks" :key="s.id">{{ s.nom }}</th>
                <th>Total</th>
                <th>Valeur</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in produits" :key="p.id" :class="{ 'row-alerte': p.alerte_stock }">
                <td>
                  <div class="produit-cell">
                    <div class="produit-avatar">{{ p.nom.substring(0, 2).toUpperCase() }}</div>
                    <strong>{{ p.nom }}</strong>
                  </div>
                </td>
                <td><span class="badge-cat">{{ p.categorie }}</span></td>
                <td v-for="s in stocks" :key="s.id">
                  <div class="qty-cell">
                    <span
                      :class="{
                        'text-red': getQteForDepot(p, s.id) <= (getStockMinForDepot(p, s.id) || 0),
                        'text-muted': getQteForDepot(p, s.id) <= 0
                      }"
                    >
                      <strong>{{ getQteForDepot(p, s.id) }}</strong> {{ p.unite }}
                    </span>
                  </div>
                </td>
                <td><strong>{{ p.stock_total }} {{ p.unite }}</strong></td>
                <td>{{ fmtMAD(p.valeur_stock) }}</td>
                <td><span class="badge-statut" :class="p.statut">{{ statutLabel(p.statut) }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          </div>
          <h3>Aucun produit en stock</h3>
        </div>
      </div>
    </div>

    <div v-if="selectedDepot" class="drawer-overlay" @click.self="selectedDepot = null">
      <div class="drawer">
        <div class="drawer-header">
          <div>
            <h3>{{ selectedDepot.nom }}</h3>
            <span class="depot-code">{{ selectedDepot.code }} · {{ selectedDepot.type_label }}</span>
          </div>
          <button class="btn btn-sm btn-primary btn-with-icon" @click="openMinModal" style="margin-left:1rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            Modifier seuil
          </button>
          <button class="modal-close" @click="selectedDepot = null">✕</button>
        </div>
        <div class="drawer-body">
          <div class="depot-kpis">
            <div class="kpi"><strong>{{ depotDetail?.nb_produits || 0 }}</strong><span>Références</span></div>
            <div class="kpi"><strong>{{ fmtMAD(depotDetail?.valeur_totale || 0) }}</strong><span>Valeur stock</span></div>
          </div>
          <div v-if="loadingDetail" class="loading-state"><div class="spinner"></div></div>
          <div v-else-if="depotDetail?.produits?.length">
            <h4 style="margin:1rem 0 .5rem; color:#475569; font-size:.85rem; text-transform:uppercase; letter-spacing:.05em">Produits en stock</h4>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Produit</th>
                  <th>Qté</th>
                  <th>Min</th>
                  <th>Emplacement</th>
                  <th>Valeur</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in depotDetail.produits" :key="p.id" :class="{ 'row-alerte': p.alerte }">
                  <td><strong>{{ p.nom }}</strong><br><span class="sub">{{ p.categorie }}</span></td>
                  <td>
                    <strong :class="{ 'text-red': p.alerte }">
                      {{ p.quantite }}
                    </strong>
                    <span :class="{ 'text-red': p.alerte }">
                      {{ p.unite }}
                    </span>
                   
                  </td>
                  <td class="text-muted">{{ p.stock_minimum }}</td>
                  <td class="text-muted">{{ p.emplacement || '—' }}</td>
                  <td>{{ fmtMAD(p.valeur) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state" style="padding:2rem">
            <div class="empty-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
            </div>
            <p>Aucun produit dans ce dépôt</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="minModal" class="modal-overlay" @click.self="minModal = false">
      <div class="modal-box" style="width:400px;">
        <div class="modal-header">
          <h3>Modifier le seuil d'alerte</h3>
          <button class="modal-close" @click="minModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Produit *</label>
            <select v-model="selectedProductId" class="form-input">
              <option value="">-- Sélectionner un produit --</option>
              <option v-for="p in depotDetail?.produits" :key="p.id" :value="p.id">
                {{ p.nom }} (actuel : {{ p.stock_minimum }})
              </option>
            </select>
          </div>
          <div class="form-group" style="margin-top:1rem;">
            <label>Nouveau seuil minimum *</label>
            <input v-model.number="newMinValue" type="number" min="0" class="form-input" />
          </div>
          <p v-if="minError" class="form-error">{{ minError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="minModal = false">Annuler</button>
          <button class="btn btn-primary" @click="saveMinValue" :disabled="savingMin">
            {{ savingMin ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDepotModal" class="modal-overlay" @click.self="showDepotModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>{{ depotEditMode ? 'Modifier le dépôt' : 'Nouveau dépôt' }}</h3>
          <button class="modal-close" @click="showDepotModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>Nom *</label>
              <input v-model="depotForm.nom" class="form-input" placeholder="Ex : Dépôt Principal" />
            </div>
            <div class="form-group">
              <label>Code *</label>
              <input v-model="depotForm.code" class="form-input" placeholder="Ex : DEP-001" />
            </div>
            <div class="form-group">
              <label>Type *</label>
              <select v-model="depotForm.type" class="form-input">
                <option value="principal">Principal</option>
                <option value="secondaire">Secondaire</option>
                <option value="chantier">Chantier</option>
              </select>
            </div>
            <div class="form-group">
              <label>Adresse</label>
              <input v-model="depotForm.adresse" class="form-input" placeholder="Adresse du dépôt" />
            </div>
            <div class="form-group col-span-2">
              <label>Description</label>
              <textarea v-model="depotForm.description" class="form-input" rows="2"></textarea>
            </div>
          </div>
          <p v-if="depotError" class="form-error">{{ depotError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showDepotModal = false">Annuler</button>
          <button class="btn btn-primary" @click="saveDepot" :disabled="saving">
            {{ saving ? 'Enregistrement...' : (depotEditMode ? 'Mettre à jour' : 'Créer le dépôt') }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import ExportToolbar from '@/components/ExportToolbar.vue'
import { usePdfExport } from '@/composables/usePdfExport'

const { printPdf } = usePdfExport()

// ─── Export / Impression ───────────────────────────────────────
const excelColumns = [
  { key: 'produit', label: 'Produit' },
  { key: 'categorie', label: 'Catégorie' },
  { key: 'depot', label: 'Dépôt' },
  { key: 'quantite', label: 'Quantité' },
  { key: 'unite', label: 'Unité' },
  { key: 'seuil', label: 'Seuil minimum' },
  { key: 'valeur', label: 'Valeur (DH)' },
  { key: 'statut', label: 'État', value: (r) => (r.quantite <= r.seuil ? 'Sous seuil' : 'OK') },
]

// Aplati la matrice produits × dépôts en lignes simples pour l'export Excel
const flatStockLines = computed(() => {
  const lines = []
  for (const p of produits.value) {
    for (const s of stocks.value) {
      const qte = getQteForDepot(p, s.id)
      lines.push({
        produit: p.nom,
        categorie: p.categorie,
        depot: s.nom,
        quantite: qte,
        unite: p.unite,
        seuil: getStockMinForDepot(p, s.id) || 0,
        valeur: qte * (p.prix_unitaire || 0),
      })
    }
  }
  return lines
})

const showCustomPrint = ref(false)
const customPrintStockId = ref('')
const customPrintColumnOptions = [
  { key: 'produit', label: 'Produit' },
  { key: 'categorie', label: 'Catégorie' },
  { key: 'depot', label: 'Dépôt' },
  { key: 'emplacement', label: 'Emplacement' },
  { key: 'quantite', label: 'Quantité' },
  { key: 'seuil', label: 'Seuil min.' },
  { key: 'prix', label: 'Prix unitaire' },
  { key: 'valeur', label: 'Valeur' },
  { key: 'etat', label: 'État' },
]
const customPrintColumns = ref(['produit', 'depot', 'quantite', 'seuil', 'etat'])

async function launchCustomPrint() {
  showCustomPrint.value = false
  await printPdf('/admin/print/stock-personnalise', {
    columns: customPrintColumns.value,
    stock_ids: customPrintStockId.value ? [customPrintStockId.value] : [],
  })
}


const stocks = ref([])
const produits = ref([])
const loading = ref(true)
const loadingProduits = ref(false)
const loadingDetail = ref(false)
const saving = ref(false)
const savingMin = ref(false)
const activeTab = ref('depots')
const searchProduit = ref('')
const filtreDepot = ref('')
const selectedDepot = ref(null)
const depotDetail = ref(null)
const showDepotModal = ref(false)
const depotEditMode = ref(false)
const depotEditId = ref(null)
const depotError = ref('')

const minModal = ref(false)
const selectedProductId = ref(null)
const newMinValue = ref(0)
const minError = ref('')

const depotForm = reactive({ nom: '', code: '', type: 'principal', adresse: '', description: '' })

const stats = computed(() => ({
  nbDepots: stocks.value.length,
  nbProduits: produits.value.length,
  nbAlertes: produits.value.filter(p => p.alerte_stock).length,
  valeurTotale: produits.value.reduce((s, p) => s + (p.valeur_stock || 0), 0),
}))

async function fetchStocks() {
  loading.value = true
  try { const { data } = await api.get('/admin/stocks'); stocks.value = data.data }
  finally { loading.value = false }
}

async function fetchProduits() {
  loadingProduits.value = true
  try {
    const params = {}
    if (searchProduit.value) params.search = searchProduit.value
    const { data } = await api.get('/admin/produits', { params })
    produits.value = data.data
  } finally { loadingProduits.value = false }
}

function getQteForDepot(produit, stockId) {
  const s = produit.depots?.find(d => d.id === stockId)
  return s?.quantite ?? 0
}

function getStockMinForDepot(produit, stockId) {
  const depot = produit.depots?.find(d => d.id === stockId)
  return depot?.stock_minimum ?? 0
}

function openMinModal() {
  minError.value = ''
  selectedProductId.value = null
  newMinValue.value = 0
  minModal.value = true
}

async function saveMinValue() {
  minError.value = ''
  if (!selectedProductId.value) {
    minError.value = 'Veuillez sélectionner un produit.'
    return
  }
  if (newMinValue.value === undefined || newMinValue.value === null || newMinValue.value < 0) {
    minError.value = 'Veuillez entrer une valeur valide (≥ 0).'
    return
  }

  savingMin.value = true
  try {
    const produit = depotDetail.value.produits.find(p => p.id === selectedProductId.value)
    await api.put(`/admin/stocks/${selectedDepot.value.id}/produits/${selectedProductId.value}`, {
      stock_minimum: newMinValue.value,
    })

    if (produit) {
      produit.stock_minimum = newMinValue.value
    }

    minModal.value = false
  } catch (e) {
    minError.value = e.response?.data?.message || 'Erreur lors de la mise à jour.'
  } finally {
    savingMin.value = false
  }
}

async function voirDepot(depot) {
  selectedDepot.value = depot
  loadingDetail.value = true
  depotDetail.value = null
  try {
    const { data } = await api.get(`/admin/stocks/${depot.id}`)
    depotDetail.value = data.data
  } finally {
    loadingDetail.value = false
  }
}

function openCreateDepot() {
  depotEditMode.value = false; depotEditId.value = null
  Object.assign(depotForm, { nom: '', code: '', type: 'principal', adresse: '', description: '' })
  depotError.value = ''; showDepotModal.value = true
}

function openEditDepot(s) {
  depotEditMode.value = true; depotEditId.value = s.id
  Object.assign(depotForm, { nom: s.nom, code: s.code, type: s.type, adresse: s.adresse || '', description: s.description || '' })
  depotError.value = ''; showDepotModal.value = true
}

async function saveDepot() {
  depotError.value = ''
  if (!depotForm.nom) { depotError.value = 'Le nom est requis.'; return }
  if (!depotForm.code) { depotError.value = 'Le code est requis.'; return }
  saving.value = true
  try {
    if (depotEditMode.value) { await api.put(`/admin/stocks/${depotEditId.value}`, depotForm) }
    else { await api.post('/admin/stocks', depotForm) }
    showDepotModal.value = false; await fetchStocks()
  } catch (e) { depotError.value = e.response?.data?.message || 'Erreur.' }
  finally { saving.value = false }
}

async function supprimerDepot(id) {
  if (!confirm('Supprimer ce dépôt ?')) return
  try { await api.delete(`/admin/stocks/${id}`); await fetchStocks() } catch { alert('Erreur.') }
}

const fmtMAD = n => n == null ? '—' : new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
const statutLabel = s => ({ disponible: 'Disponible', rupture: 'Rupture', archive: 'Archivé' })[s] || s

onMounted(async () => { await fetchStocks(); await fetchProduits() })
</script>

<style scoped>
.sv-wrap { min-height: 100vh; background: #f4f7fc; margin: -42px !important; padding: 0 1.5rem; font-family: ui-sans-serif, system-ui, sans-serif; }
.sv-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 1.5rem 0 1rem; }
.sv-header-actions { display: flex; align-items: center; gap: 0.7rem; flex-wrap: wrap; }
.custom-print-columns { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.checkbox-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.7rem;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  font-size: 0.8rem;
  color: #334155;
  cursor: pointer;
  user-select: none;
}
.checkbox-pill input { accent-color: #2563eb; }
.sv-header h1 { margin: 0 0 .25rem; font-size: 1.6rem; font-weight: 800; color: #0f172a; }
.sv-header p { margin: 0; color: #64748b; font-size: .9rem; }

.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 22px; height: 22px; }
.stat-icon.blue { background: #eff6ff; color: #2563eb; }
.stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.stat-icon.red { background: #fff1f2; color: #e11d48; }
.stat-icon.green { background: #f0fdf4; color: #16a34a; }
.stat-card strong { display: block; font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.stat-card span { font-size: .8rem; color: #64748b; }

.tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; }
.tab { padding: .75rem 1.25rem; font-size: .9rem; font-weight: 600; color: #64748b; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; margin-bottom: -2px; transition: all .2s; }
.tab.active { color: #2563eb; border-bottom-color: #2563eb; }
.tab:hover:not(.active) { color: #334155; }

.depots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
.depot-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; padding: 1.25rem; cursor: pointer; transition: all .2s; }
.depot-card:hover { border-color: #2563eb; box-shadow: 0 4px 16px rgba(37,99,235,.1); transform: translateY(-1px); }
.depot-top { display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem; }
.depot-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.depot-icon svg { width: 22px; height: 22px; }
.depot-icon.principal { background: #eff6ff; color: #2563eb; }
.depot-icon.secondaire { background: #f5f3ff; color: #7c3aed; }
.depot-icon.chantier { background: #fff7ed; color: #ea580c; }
.depot-top strong { display: block; font-size: 1rem; font-weight: 700; color: #0f172a; }
.depot-code { font-size: .75rem; color: #64748b; display: block; }
.badge-type { margin-left: auto; padding: 3px 10px; border-radius: 10px; font-size: .72rem; font-weight: 700; }
.badge-type.principal { background: #eff6ff; color: #2563eb; }
.badge-type.secondaire { background: #f5f3ff; color: #7c3aed; }
.badge-type.chantier { background: #fff7ed; color: #ea580c; }

.depot-adresse { font-size: .8rem; color: #64748b; margin-bottom: .75rem; display: flex; align-items: center; gap: 0.35rem; }
.inline-icon { width: 14px; height: 14px; color: #94a3b8; }
.stroke-red { color: #e11d48; }

.depot-stats { display: flex; gap: 1.5rem; margin-bottom: 1rem; padding: .75rem; background: #f8fafc; border-radius: 8px; }
.depot-stat span { display: block; font-size: 1.2rem; font-weight: 800; color: #0f172a; }
.depot-stat label { font-size: .72rem; color: #64748b; }

/* Actions modernisées */
.depot-actions { display: flex; gap: .5rem; align-items: center; }
.depot-actions .btn-outline { flex: 1; }

.btn-action { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
.btn-action:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
.btn-action.edit:hover { color: #3b82f6; border-color: #bfdbfe; background: #eff6ff; }
.btn-action.delete:hover { color: #f43f5e; border-color: #fecdd3; background: #fff1f2; }

.filters-bar { display: flex; gap: .75rem; margin-bottom: 1.5rem; }
.search-wrap { flex: 1; position: relative; }
.search-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; }
.search-input { width: 100%; padding: .6rem .75rem .6rem 2.25rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; background: #fff; box-sizing: border-box; }
.filter-select { padding: .6rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .85rem; background: #fff; color: #334155; }

.table-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 2rem; }
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
.data-table th { padding: .75rem 1rem; border-bottom: 2px solid #e2e8f0; text-align: left; color: #475569; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; background: #f8fafc; }
.data-table td { padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tbody tr:hover td { background: #f8fafc; }
.data-table tbody tr:last-child td { border-bottom: none; }
.row-alerte td { background: transparent !important; }

.produit-cell { display: flex; align-items: center; gap: .75rem; }
.produit-avatar { width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 700; font-size: .8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; letter-spacing: 0.05em; }
.badge-cat { background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 10px; font-size: .75rem; font-weight: 600; }
.qty-cell { text-align: center; }
.badge-statut { padding: 3px 10px; border-radius: 12px; font-size: .75rem; font-weight: 700; }
.badge-statut.disponible { background: #d1fae5; color: #065f46; }
.badge-statut.rupture { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-statut.archive { background: #f1f5f9; color: #64748b; }
.badge-alerte { display: inline-flex; align-items: center; margin-left: 0.35rem; }
.badge-alerte svg { width: 14px; height: 14px; }
.text-red { color: #e11d48; }
.text-muted { color: #94a3b8; font-size: .82rem; }
.sub { font-size: .74rem; color: #94a3b8; }

/* Drawer */
.drawer-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.4); z-index: 998; display: flex; justify-content: flex-end; }
.drawer { width: 580px; max-width: 95vw; background: #fff; height: 100vh; overflow-y: auto; box-shadow: -8px 0 40px rgba(0,0,0,.15); display: flex; flex-direction: column; }
.drawer-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.drawer-header h3 { margin: 0 0 .25rem; font-size: 1.2rem; font-weight: 700; }
.drawer-body { padding: 1.5rem; flex: 1; }
.depot-kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
.kpi { background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; }
.kpi strong { display: block; font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: .25rem; }
.kpi span { font-size: .8rem; color: #64748b; }

.loading-state { display: flex; align-items: center; gap: 1rem; justify-content: center; min-height: 200px; color: #64748b; }
.spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; padding: 4rem 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.empty-icon { width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
.empty-icon svg { width: 32px; height: 32px; }
.empty-state h3 { margin: 0 0 .5rem; color: #0f172a; font-size: 1.15rem; font-weight: 700; }
.empty-state p { color: #64748b; margin-bottom: 1.5rem; max-width: 320px; font-size: 0.9rem; line-height: 1.5; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: .5rem 1rem; font-size: .85rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: all .2s; }
.btn-icon { width: 14px; height: 14px; }
.btn-with-icon svg { width: 14px; height: 14px; }
.btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn-danger { background: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
.btn-danger:hover { background: #ffe4e6; }
.btn-outline { background: transparent; border: 1px solid #2563eb; color: #2563eb; }
.btn-outline:hover { background: #2563eb; color: #fff; }
.btn-sm { padding: .35rem .6rem; font-size: .78rem; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-box { background: #fff; border-radius: 16px; width: 520px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }
.modal-body { padding: 1.25rem 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .75rem; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.col-span-2 { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: .4rem; }
.form-group label { font-size: .85rem; font-weight: 600; color: #475569; }
.form-input { padding: .6rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; color: #0f172a; background: #f8fafc; width: 100%; box-sizing: border-box; }
.form-input:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
.form-error { color: #e11d48; font-size: .85rem; margin-top: .5rem; }

@media (max-width: 768px) { .stat-grid { grid-template-columns: 1fr 1fr; } .depots-grid { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; } .col-span-2 { grid-column: span 1; } }
</style>