<template>
  <div class="chantiers-page">

    <!-- ── En-tête ─────────────────────────────────────────── -->
    <div class="page-header">
      <div>
        <h1>Chantiers</h1>
        <p>{{ chantiers.length }} chantier{{ chantiers.length > 1 ? 's' : '' }} au total</p>
      </div>
      <div class="page-header-actions">
        <button
          v-if="auth.hasPermission('create_chantiers')"
          class="btn btn-primary"
          @click="$router.push({ name: 'chantier-create' })"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          Nouveau chantier
        </button>
        <ExportToolbar
          :pdf-url="'/admin/exports/chantiers'"
          :pdf-params="filters"
          pdf-filename="chantiers"
          :excel-columns="excelColumns"
          :excel-rows="chantiers"
          excel-filename="chantiers"
        />
      </div>
    </div>

    <!-- ── Filtres ──────────────────────────────────────────── -->
    <div class="filters-row">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input
          v-model="filters.search"
          class="search-input"
          type="text"
          placeholder="Rechercher par nom, référence, ville..."
          @input="fetchChantiers"
        />
      </div>
      <select v-model="filters.statut" class="filter-select" @change="fetchChantiers">
        <option value="">Tous les statuts</option>
        <option value="planifie">Planifié</option>
        <option value="en_cours">En cours</option>
        <option value="suspendu">Suspendu</option>
        <option value="termine">Terminé</option>
        <option value="annule">Annulé</option>
      </select>
      <select v-model="filters.type" class="filter-select" @change="fetchChantiers">
        <option value="">Tous les types</option>
        <option value="residentiel">Résidentiel</option>
        <option value="commercial">Commercial</option>
        <option value="industriel">Industriel</option>
        <option value="public">Public</option>
      </select>
    </div>

    <!-- ── Loading ──────────────────────────────────────────── -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement des chantiers...</span>
    </div>

    <!-- ── Vide ─────────────────────────────────────────────── -->
    <div v-else-if="chantiers.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/></svg>
      </div>
      <h3>Aucun chantier trouvé</h3>
      <p>{{ filters.search || filters.statut || filters.type ? 'Modifiez vos filtres ou' : '' }} Créez votre premier chantier.</p>
      <button v-if="auth.hasPermission('create_chantiers')" class="btn btn-primary" @click="openCreateModal">Nouveau chantier</button>
    </div>

    <!-- ── Grille ───────────────────────────────────────────── -->
    <div v-else class="chantiers-grid">
      <div
        v-for="c in chantiers"
        :key="c.id"
        class="chantier-card"
        @click="openChantier(c)"
      >
        <!-- Image / couleur de fond -->
        <div 
  class="card-visual" 
  :style="{ 
    backgroundImage: 'url(' + getImageForType(c.type) + ')',
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }"
>
  <div class="card-overlay"></div> <!-- Ajout d'un overlay pour lisibilité -->
  <svg class="card-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/></svg>
  <span class="card-statut-badge" :class="statutClass(c.statut)">{{ c.statut_label }}</span>
</div>

        <div class="card-body">
          <div class="card-ref">{{ c.reference }} · {{ c.type_label }}</div>
          <div class="card-title">{{ c.nom }}</div>
          <div class="card-location">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ c.ville }}, {{ c.pays }}
          </div>

          <!-- Barre de progression -->
          <div class="card-prog-row">
            <span class="prog-label">Progression</span>
            <span class="prog-pct">{{ c.progression }}%</span>
          </div>
          <div class="prog-bar">
            <div class="prog-fill" :style="{ width: c.progression + '%' }"></div>
          </div>

          <!-- Stats bas de carte -->
          <div class="card-stats">
            <div class="card-stat">
              <span class="stat-val">{{ formatMAD(c.budget_total) }}</span>
              <span class="stat-lbl">Budget</span>
            </div>
            <div class="card-stat">
              <span class="stat-val">{{ c.nb_projets }}</span>
              <span class="stat-lbl">Projets</span>
            </div>
            <div class="card-stat">
              <span class="stat-val">{{ c.jours_restants }}</span>
              <span class="stat-lbl">Jours rest.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  

  </div>
</template>

<script setup>

import { ref, reactive, onMounted } from 'vue'

import { useRouter } from 'vue-router'
import chantierService from '@/services/chantierService'
import clientService from '@/services/clientService'
import { useAuthStore } from '@/stores/auth'
import ExportToolbar from '@/components/ExportToolbar.vue'

