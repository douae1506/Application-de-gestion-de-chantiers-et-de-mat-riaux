<template>
  <div class="projects-page">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Gestion des projets</h1>
        <p>{{ filteredProjects.length }} projet{{ filteredProjects.length > 1 ? 's' : '' }} trouvé{{ filteredProjects.length > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary" @click="openCreateProject">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Ajouter un projet
      </button>
    </div>

    <!-- Stats -->
    <div class="stats-bar">
      <div class="stat-pill stat-pill--all" :class="{ active: filterStatus === '' }" @click="filterStatus = ''">
        <span class="stat-dot"></span> Tous <strong>{{ projects.length }}</strong>
      </div>
      <div class="stat-pill stat-pill--non_commence" :class="{ active: filterStatus === 'non_commence' }" @click="filterStatus = 'non_commence'">
        <span class="stat-dot"></span> Non commencés <strong>{{ countByStatus('non_commence') }}</strong>
      </div>
      <div class="stat-pill stat-pill--en_cours" :class="{ active: filterStatus === 'en_cours' }" @click="filterStatus = 'en_cours'">
        <span class="stat-dot"></span> En cours <strong>{{ countByStatus('en_cours') }}</strong>
      </div>
      <div class="stat-pill stat-pill--termine" :class="{ active: filterStatus === 'termine' }" @click="filterStatus = 'termine'">
        <span class="stat-dot"></span> Terminés <strong>{{ countByStatus('termine') }}</strong>
      </div>
      <div class="stat-pill stat-pill--bloque" :class="{ active: filterStatus === 'bloque' }" @click="filterStatus = 'bloque'">
        <span class="stat-dot"></span> Bloqués <strong>{{ countByStatus('bloque') }}</strong>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-box">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par nom, référence, chantier..." />
        <button v-if="search" class="clear-btn" @click="search = ''">×</button>
      </div>
      <div class="toolbar-right">
        <select v-model="filterCategorie" class="filter-select">
          <option value="">Toutes catégories</option>
          <option value="Construction">Construction</option>
          <option value="Rénovation">Rénovation</option>
          <option value="Aménagement">Aménagement</option>
          <option value="Électricité">Électricité</option>
          <option value="Plomberie">Plomberie</option>
          <option value="Autre">Autre</option>
        </select>
        <div class="view-toggle">
          <button :class="{ active: view === 'table' }" @click="view = 'table'">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
          </button>
          <button :class="{ active: view === 'cards' }" @click="view = 'cards'">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <div v-if="view === 'table'" class="table-card">
      <table class="projects-table">
        <thead>
          <tr>
            <th>Réf.</th>
            <th>Nom</th>
            <th>Chantier</th>
            <th>Catégorie</th>
            <th>Statut</th>
            <th>Progression</th>
            <th>Budget</th>
            <th>Coût réel</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in paginatedProjects" :key="p.id" @click="goToProject(p.id)" class="clickable-row">
            <td>{{ p.reference }}</td>
            <td><strong>{{ p.nom }}</strong></td>
            <td>
              <span v-if="p.chantier" class="chantier-link" @click.stop="goToChantier(p.chantier.id)">
                {{ p.chantier.nom }}
              </span>
              <span v-else class="text-muted">—</span>
            </td>
            <td>{{ p.categorie || '—' }}</td>
            <td><span class="status-badge" :class="'status-' + p.statut">{{ p.statut_label || p.statut }}</span></td>
            <td>
              <div class="progress-bar-line"><div class="fill" :style="{ width: p.progression + '%' }"></div></div>
              <span class="pct">{{ p.progression }}%</span>
            </td>
            <td>{{ formatMAD(p.budget) }}</td>
            <td><strong class="text-blue">{{ formatMAD(p.cout_reel) }}</strong></td>
            <td>
              <div class="action-btns">
                <button class="action-btn edit" @click.stop="goToProject(p.id)" title="Voir">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                </button>
                <button class="action-btn delete" @click.stop="confirmDelete(p)" title="Supprimer">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredProjects.length === 0">
            <td colspan="9" class="empty-row">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <p>Aucun projet trouvé</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination">
        <span class="page-info">{{ (currentPage-1)*perPage+1 }}–{{ Math.min(currentPage*perPage, filteredProjects.length) }} sur {{ filteredProjects.length }}</span>
        <div class="page-btns">
          <button @click="currentPage--" :disabled="currentPage === 1">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </button>
          <button v-for="p in totalPages" :key="p" :class="{ current: p === currentPage }" @click="currentPage = p">{{ p }}</button>
          <button @click="currentPage++" :disabled="currentPage === totalPages">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- CARDS VIEW -->
    <div v-else class="project-cards">
      <div class="project-card" v-for="p in paginatedProjects" :key="p.id" @click="goToProject(p.id)">
        <div class="pc-header" :style="{ background: cardGradient(p.statut) }">
          <div class="pc-status" :class="'status-' + p.statut">{{ p.statut_label || p.statut }}</div>
          <span class="pc-ref">{{ p.reference }}</span>
        </div>
        <div class="pc-body">
          <h4>{{ p.nom }}</h4>
          <p class="pc-chantier" v-if="p.chantier">📍 {{ p.chantier.nom }}</p>
          <p class="pc-categorie" v-else>—</p>
          <div class="pc-progress">
            <span>Progression</span>
            <div class="progress-bar-line"><div class="fill" :style="{ width: p.progression + '%' }"></div></div>
            <span class="pct">{{ p.progression }}%</span>
          </div>
          <div class="pc-budget">
            <span>Budget</span>
            <strong>{{ formatMAD(p.budget) }}</strong>
            <span class="pc-cout-reel">Coût réel: {{ formatMAD(p.cout_reel) }}</span>
          </div>
          <div class="pc-actions">
            <button class="action-btn edit" @click.stop="goToProject(p.id)"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg></button>
            <button class="action-btn delete" @click.stop="confirmDelete(p)"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg></button>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── MODAL DE CONFIRMATION SUPPRESSION ─── -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="confirm-modal">
          <div class="confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <h3>Supprimer le projet ?</h3>
          <p>Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget?.nom }}</strong> ? Cette action est irréversible.</p>
          <div class="confirm-actions">
            <button class="btn-cancel" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-delete" @click="doDelete">Oui, supprimer</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import projetService from '@/services/projetService'

