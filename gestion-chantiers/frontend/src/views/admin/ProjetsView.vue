Voici le code complet corrigé et optimisé.

Les modifications principales portent sur la fixation de la taille du SVG à l'intérieur du bouton (qui n'avait pas de dimensions par défaut et pouvait s'étirer) et l'ajustement du comportement Flexbox dans `.page-header` pour éviter que le bouton ne s'étire sur toute la hauteur ou toute la largeur en mode responsive.

```vue
<template>
  <div class="projects-page">

    <div class="page-header">
      <div>
        <h1>Gestion des projets</h1>
        <p>{{ filteredProjects.length }} projet{{ filteredProjects.length > 1 ? 's' : '' }} trouvé{{ filteredProjects.length > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn btn-primary" @click="openCreateProject">
        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-svg"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Ajouter un projet
      </button>
    </div>

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

    <div v-if="view === 'table'" class="table-card">
      <div class="table-wrap">
        <table class="data-table">
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
            <tr v-for="p in paginatedProjects" :key="p.id" @click="goToProject(p.id)" style="cursor: pointer;">
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
                  <button class="btn-action edit" @click.stop="goToProject(p.id)" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </button>
                  <button class="btn-action delete" @click.stop="confirmDelete(p)" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProjects.length === 0">
              <td colspan="9" class="empty-row">
                <div class="empty-state">
                  <span>📁</span>
                  <h3>Aucun projet trouvé</h3>
                  <p>Modifiez vos filtres ou lancez une nouvelle recherche.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

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
            <button class="btn-action edit" @click.stop="goToProject(p.id)">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="btn-action delete" @click.stop="confirmDelete(p)">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="modal-box">
          <div class="modal-header">
            <h3>Supprimer le projet ?</h3>
            <button class="modal-close" @click="showDeleteConfirm = false">×</button>
          </div>
          <div style="padding: 1.5rem; text-align: center;">
            <p>Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget?.nom }}</strong> ? Cette action est irréversible.</p>
            <div style="display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem;">
              <button class="btn btn-secondary" @click="showDeleteConfirm = false">Annuler</button>
              <button class="btn btn-danger" @click="doDelete">Oui, supprimer</button>
            </div>
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
    const { data } = await projetService.getProjets()
    projects.value = data.data || data
  } catch (e) {
    error.value = "Impossible de charger les projets."
    console.error(e)
  } finally {
    loading.value = false
  }
}

// ─── Filtres & Listes ──────────────────────────────────
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

onMounted(() => {
  fetchProjects()
})
</script>

<style scoped>
/* ── Page Layout ──────────────────────────────── */
.projects-page {
  margin: -42px;
  padding: 40px 24px;
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ── Header Modifié (Alignement propre) ───────── */
.page-header { 
  display: flex; 
  align-items: center; /* Aligne verticalement le bouton avec le texte au lieu de l'étirer */
  justify-content: space-between; 
  margin-bottom: 1.25rem; 
  gap: 1rem; 
  flex-wrap: wrap; 
}
.page-header h1 { font-size: 1.4rem; font-weight: 700; color: #1e2a4a; margin: 0 0 .2rem; }
.page-header p { font-size: .875rem; color: #8a94b2; margin: 0; }

/* Empêche le bouton de prendre une taille anormale */
.page-header .btn-primary {
  align-self: center;
  height: fit-content;
}

/* Fixation explicite de la taille du SVG dans le bouton */
.btn-icon-svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* ── Stats ────────────────────────────────────── */
.stats-bar { display: flex; gap: .6rem; margin-bottom: 1rem; flex-wrap: wrap; }
.stat-pill { display: flex; align-items: center; gap: .5rem; padding: .45rem 1rem; border-radius: 20px; border: 1.5px solid #e4e9f2; background: #fff; font-size: .82rem; font-weight: 500; color: #8a94b2; cursor: pointer; transition: all .15s; }
.stat-pill strong { color: #1e2a4a; margin-left: .25rem; }
.stat-pill .stat-dot { width: 8px; height: 8px; border-radius: 50%; }
.stat-pill--all .stat-dot { background: #6366f1; }
.stat-pill--non_commence .stat-dot { background: #94a3b8; }
.stat-pill--en_cours .stat-dot { background: #3b82f6; }
.stat-pill--termine .stat-dot { background: #22c55e; }
.stat-pill--bloque .stat-dot { background: #ef4444; }
.stat-pill.active { border-color: #6366f1; background: #eff6ff; color: #4f46e5; }

/* ── Toolbar ──────────────────────────────────── */
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: .75rem; flex-wrap: wrap; }
.search-box { display: flex; align-items: center; gap: .5rem; background: #fff; border: 1.5px solid #e4e9f2; border-radius: 10px; padding: .5rem .85rem; flex: 1; max-width: 360px; transition: border-color .2s; }
.search-box:focus-within { border-color: #6366f1; }
.search-box svg { width: 16px; height: 16px; color: #8a94b2; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: .875rem; color: #1e2a4a; flex: 1; background: none; }
.search-box input::placeholder { color: #94a3b8; }
.clear-btn { background: none; border: none; font-size: 1.1rem; color: #94a3b8; cursor: pointer; line-height: 1; }
.toolbar-right { display: flex; align-items: center; gap: .6rem; }
.filter-select { padding: .5rem .85rem; border: 1.5px solid #e4e9f2; border-radius: 10px; font-size: .875rem; color: #1e2a4a; background: #fff; cursor: pointer; outline: none; }
.view-toggle { display: flex; background: #f0f2f8; border-radius: 8px; padding: 3px; gap: 2px; }
.view-toggle button { width: 32px; height: 32px; border: none; background: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #8a94b2; transition: all .15s; }
.view-toggle button svg { width: 16px; height: 16px; }
.view-toggle button.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* ── Progress & Badges ────────────────────────── */
.progress-bar-line { display: inline-block; width: 70px; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; vertical-align: middle; margin-right: 6px; }
.progress-bar-line .fill { height: 100%; background: #3b82f6; }
.pct { font-weight: 600; font-size: .75rem; color: #0f172a; }

.status-badge { display: inline-flex; align-items: center; padding: .25rem .75rem; border-radius: 20px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
.status-non_commence { background: #f1f5f9; color: #475569; }
.status-en_cours { background: #dbeafe; color: #1d4ed8; }
.status-termine { background: #d1fae5; color: #065f46; }
.status-bloque { background: #fce4ec; color: #c62828; }

.text-blue { color: #2563eb !important; }
.text-muted { color: #94a3b8; }
.chantier-link { color: #2563eb; cursor: pointer; text-decoration: underline; }
.chantier-link:hover { color: #1d4ed8; }
.empty-row { padding: 0 !important; }

/* ── Styles Complémentaires ───────────────────── */
.table-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 2rem; }
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
.data-table th {
  padding: .75rem 1rem;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
  color: #475569;
  font-weight: 600;
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .04em;
  background: #f8fafc;
  white-space: nowrap;
}
.data-table td { padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tbody tr:hover td { background: #f8fafc; }
.data-table tbody tr:last-child td { border-bottom: none; }

.action-btns { display: flex; gap: .4rem; }
.btn-action { 
  display: inline-flex; 
  align-items: center; 
  justify-content: center; 
  width: 32px; 
  height: 32px; 
  border-radius: 8px; 
  border: 1px solid #e2e8f0; 
  background: #fff; 
  color: #64748b; 
  cursor: pointer; 
  transition: all 0.2s; 
  flex-shrink: 0; 
}
.btn-action:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
.btn-action.edit:hover { color: #3b82f6; border-color: #bfdbfe; background: #eff6ff; }
.btn-action.delete:hover { color: #f43f5e; border-color: #fecdd3; background: #fff1f2; }

.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }
.empty-state h3 { margin: 0 0 .5rem; color: #0f172a; }
.empty-state p { color: #64748b; margin-bottom: 1.5rem; }

/* Buttons global class definitions */
.btn { padding: .5rem 1rem; font-size: .85rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: all .2s; display: inline-flex; align-items: center; gap: .4rem; }
.btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn-danger { background: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
.btn-danger:hover { background: #ffe4e6; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

/* Modal structures */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 999; backdrop-filter: blur(4px); }
.modal-box { background: #fff; border-radius: 16px; width: 560px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: space-between; padding: .85rem 1.25rem; border-top: 1px solid #f0f2f8; }
.page-info { font-size: .8rem; color: #8a94b2; }
.page-btns { display: flex; gap: .3rem; }
.page-btns button { width: 30px; height: 30px; border-radius: 7px; border: 1.5px solid #e4e9f2; background: #fff; color: #475569; font-size: .8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.page-btns button svg { width: 14px; height: 14px; }
.page-btns button:hover:not(:disabled) { border-color: #2563eb; color: #2563eb; }
.page-btns button:disabled { opacity: .4; cursor: not-allowed; }
.page-btns button.current { background: #2563eb; border-color: #2563eb; color: #fff; }

/* ── Cartes ────────────────────────────────────── */
.project-cards { display: grid; grid-template-columns: repeat(auto-fill,minmax(260px,1fr)); gap: 1rem; }
.project-card { background: #fff; border-radius: 16px; border: 1px solid #e4e9f2; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); cursor: pointer; transition: box-shadow .2s; }
.project-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.06); }
.pc-header { padding: 1rem 1rem 0.8rem; position: relative; display: flex; justify-content: space-between; align-items: center; }
.pc-status { display: inline-flex; align-items: center; padding: .2rem .6rem; border-radius: 10px; font-size: .68rem; font-weight: 700; background: rgba(255,255,255,.2); color: #fff; }
.pc-ref { font-size: .7rem; color: rgba(255,255,255,.7); font-weight: 600; }
.pc-body { padding: 1rem; }
.pc-body h4 { font-size: .95rem; font-weight: 700; color: #1e2a4a; margin: 0 0 .2rem; }
.pc-chantier { font-size: .75rem; color: #64748b; margin: 0 0 .5rem; }
.pc-categorie { font-size: .75rem; color: #94a3b8; margin: 0 0 .5rem; }
.pc-progress { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; font-size: .75rem; color: #475569; }
.pc-progress .progress-bar-line { flex: 1; }
.pc-budget { display: flex; justify-content: space-between; font-size: .8rem; padding: .5rem 0; border-top: 1px solid #f1f5f9; margin-top: .5rem; }
.pc-budget strong { color: #0f172a; }
.pc-cout-reel { font-size: .7rem; color: #94a3b8; }
.pc-actions { display: flex; gap: .5rem; justify-content: flex-end; margin-top: .75rem; }

/* ── Responsive Modifié ───────────────────────── */
@media (max-width: 768px) {
  .projects-page { padding: 16px; margin: -16px; }
  .page-header { 
    flex-direction: row; /* Garde le titre et le bouton alignés horizontalement si la place le permet */
    align-items: center; 
    justify-content: space-between;
  }
  
  .stats-bar { gap: .4rem; }
  .stat-pill { padding: .3rem .8rem; font-size: .75rem; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-box { max-width: 100%; }
  .toolbar-right { justify-content: space-between; }
  .data-table th, .data-table td { padding: .6rem .5rem; font-size: .8rem; }
  .data-table th:nth-child(3), .data-table td:nth-child(3),
  .data-table th:nth-child(4), .data-table td:nth-child(4),
  .data-table th:nth-child(7), .data-table td:nth-child(7) { display: none; }
  .project-cards { grid-template-columns: 1fr; }
}
</style>

```