const excelColumns = [
  { key: 'reference', label: 'Référence' },
  { key: 'nom', label: 'Nom' },
  { key: 'ville', label: 'Ville', value: (r) => r.ville || '—' },
  { key: 'type_label', label: 'Type', value: (r) => r.type_label || r.type },
  { key: 'statut_label', label: 'Statut', value: (r) => r.statut_label || r.statut },
  { key: 'progression', label: 'Progression (%)' },
  { key: 'budget_total', label: 'Budget total (DH)' },
  { key: 'cout_reel', label: 'Coût réel (DH)' },
  { key: 'date_debut', label: 'Date début' },
]

const router = useRouter()
const auth = useAuthStore()

const chantiers = ref([])
const clients   = ref([])
const loading   = ref(false)
const saving    = ref(false)
const showModal = ref(false)
const errors    = ref({})

const filters = reactive({ search: '', statut: '', type: '' })

const form = reactive({
  client_id: '', nom: '', description: '', type: 'residentiel',
  adresse: '', ville: '', pays: 'Maroc', code_postal: '',
  surface: '', budget_total: '', architecte: '',
  date_debut: '', date_fin_prevue: '',
})
const openChantier = (chantier) => {
  router.push({
    name: 'chantier-detail',
    params: {
      id: chantier.id
    }
  })
}
// ─── Données ──────────────────────────────────────────────

async function fetchChantiers() {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.statut) params.statut = filters.statut
    if (filters.type)   params.type   = filters.type

    const { data } = await chantierService.getChantiers(params)
    chantiers.value = data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}
function getImageForType(type) {
  const images = {
    residentiel: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&auto=format&fit=crop&q=60',
    commercial:  'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&auto=format&fit=crop&q=60',
    industriel:  'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600&auto=format&fit=crop&q=60',
    public:      'https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=600&auto=format&fit=crop&q=60',
  };
  return images[type] || 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&auto=format&fit=crop&q=60';
}
async function fetchClients() {
  try {
    const { data } = await clientService.getClients()
    clients.value = data.data
  } catch (e) {
    console.error(e)
  }
}

// ─── Actions ──────────────────────────────────────────────

function goDetail(id) {
  router.push({ name: 'chantier-detail', params: { id } })
}



// ─── Helpers UI ───────────────────────────────────────────

function formatMAD(n) {
  if (!n) return '—'
  return new Intl.NumberFormat('fr-MA').format(n) + ' MAD'
}

function typeBg(type) {
  const map = {
    residentiel: 'linear-gradient(135deg,#c7d2fe 0%,#a5b4fc 100%)',
    commercial:  'linear-gradient(135deg,#a5f3fc 0%,#67e8f9 100%)',
    industriel:  'linear-gradient(135deg,#bbf7d0 0%,#6ee7b7 100%)',
    public:      'linear-gradient(135deg,#fde68a 0%,#fcd34d 100%)',
  }
  return map[type] ?? 'linear-gradient(135deg,#e2e8f0 0%,#cbd5e1 100%)'
}

function statutClass(statut) {
  return {
    'badge-encours':  statut === 'en_cours',
    'badge-planifie': statut === 'planifie',
    'badge-termine':  statut === 'termine',
    'badge-suspendu': statut === 'suspendu',
    'badge-annule':   statut === 'annule',
  }
}

onMounted(() => {
  fetchChantiers()
  fetchClients()
})
</script>

