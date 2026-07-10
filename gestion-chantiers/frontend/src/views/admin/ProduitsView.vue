<template>
  <div class="pv-wrap">

    <div class="pv-header">
      <div>
        <h1>Catalogue Produits</h1>
        <p>Gérez les fiches produits (nom, catégorie, prix unitaire) et associez-les à des fournisseurs</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nouveau produit
      </button>
    </div>

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
              <th>Prix unitaire</th>
              <th>Stock Global</th>
              <th>Valeur Stock</th>
              <th>Statut</th>
              <th>Fournisseurs</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in produits" :key="p.id">
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
              <td><strong>{{ fmtMAD(p.prix_unitaire) }}</strong></td>
              <td>
                <div class="stock-cell">
                  <span class="stock-qty" :class="{ 'text-warning': p.alerte_stock && p.stock_total > 0, 'text-danger': p.stock_total === 0 }">
                    {{ p.stock_total }} <small>{{ p.unite }}</small>
                  </span>
                  <span class="sub">{{ p.nb_depots }} dépôt(s)</span>
                </div>
              </td>
              <td><span class="text-muted-dark">{{ fmtMAD(p.valeur_stock) }}</span></td>
              <td><span class="badge-statut" :class="p.statut">{{ statutLabel(p.statut) }}</span></td>
              <td>
                <div v-if="p.fournisseurs && p.fournisseurs.length" class="fournisseurs-list">
                  <span v-for="f in p.fournisseurs.slice(0,2)" :key="f.id" class="fournisseur-tag">{{ f.nom }}</span>
                  <span
                    v-if="p.fournisseurs.length > 2"
                    class="fournisseur-tag count"
                    role="button"
                    tabindex="0"
                    @click="openFournisseursPopover(p)"
                    @keyup.enter="openFournisseursPopover(p)"
                  >+{{ p.fournisseurs.length - 2 }}</span>
                </div>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-action edit" @click="openEditModal(p)" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </button>
                  <button class="btn-action delete" @click="supprimer(p.id)" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                  </button>
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

    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>{{ editMode ? 'Modifier le produit' : 'Nouveau produit' }}</h3>
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
            <div class="form-group" v-if="editMode">
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

    <div v-if="showFournisseursModal" class="modal-overlay" @click.self="showFournisseursModal = false">
      <div class="modal-box fournisseurs-modal">
        <div class="modal-header">
          <h3>Fournisseurs de {{ fournisseursModalProduit?.nom }}</h3>
          <button class="modal-close" @click="showFournisseursModal = false">✕</button>
        </div>
        <div class="modal-body">
          <ul class="fournisseurs-full-list">
            <li v-for="f in fournisseursModalProduit?.fournisseurs" :key="f.id">
              <span class="fournisseur-full-nom">{{ f.nom }}</span>
              <span v-if="f.telephone" class="fournisseur-full-meta">{{ f.telephone }}</span>
              <span v-if="f.email" class="fournisseur-full-meta">{{ f.email }}</span>
            </li>
          </ul>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showFournisseursModal = false">Fermer</button>
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
const fournisseurs = ref([])  
const loading = ref(true)
const saving = ref(false)
const search = ref('')
const filtreCategorie = ref('')
const filtreStatut = ref('')
const showModal = ref(false)
const editMode = ref(false)
const editId = ref(null)
const formError = ref('')

// ─── Popover "voir tous les fournisseurs" ─────────────────────
const showFournisseursModal = ref(false)
const fournisseursModalProduit = ref(null)
function openFournisseursPopover(produit) {
  fournisseursModalProduit.value = produit
  showFournisseursModal.value = true
}

const form = reactive({
  nom: '',
  categorie: '',
  unite: 'unité',
  prix_unitaire: 0,
  description: '',
  statut: 'disponible',
  fournisseurs: [],   
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
    fournisseurs: [],   
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
    fournisseurs: p.fournisseurs ? p.fournisseurs.map(f => f.id) : [], 
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
  await fetchFournisseurs() 
})
</script>

