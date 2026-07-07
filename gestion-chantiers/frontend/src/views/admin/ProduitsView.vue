<template>
  <div class="pv-wrap">

    <!-- Header -->
    <div class="pv-header">
      <div>
        <h1>Catalogue Produits</h1>
        <p>Gérez les fiches produits (nom, catégorie, prix unitaire) et associez-les à des fournisseurs</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">+ Nouveau produit</button>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
        <div><strong>{{ stats.total }}</strong><span>Produits</span></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <strong>{{ stats.avecStock }}</strong>
          <span>Produits avec stock</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <div>
          <strong>{{ stats.sansStock }}</strong>
          <span>Produits sans stock</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><strong>{{ fmtMAD(stats.valeur) }}</strong><span>Valeur totale</span></div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input v-model="search" @input="fetchProduits" class="search-input" placeholder="Rechercher un produit..." />
      </div>
      <select v-model="filtreCategorie" @change="fetchProduits" class="filter-select">
        <option value="">Toutes catégories</option>
        <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
      </select>
      <select v-model="filtreStatut" @change="fetchProduits" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="disponible">Disponible</option>
        <option value="rupture">Rupture</option>
        <option value="archive">Archivé</option>
      </select>
    </div>

    <!-- Tableau -->
    <div class="table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div><span>Chargement...</span>
      </div>
      <div v-else-if="produits.length" class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Produit</th>
              <th>Catégorie</th>
              <th>Unité</th>
              <th>Prix unitaire</th>
              <th>Stock total</th>
              <th>Valeur</th>
              <th>Statut</th>
              <th>Fournisseurs</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in produits" :key="p.id" :class="{ 'row-alerte': p.alerte_stock }">
              <td>
                <div class="produit-cell">
                  <div class="produit-avatar">{{ p.nom[0].toUpperCase() }}</div>
                  <div>
                    <strong>{{ p.nom }}</strong>
                    <span class="sub">{{ p.description || '—' }}</span>
                  </div>
                </div>
              </td>
              <td><span class="badge-cat">{{ p.categorie }}</span></td>
              <td>{{ p.unite }}</td>
              <td><strong>{{ fmtMAD(p.prix_unitaire) }}</strong></td>
              <td>
                <div class="stock-cell">
                  <strong :class="p.alerte_stock ? 'text-red' : ''">{{ p.stock_total }} {{ p.unite }}</strong>
                  <span v-if="p.alerte_stock" class="badge-alerte">⚠ Alerte</span>
                  <span class="sub">{{ p.nb_depots }} dépôt(s)</span>
                </div>
              </td>
              <td>{{ fmtMAD(p.valeur_stock) }}</td>
              <td><span class="badge-statut" :class="p.statut">{{ statutLabel(p.statut) }}</span></td>
              <td>
                <div v-if="p.fournisseurs && p.fournisseurs.length" class="fournisseurs-list">
                  <span v-for="f in p.fournisseurs.slice(0,2)" :key="f.id" class="fournisseur-tag">{{ f.nom }}</span>
                  <span v-if="p.fournisseurs.length > 2" class="fournisseur-tag">+{{ p.fournisseurs.length - 2 }}</span>
                  <span v-if="!p.fournisseurs.length" class="text-muted">—</span>
                </div>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-sm btn-outline" @click="openEditModal(p)" title="Modifier">✏️</button>
                  <button class="btn btn-sm btn-danger" @click="supprimer(p.id)" title="Supprimer">🗑</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">
        <span>📦</span>
        <h3>Aucun produit</h3>
        <p>Commencez par créer vos premiers produits dans le catalogue.</p>
        <button class="btn btn-primary" @click="openCreateModal">+ Créer un produit</button>
      </div>
    </div>

    <!-- Modal produit -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>{{ editMode ? '✏️ Modifier le produit' : '+ Nouveau produit' }}</h3>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group col-span-2">
              <label>Nom *</label>
              <input v-model="form.nom" class="form-input" placeholder="Ex : Ciment CPA 42.5" />
            </div>
            <div class="form-group">
              <label>Catégorie *</label>
              <input v-model="form.categorie" class="form-input" placeholder="Ex : Matériaux, Outillage..." list="cat-list" />
              <datalist id="cat-list">
                <option v-for="c in categories" :key="c" :value="c" />
              </datalist>
            </div>
            <div class="form-group">
              <label>Unité *</label>
              <select v-model="form.unite" class="form-input">
                <option value="unité">Unité</option>
                <option value="kg">Kilogramme (kg)</option>
                <option value="tonne">Tonne</option>
                <option value="m²">m²</option>
                <option value="m³">m³</option>
                <option value="ml">Mètre linéaire (ml)</option>
                <option value="litre">Litre</option>
                <option value="sac">Sac</option>
                <option value="boîte">Boîte</option>
                <option value="rouleau">Rouleau</option>
              </select>
            </div>
            <div class="form-group">
              <label>Prix unitaire (MAD) *</label>
              <input v-model.number="form.prix_unitaire" type="number" min="0" step="0.01" class="form-input" />
            </div>
            <div v-if="editMode" class="form-group">
              <label>Statut</label>
              <select v-model="form.statut" class="form-input">
                <option value="disponible">Disponible</option>
                <option value="rupture">Rupture</option>
                <option value="archive">Archivé</option>
              </select>
            </div>
            <div class="form-group col-span-2">
              <label>Fournisseurs associés</label>
              <select v-model="form.fournisseurs" multiple class="form-input" style="min-height:80px;">
                <option v-for="f in fournisseurs" :key="f.id" :value="f.id">
                  {{ f.nom }}
                </option>
              </select>
              <small style="color:#64748b;font-size:.75rem;margin-top:.2rem;">
                Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs fournisseurs.
              </small>
            </div>
            <div class="form-group col-span-2">
              <label>Description</label>
              <textarea v-model="form.description" class="form-input" rows="2" placeholder="Description optionnelle..."></textarea>
            </div>
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = false">Annuler</button>
          <button class="btn btn-primary" @click="save" :disabled="saving">
            {{ saving ? 'Enregistrement...' : (editMode ? 'Mettre à jour' : 'Créer') }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'

const produits = ref([])
const categories = ref([])
const fournisseurs = ref([])  // <-- nouvelle référence
const loading = ref(true)
const saving = ref(false)
const search = ref('')
const filtreCategorie = ref('')
const filtreStatut = ref('')
const showModal = ref(false)
const editMode = ref(false)
const editId = ref(null)
const formError = ref('')

const form = reactive({
  nom: '',
  categorie: '',
  unite: 'unité',
  prix_unitaire: 0,
  description: '',
  statut: 'disponible',
  fournisseurs: [],   // <-- nouveau champ
})

const stats = computed(() => ({
  total: produits.value.length,
  avecStock: produits.value.filter(p => p.stock_total > 0).length,
  sansStock: produits.value.filter(p => p.stock_total === 0).length,
  valeur: produits.value.reduce((s, p) => s + (p.valeur_stock || 0), 0),
}))

async function fetchProduits() {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filtreCategorie.value) params.categorie = filtreCategorie.value
    if (filtreStatut.value) params.statut = filtreStatut.value
    const { data } = await api.get('/admin/produits', { params })
    produits.value = data.data
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const { data } = await api.get('/admin/produits/categories')
    categories.value = data.data
  } catch (e) {
    console.error('Erreur chargement catégories', e)
  }
}