<style scoped>
/* ── Page ─────────────────────────────────────────────────── */
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
}
.page-header-actions { display: flex; align-items: center; gap: 0.7rem; flex-wrap: wrap; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; color: #0a2540; margin: 0 0 .2rem; }
.page-header p  { font-size: .875rem; color: #639fab; margin: 0; }

/* ── Filtres ──────────────────────────────────────────────── */
.filters-row { display: flex; gap: .75rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-icon {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px; color: #639fab; pointer-events: none;
}
.search-input {
  width: 100%; padding: .55rem .75rem .55rem 2.2rem;
  border: 1px solid #e0f2fe; border-radius: 8px;
  font-size: .875rem; color: #0a2540; background: #fff;
  transition: border-color .15s;
}
.search-input:focus { outline: none; border-color: #0284c7; }
.filter-select {
  padding: .55rem .75rem; border: 1px solid #e0f2fe; border-radius: 8px;
  font-size: .875rem; color: #0a2540; background: #fff; cursor: pointer;
}
.filter-select:focus { outline: none; border-color: #0284c7; }

/* ── États ───────────────────────────────────────────────── */
.loading-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; min-height: 300px; gap: 1rem; color: #639fab;
}
.spinner {
  width: 32px; height: 32px; border: 3px solid #e0f2fe;
  border-top-color: #0284c7; border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; min-height: 350px; gap: 1rem; text-align: center;
}
.empty-icon {
  width: 64px; height: 64px; border-radius: 50%; background: #f0f9ff;
  display: flex; align-items: center; justify-content: center;
}
.empty-icon svg { width: 30px; height: 30px; color: #0284c7; }
.empty-state h3 { font-size: 1rem; font-weight: 600; color: #0a2540; margin: 0; }
.empty-state p  { font-size: .875rem; color: #639fab; margin: 0; }

/* ── Grille de cartes ───────────────────────────────────── */
.chantiers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.chantier-card {
  background: #fff; border: 1px solid #e0f2fe; border-radius: 14px;
  overflow: hidden; cursor: pointer;
  transition: box-shadow .2s, border-color .2s, transform .15s;
}
.chantier-card:hover {
  border-color: #0284c7;
  box-shadow: 0 4px 16px rgba(2,132,199,.12);
  transform: translateY(-2px);
}

/* Visuel coloré en haut de carte */
.card-visual {
  height: 130px; position: relative; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
}
.card-bg-icon {
  width: 90px; height: 90px; color: #fff; opacity: .18;
}
.card-statut-badge {
  position: absolute; top: 10px; right: 10px;
  font-size: .72rem; font-weight: 600;
  padding: 3px 10px; border-radius: 20px;
}
.badge-encours  { background: #d1fae5; color: #065f46; }
.badge-planifie { background: #dbeafe; color: #1d4ed8; }
.badge-termine  { background: #ede9fe; color: #5b21b6; }
.badge-suspendu { background: #fef3c7; color: #92400e; }
.badge-annule   { background: #fee2e2; color: #991b1b; }

/* Corps de la carte */
.card-body { padding: 1rem 1.1rem; }
.card-ref  { font-size: .72rem; color: #639fab; margin-bottom: .25rem; font-weight: 500; }
.card-title {
  font-size: .95rem; font-weight: 700; color: #0a2540;
  margin-bottom: .4rem; line-height: 1.3;
}
.card-location {
  display: flex; align-items: center; gap: 4px;
  font-size: .8rem; color: #639fab; margin-bottom: .9rem;
}
.card-location svg { width: 13px; height: 13px; flex-shrink: 0; }

/* Barre de progression */
.card-prog-row {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: .35rem;
}
.prog-label { font-size: .75rem; color: #639fab; }
.prog-pct   { font-size: .75rem; font-weight: 600; color: #0a2540; }
.prog-bar   {
  height: 5px; background: #f0f9ff; border-radius: 10px;
  overflow: hidden; margin-bottom: .85rem;
}
.prog-fill  { height: 100%; background: #0284c7; border-radius: 10px; transition: width .4s; }

/* Stats bas */
.card-stats {
  display: flex; gap: .75rem;
  border-top: 1px solid #f0f9ff; padding-top: .75rem;
}
.card-stat { flex: 1; text-align: center; }
.card-stat + .card-stat { border-left: 1px solid #f0f9ff; }
.stat-val  { display: block; font-size: .82rem; font-weight: 700; color: #0a2540; }
.stat-lbl  { display: block; font-size: .7rem; color: #639fab; margin-top: 1px; }

/* ── Boutons ─────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .55rem 1.1rem; border-radius: 8px;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  border: 1px solid transparent; transition: all .15s;
}
.btn svg { width: 16px; height: 16px; }
.btn-primary { background: #0284c7; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #0369a1; }
.btn-primary:disabled { opacity: .6; cursor: default; }
.btn-ghost   { background: transparent; border-color: #e0f2fe; color: #0a2540; }
.btn-ghost:hover { background: #f0f9ff; }

/* ── Modal ───────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(10,37,64,.35);
  display: flex; align-items: center; justify-content: center; z-index: 9999;
  padding: 1rem;
}
.modal {
  background: #fff; border-radius: 16px; width: 100%; max-width: 640px;
  max-height: 90vh; display: flex; flex-direction: column;
  box-shadow: 0 20px 60px rgba(10,37,64,.18);
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #e0f2fe;
}
.modal-header h2 { font-size: 1rem; font-weight: 700; color: #0a2540; margin: 0; }
.modal-close {
  width: 30px; height: 30px; border: none; background: transparent;
  border-radius: 6px; cursor: pointer; color: #639fab;
  display: flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #f0f9ff; color: #0a2540; }
.modal-close svg   { width: 16px; height: 16px; }

.modal-body { padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: .75rem;
  padding: 1rem 1.5rem; border-top: 1px solid #e0f2fe;
}

/* Formulaire */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.span2 { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group label { font-size: .8rem; font-weight: 600; color: #0a2540; }
.required { color: #ef4444; }
.form-control {
  padding: .55rem .75rem; border: 1px solid #e0f2fe; border-radius: 8px;
  font-size: .875rem; color: #0a2540; background: #fff; transition: border-color .15s;
  font-family: inherit;
}
.form-control:focus { outline: none; border-color: #0284c7; }
textarea.form-control { resize: vertical; }
.form-error { font-size: .75rem; color: #ef4444; }
</style>