// ─── Router ──────────────────────────────────────────────
const router = useRouter()

// ─── États ──────────────────────────────────────────────
const projects = ref([])
const loading = ref(true)
const error = ref(null)

const search = ref('')
const filterStatus = ref('')
const filterCategorie = ref('')
const view = ref('table')
const currentPage = ref(1)
const perPage = 8

// ─── Suppression ───────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteTarget = ref(null)

// ─── Chargement des projets ────────────────────────────
async function fetchProjects() {
  loading.value = true
  error.value = null
  try {
    const { data } = await projetService.getProjets() // suppose une méthode qui retourne { data: [...] }
    projects.value = data.data || data // adapter selon votre API
  } catch (e) {
    error.value = "Impossible de charger les projets."
    console.error(e)
  } finally {
    loading.value = false
  }
}

// ─── Filtres ──────────────────────────────────────────
const filteredProjects = computed(() => {
  return projects.value.filter(p => {
    const q = search.value.toLowerCase()
    const matchSearch = !q ||
      p.nom?.toLowerCase().includes(q) ||
      p.reference?.toLowerCase().includes(q) ||
      p.chantier?.nom?.toLowerCase().includes(q)

    const matchStatus = !filterStatus.value || p.statut === filterStatus.value
    const matchCategorie = !filterCategorie.value || p.categorie === filterCategorie.value

    return matchSearch && matchStatus && matchCategorie
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProjects.value.length / perPage)))
const paginatedProjects = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredProjects.value.slice(start, start + perPage)
})