<style scoped>
.pv-wrap { min-height: 100vh; background: #f8fafc; margin: -42px !important; padding: 1.5rem; font-family: ui-sans-serif, system-ui, sans-serif; }
.pv-header { display: flex; justify-content: space-between; align-items: center; padding: 0 0 1.5rem; }
.pv-header h1 { margin: 0 0 .25rem; font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.pv-header p { margin: 0; color: #64748b; font-size: .92rem; }

.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.25rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 24px; height: 24px; }
.stat-icon.blue { background: #eff6ff; color: #3b82f6; }
.stat-icon.green { background: #f0fdf4; color: #22c55e; }
.stat-icon.red { background: #fff1f2; color: #f43f5e; }
.stat-icon.orange { background: #fff7ed; color: #f97316; }
.stat-card strong { display: block; font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.stat-card span { font-size: .82rem; color: #64748b; font-weight: 500; }

.filters-bar { display: flex; gap: .75rem; margin-bottom: 1.5rem; }
.search-wrap { flex: 1; position: relative; }
.search-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #94a3b8; }
.search-input { width: 100%; padding: .65rem .75rem .65rem 2.5rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: .9rem; background: #fff; box-sizing: border-box; transition: all 0.2s; }
.search-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.filter-select { padding: .65rem 2rem .65rem .75rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: .88rem; background: #fff; color: #334155; cursor: pointer; }

.table-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01); }
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
.data-table th { padding: .85rem 1.25rem; border-bottom: 1px solid #e2e8f0; text-align: left; color: #64748b; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; background: #f8fafc; }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
.data-table tbody tr:hover td { background: #fafafa; }
.data-table tbody tr:last-child td { border-bottom: none; }

.produit-cell { display: flex; align-items: center; gap: .85rem; }
.produit-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: #fff; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; shadow: 0 2px 4px rgba(59,130,246,0.1); }
.produit-cell strong { display: block; color: #0f172a; font-weight: 600; }
.sub { display: block; font-size: .78rem; color: #94a3b8; margin-top: 2px; }
.text-muted-dark { color: #475569; font-weight: 500; }

.badge-cat { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: .78rem; font-weight: 600; }

/* Amélioration Colonne Stock */
.stock-cell { display: flex; flex-direction: column; gap: 2px; }
.stock-qty { font-weight: 700; color: #0f172a; font-size: .95rem; }
.stock-qty small { font-weight: 500; color: #64748b; margin-left: 2px; }
.text-warning { color: #d97706 !important; }
.text-danger { color: #e11d48 !important; }

/* Amélioration de la couleur Jaune pour la Rupture */
.badge-statut { padding: 4px 10px; border-radius: 8px; font-size: .78rem; font-weight: 600; display: inline-flex; align-items: center; }
.badge-statut.disponible { background: #d1fae5; color: #065f46; }
.badge-statut.rupture { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; } /* Un beau jaune ambré au lieu du rouge agressif */
.badge-statut.archive { background: #f1f5f9; color: #64748b; }

.fournisseurs-list { display: flex; flex-wrap: wrap; gap: 4px; max-width: 220px; }
.fournisseur-tag { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 6px; font-size: .75rem; font-weight: 500; white-space: nowrap; border: 1px solid #e2e8f0; }
.fournisseur-tag.count { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-weight: 600; cursor: pointer; }
.fournisseur-tag.count:hover { background: #bae6fd; }
.fournisseur-tag.count:focus-visible { outline: 2px solid #0369a1; outline-offset: 1px; }

.fournisseurs-modal { width: 420px; }
.fournisseurs-full-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .6rem; }
.fournisseurs-full-list li { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; padding: .6rem .75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; }
.fournisseur-full-nom { font-weight: 600; color: #0f172a; font-size: .88rem; }
.fournisseur-full-meta { font-size: .78rem; color: #64748b; }

/* Modernisation Boutons Actions */
.action-btns { display: flex; gap: .5rem; justify-content: flex-end; }
.btn-action { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; cursor: pointer; transition: all 0.2s; }
.btn-action:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
.btn-action.edit:hover { color: #3b82f6; border-color: #bfdbfe; background: #eff6ff; }
.btn-action.delete:hover { color: #f43f5e; border-color: #fecdd3; background: #fff1f2; }

/* Styles Restants Globaux */
.loading-state { display: flex; align-items: center; gap: 1rem; justify-content: center; min-height: 200px; color: #64748b; }
.spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }
.empty-state h3 { margin: 0 0 .5rem; color: #0f172a; }
.empty-state p { color: #64748b; margin-bottom: 1.5rem; }

.btn { padding: .6rem 1.2rem; font-size: .88rem; font-weight: 600; border-radius: 10px; cursor: pointer; border: 1px solid #e2e8f0; transition: all .2s; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-primary { background: #3b82f6; color: #fff; border-color: #3b82f6; box-shadow: 0 2px 4px rgba(59,130,246,0.15); }
.btn-primary:hover { background: #2563eb; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal-box { background: #fff; border-radius: 16px; width: 540px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-header h3 { margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; }
.modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #94a3b8; transition: color 0.2s; }
.modal-close:hover { color: #475569; }
.modal-body { padding: 1.25rem 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: .75rem; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.col-span-2 { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: .4rem; }
.form-group label { font-size: .85rem; font-weight: 600; color: #475569; }
.form-input { padding: .65rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: .9rem; color: #0f172a; background: #f8fafc; width: 100%; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.form-error { color: #e11d48; font-size: .85rem; margin-top: .5rem; }

@media (max-width: 768px) { .stat-grid { grid-template-columns: 1fr 1fr; } .form-grid { grid-template-columns: 1fr; } .col-span-2 { grid-column: span 1; } }
</style>