// ⭐ Nouvelle fonction pour charger les fournisseurs
async function fetchFournisseurs() {
  try {
    const { data } = await api.get('/admin/fournisseurs')
    fournisseurs.value = data.data
  } catch (e) {
    console.error('Erreur chargement fournisseurs', e)
  }
}

function openCreateModal() {
  editMode.value = false
  editId.value = null
  Object.assign(form, {
    nom: '',
    categorie: '',
    unite: 'unité',
    prix_unitaire: 0,
    description: '',
    statut: 'disponible',
    fournisseurs: [],   // <-- réinitialisation
  })
  formError.value = ''
  showModal.value = true
}

function openEditModal(p) {
  editMode.value = true
  editId.value = p.id
  Object.assign(form, {
    nom: p.nom,
    categorie: p.categorie,
    unite: p.unite,
    prix_unitaire: p.prix_unitaire,
    description: p.description || '',
    statut: p.statut,
    fournisseurs: p.fournisseurs ? p.fournisseurs.map(f => f.id) : [], // <-- pré‑remplir
  })
  formError.value = ''
  showModal.value = true
}

async function save() {
  formError.value = ''
  if (!form.nom) { formError.value = 'Le nom est requis.'; return }
  if (!form.categorie) { formError.value = 'La catégorie est requise.'; return }
  saving.value = true
  try {
    if (editMode.value) {
      await api.put(`/admin/produits/${editId.value}`, form)
    } else {
      await api.post('/admin/produits', form)
    }
    showModal.value = false
    await fetchProduits()
    await fetchCategories()
    await fetchFournisseurs()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Erreur.'
  } finally {
    saving.value = false
  }
}