const countByStatus = (status) => projects.value.filter(p => p.statut === status).length

// ─── Navigation ────────────────────────────────────────
function goToProject(id) {
  router.push({ name: 'projet-detail', params: { id } })
}

function goToChantier(id) {
  router.push({ name: 'chantier-detail', params: { id } })
}

function openCreateProject() {
  router.push({ name: 'projet-create' })
}

// ─── Suppression ──────────────────────────────────────
function confirmDelete(project) {
  deleteTarget.value = project
  showDeleteConfirm.value = true
}

async function doDelete() {
  if (!deleteTarget.value) return

  try {
    await projetService.deleteProjet(deleteTarget.value.id)

    await fetchProjects()

    showDeleteConfirm.value = false
    deleteTarget.value = null

  } catch (e) {
    console.error(e)
    alert("Erreur lors de la suppression")
  }
}

// ─── Helpers ────────────────────────────────────────────
function formatMAD(n) {
  if (n === null || n === undefined) return '—'
  return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
}

function cardGradient(status) {
  const map = {
    'non_commence': 'linear-gradient(135deg,#94a3b8,#64748b)',
    'en_cours': 'linear-gradient(135deg,#3b82f6,#2563eb)',
    'termine': 'linear-gradient(135deg,#22c55e,#16a34a)',
    'bloque': 'linear-gradient(135deg,#ef4444,#dc2626)',
  }
  return map[status] || 'linear-gradient(135deg,#6366f1,#8b5cf6)'
}

// ─── Montage ────────────────────────────────────────────
onMounted(() => {
  fetchProjects()
})
</script>

<style scoped>
/* ── Page layout ──────────────────────────────── */
.projects-page {
  margin: -30px;
  padding: 40px 24px;
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ── Header ────────────────────────────────────── */
.page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.25rem; gap:1rem; flex-wrap:wrap; }
.page-header h1 { font-size:1.4rem; font-weight:700; color:#1e2a4a; margin:0 0 .2rem; }
.page-header p { font-size:.875rem; color:#8a94b2; margin:0; }
.btn-primary { display:flex; align-items:center; gap:.4rem; padding:.6rem 1.1rem; background:linear-gradient(135deg,#6366f1,#8b5cf6); border:none; border-radius:10px; color:#fff; font-size:.875rem; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(99,102,241,.3); transition:opacity .15s; white-space:nowrap; }
.btn-primary svg { width:15px; height:15px; }
.btn-primary:hover { opacity:.9; }

/* ── Stats ────────────────────────────────────── */
.stats-bar { display:flex; gap:.6rem; margin-bottom:1rem; flex-wrap:wrap; }
.stat-pill { display:flex; align-items:center; gap:.5rem; padding:.45rem 1rem; border-radius:20px; border:1.5px solid #e4e9f2; background:#fff; font-size:.82rem; font-weight:500; color:#8a94b2; cursor:pointer; transition:all .15s; }
.stat-pill strong { color:#1e2a4a; margin-left:.25rem; }
.stat-pill .stat-dot { width:8px; height:8px; border-radius:50%; }
.stat-pill--all .stat-dot { background:#6366f1; }
.stat-pill--non_commence .stat-dot { background:#94a3b8; }
.stat-pill--en_cours .stat-dot { background:#3b82f6; }
.stat-pill--termine .stat-dot { background:#22c55e; }
.stat-pill--bloque .stat-dot { background:#ef4444; }
.stat-pill.active { border-color:#6366f1; background:#eff6ff; color:#4f46e5; }

/* ── Toolbar ──────────────────────────────────── */
.toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; gap:.75rem; flex-wrap:wrap; }
.search-box { display:flex; align-items:center; gap:.5rem; background:#fff; border:1.5px solid #e4e9f2; border-radius:10px; padding:.5rem .85rem; flex:1; max-width:360px; transition:border-color .2s; }
.search-box:focus-within { border-color:#6366f1; }
.search-box svg { width:16px; height:16px; color:#8a94b2; flex-shrink:0; }
.search-box input { border:none; outline:none; font-size:.875rem; color:#1e2a4a; flex:1; background:none; }
.search-box input::placeholder { color:#94a3b8; }
.clear-btn { background:none; border:none; font-size:1.1rem; color:#94a3b8; cursor:pointer; line-height:1; }
.toolbar-right { display:flex; align-items:center; gap:.6rem; }
.filter-select { padding:.5rem .85rem; border:1.5px solid #e4e9f2; border-radius:10px; font-size:.875rem; color:#1e2a4a; background:#fff; cursor:pointer; outline:none; }
.view-toggle { display:flex; background:#f0f2f8; border-radius:8px; padding:3px; gap:2px; }
.view-toggle button { width:32px; height:32px; border:none; background:none; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#8a94b2; transition:all .15s; }
.view-toggle button svg { width:16px; height:16px; }
.view-toggle button.active { background:#fff; color:#6366f1; box-shadow:0 1px 3px rgba(0,0,0,.08); }

/* ── Table ────────────────────────────────────── */
.table-card { background:#fff; border-radius:16px; border:1px solid #e4e9f2; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; }
.projects-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
.projects-table thead { background:#f8fafc; }
.projects-table th { padding:.85rem 1rem; text-align:left; font-size:.72rem; font-weight:700; color:#8a94b2; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #e4e9f2; white-space:nowrap; }
.projects-table td { padding:.9rem 1rem; border-bottom:1px solid #f0f2f8; color:#1e2a4a; vertical-align:middle; }
.projects-table tr:last-child td { border-bottom:none; }
.projects-table .clickable-row { cursor:pointer; transition:background .1s; }
.projects-table .clickable-row:hover td { background:#fafbff; }

.progress-bar-line { display:inline-block; width:70px; height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; vertical-align:middle; margin-right:6px; }
.progress-bar-line .fill { height:100%; background:#3b82f6; }
.pct { font-weight:600; font-size:.75rem; color:#0f172a; }

.status-badge { display:inline-flex; align-items:center; padding:.25rem .75rem; border-radius:20px; font-size:.72rem; font-weight:700; white-space:nowrap; }
.status-non_commence { background:#f1f5f9; color:#475569; }
.status-en_cours { background:#dbeafe; color:#1d4ed8; }
.status-termine { background:#d1fae5; color:#065f46; }
.status-bloque { background:#fce4ec; color:#c62828; }

.text-blue { color:#2563eb !important; }
.text-muted { color:#94a3b8; }

.chantier-link { color:#2563eb; cursor:pointer; text-decoration:underline; }
.chantier-link:hover { color:#1d4ed8; }

.action-btns { display:flex; gap:.4rem; }
.action-btn { width:30px; height:30px; border-radius:8px; border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; }
.action-btn svg { width:14px; height:14px; }
.action-btn.edit { background:rgba(99,102,241,.08); color:#6366f1; }
.action-btn.edit:hover { background:rgba(99,102,241,.18); }
.action-btn.delete { background:rgba(239,68,68,.08); color:#ef4444; }
.action-btn.delete:hover { background:rgba(239,68,68,.18); }

.empty-row { text-align:center; padding:3rem!important; }
.empty-state { display:flex; flex-direction:column; align-items:center; gap:.75rem; color:#94a3b8; }
.empty-state svg { width:48px; height:48px; }
.empty-state p { font-size:.875rem; }

/* Pagination */
.pagination { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.25rem; border-top:1px solid #f0f2f8; }
.page-info { font-size:.8rem; color:#8a94b2; }
.page-btns { display:flex; gap:.3rem; }
.page-btns button { width:30px; height:30px; border-radius:7px; border:1.5px solid #e4e9f2; background:#fff; color:#475569; font-size:.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.page-btns button svg { width:14px; height:14px; }
.page-btns button:hover:not(:disabled) { border-color:#6366f1; color:#6366f1; }
.page-btns button:disabled { opacity:.4; cursor:not-allowed; }
.page-btns button.current { background:#6366f1; border-color:#6366f1; color:#fff; }

/* ── Cartes ────────────────────────────────────── */
.project-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1rem; }
.project-card { background:#fff; border-radius:16px; border:1px solid #e4e9f2; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); cursor:pointer; transition:box-shadow .2s; }
.project-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.06); }
.pc-header { padding:1rem 1rem 0.8rem; position:relative; display:flex; justify-content:space-between; align-items:center; }
.pc-status { display:inline-flex; align-items:center; padding:.2rem .6rem; border-radius:10px; font-size:.68rem; font-weight:700; background:rgba(255,255,255,.2); color:#fff; }
.pc-ref { font-size:.7rem; color:rgba(255,255,255,.7); font-weight:600; }
.pc-body { padding:1rem; }
.pc-body h4 { font-size:.95rem; font-weight:700; color:#1e2a4a; margin:0 0 .2rem; }
.pc-chantier { font-size:.75rem; color:#64748b; margin:0 0 .5rem; }
.pc-categorie { font-size:.75rem; color:#94a3b8; margin:0 0 .5rem; }
.pc-progress { display:flex; align-items:center; gap:.5rem; margin-bottom:.5rem; font-size:.75rem; color:#475569; }
.pc-progress .progress-bar-line { flex:1; }
.pc-budget { display:flex; justify-content:space-between; font-size:.8rem; padding:.5rem 0; border-top:1px solid #f1f5f9; margin-top:.5rem; }
.pc-budget strong { color:#0f172a; }
.pc-cout-reel { font-size:.7rem; color:#94a3b8; }
.pc-actions { display:flex; gap:.5rem; justify-content:flex-end; margin-top:.75rem; }

/* ── Modal confirmation ───────────────────────── */
.modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:1rem; backdrop-filter:blur(4px); }
.confirm-modal { background:#fff; border-radius:16px; padding:2rem; max-width:380px; width:100%; text-align:center; box-shadow:0 25px 50px rgba(0,0,0,.25); }
.confirm-icon { width:60px; height:60px; border-radius:50%; background:rgba(239,68,68,.1); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; color:#ef4444; }
.confirm-icon svg { width:28px; height:28px; }
.confirm-modal h3 { font-size:1.1rem; font-weight:700; color:#1e2a4a; margin:0 0 .5rem; }
.confirm-modal p { font-size:.875rem; color:#64748b; line-height:1.6; margin:0 0 1.5rem; }
.confirm-actions { display:flex; gap:.75rem; justify-content:center; }
.btn-cancel { padding:.6rem 1.25rem; border:1.5px solid #e2e8f0; border-radius:10px; background:#fff; color:#475569; font-size:.875rem; font-weight:600; cursor:pointer; }
.btn-delete { padding:.6rem 1.5rem; background:linear-gradient(135deg,#ef4444,#dc2626); border:none; border-radius:10px; color:#fff; font-size:.875rem; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(239,68,68,.3); }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 768px) {
  .projects-page { padding:16px; margin:-16px; }
  .page-header { flex-direction:column; align-items:stretch; }
  .btn-primary { justify-content:center; }
  .stats-bar { gap:.4rem; }
  .stat-pill { padding:.3rem .8rem; font-size:.75rem; }
  .toolbar { flex-direction:column; align-items:stretch; }
  .search-box { max-width:100%; }
  .toolbar-right { justify-content:space-between; }
  .projects-table th, .projects-table td { padding:.6rem .5rem; font-size:.8rem; }
  .projects-table th:nth-child(3), .projects-table td:nth-child(3),
  .projects-table th:nth-child(4), .projects-table td:nth-child(4),
  .projects-table th:nth-child(7), .projects-table td:nth-child(7) { display:none; }
  .project-cards { grid-template-columns:1fr; }
}
</style>