async function supprimer(id) {
  if (!confirm('Supprimer ce produit ?')) return
  try {
    await api.delete(`/admin/produits/${id}`)
    await fetchProduits()
  } catch (e) {
    alert('Erreur.')
  }
}

const fmtMAD = n => n == null ? '—' : new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
const statutLabel = s => ({ disponible: 'Disponible', rupture: 'Rupture', archive: 'Archivé' })[s] || s

onMounted(async () => {
  await fetchProduits()
  await fetchCategories()
  await fetchFournisseurs() // <-- chargement des fournisseurs
})
</script>

<style scoped>
/* ... Gardez tous les styles existants, inchangés ... */

/* Ajoutez ces deux classes pour les tags fournisseurs */
.fournisseurs-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
.fournisseur-tag {
  background: #e2e8f0;
  color: #334155;
  padding: 0 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  white-space: nowrap;
  line-height: 1.6;
}


.pv-wrap { min-height: 100vh; background: #f4f7fc; margin: -42px !important; padding: 0 1.5rem; font-family: ui-sans-serif, system-ui, sans-serif; }
.pv-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 1.5rem 0 1rem; }
.pv-header h1 { margin: 0 0 .25rem; font-size: 1.6rem; font-weight: 800; color: #0f172a; }
.pv-header p { margin: 0; color: #64748b; font-size: .9rem; }

.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 22px; height: 22px; }
.stat-icon.blue { background: #eff6ff; color: #2563eb; }
.stat-icon.green { background: #f0fdf4; color: #16a34a; }
.stat-icon.red { background: #fff1f2; color: #e11d48; }
.stat-icon.orange { background: #fff7ed; color: #ea580c; }
.stat-card strong { display: block; font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.stat-card span { font-size: .8rem; color: #64748b; }

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
.row-alerte td { background: #fffbeb !important; }

.produit-cell { display: flex; align-items: center; gap: .75rem; }
.produit-avatar { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #2563eb, #7c3aed); color: #fff; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.produit-cell strong { display: block; color: #0f172a; }
.sub { display: block; font-size: .74rem; color: #94a3b8; }
.badge-cat { background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 10px; font-size: .75rem; font-weight: 600; }
.stock-cell { display: flex; flex-direction: column; gap: 2px; }
.text-red { color: #e11d48; }
.badge-alerte { font-size: .7rem; color: #b45309; font-weight: 600; background: #fef3c7; padding: 1px 6px; border-radius: 6px; }
.badge-statut { padding: 3px 10px; border-radius: 12px; font-size: .75rem; font-weight: 700; }
.badge-statut.disponible { background: #d1fae5; color: #065f46; }
.badge-statut.rupture { background: #fee2e2; color: #991b1b; }
.badge-statut.archive { background: #f1f5f9; color: #64748b; }
.action-btns { display: flex; gap: .4rem; }

.loading-state { display: flex; align-items: center; gap: 1rem; justify-content: center; min-height: 200px; color: #64748b; }
.spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }
.empty-state h3 { margin: 0 0 .5rem; color: #0f172a; }
.empty-state p { color: #64748b; margin-bottom: 1.5rem; }

.btn { padding: .5rem 1rem; font-size: .85rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: all .2s; }
.btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn-danger { background: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
.btn-danger:hover { background: #ffe4e6; }
.btn-outline { background: transparent; border: 1px solid #2563eb; color: #2563eb; }
.btn-outline:hover { background: #2563eb; color: #fff; }
.btn-sm { padding: .25rem .6rem; font-size: .78rem; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-box { background: #fff; border-radius: 16px; width: 540px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
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
@media (max-width: 768px) { .stat-grid { grid-template-columns: 1fr 1fr; } .form-grid { grid-template-columns: 1fr; } .col-span-2 { grid-column: span 1; } }
</style>
