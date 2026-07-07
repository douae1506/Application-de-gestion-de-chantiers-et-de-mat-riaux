<template>
  <div class="historique-page">
    <div class="page-header">
      <div>
        <h1>Historique des actions</h1>
        <p>Toutes les activités enregistrées sur la plateforme</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="refresh">Rafraîchir</button>
        <button class="btn btn-outline" @click="exportCSV"> Exporter CSV</button>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-row">
      <div class="search-wrap">
        <input v-model="filters.search" class="search-input" placeholder="Rechercher..." @input="debounceFetch" />
      </div>
      <select v-model="filters.action" class="filter-select" @change="fetchActivities">
        <option value="">Toutes les actions</option>
        <option value="created">Création</option>
        <option value="updated">Modification</option>
        <option value="deleted">Suppression</option>
        <option value="status_changed">Changement de statut</option>
        <option value="restored">Restauration</option>
      </select>
      <select v-model="filters.subject_type" class="filter-select" @change="fetchActivities">
        <option value="">Tous les types</option>
        <option value="Chantier">Chantier</option>
        <option value="Projet">Projet</option>
        <option value="User">Utilisateur</option>
        <option value="Client">Client</option>
        <option value="Stock">Stock</option>
      </select>
      <input v-model="filters.date_debut" type="date" class="filter-select" @change="fetchActivities" />
      <span>→</span>
      <input v-model="filters.date_fin" type="date" class="filter-select" @change="fetchActivities" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement de l'historique...</span>
    </div>

    <!-- Liste -->
    <div v-else-if="activities.length" class="activities-list">
      <div v-for="act in activities" :key="act.id" class="activity-item">
        <div class="activity-icon" :class="iconClass(act.action)">
          {{ iconEmoji(act.action) }}
        </div>
        <div class="activity-content">
          <div class="activity-header">
            <span class="activity-user">{{ act.user_nom || 'Système' }}</span>
            <span class="activity-role" v-if="act.user_role">{{ act.user_role }}</span>
            <span class="activity-date">{{ formatDate(act.created_at) }}</span>
          </div>
          <div class="activity-description" v-html="highlightSearch(act.description)"></div>
          <div v-if="act.properties" class="activity-details">
            <pre>{{ JSON.stringify(act.properties, null, 2) }}</pre>
          </div>
          <div class="activity-meta">
            <span class="badge-subject">{{ act.subject_type }}</span>
            <span class="badge-action">{{ act.action }}</span>
            <span v-if="act.subject_label" class="badge-label">{{ act.subject_label }}</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="lastPage > 1">
        <button class="btn btn-sm" :disabled="page <= 1" @click="page--">Précédent</button>
        <span>Page {{ page }} / {{ lastPage }}</span>
        <button class="btn btn-sm" :disabled="page >= lastPage" @click="page++">Suivant</button>
      </div>
    </div>

    <div v-else class="empty-state">
      <span>📭</span>
      <p>Aucune activité enregistrée.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import api from '@/services/api'

const activities = ref([])
const loading = ref(false)
const page = ref(1)
const lastPage = ref(1)

const filters = reactive({
  search: '',
  action: '',
  subject_type: '',
  date_debut: '',
  date_fin: '',
})

let debounceTimer = null

async function fetchActivities() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      search: filters.search,
      action: filters.action,
      subject_type: filters.subject_type,
      date_debut: filters.date_debut,
      date_fin: filters.date_fin,
      per_page: 20,
    }
    const { data } = await api.get('/admin/activities', { params })
    activities.value = data.data
    lastPage.value = data.meta?.last_page || 1
  } catch (e) {
    console.error('Erreur chargement historique', e)
    alert('Erreur lors du chargement de l\'historique.')
  } finally {
    loading.value = false
  }
}

function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    page.value = 1
    fetchActivities()
  }, 300)
}

function resetFilters() {
  filters.search = ''
  filters.action = ''
  filters.subject_type = ''
  filters.date_debut = ''
  filters.date_fin = ''
  page.value = 1
  fetchActivities()
}

function refresh() {
  fetchActivities()
}

function exportCSV() {
  // À implémenter : générer un CSV des activités filtrées
  alert('Export CSV à venir...')
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function iconClass(action) {
  return {
    'icon-created': action === 'created',
    'icon-updated': action === 'updated',
    'icon-deleted': action === 'deleted',
    'icon-status': action === 'status_changed',
    'icon-restored': action === 'restored',
  }
}

function iconEmoji(action) {
  const map = {
    created: '➕',
    updated: '✏️',
    deleted: '🗑️',
    status_changed: '🔄',
    restored: '♻️',
  }
  return map[action] || '📌'
}

function highlightSearch(text) {
  if (!filters.search) return text
  const regex = new RegExp(`(${filters.search})`, 'gi')
  return text.replace(regex, '<mark>$1</mark>')
}

watch(() => [page.value], fetchActivities)
onMounted(fetchActivities)
</script>

<style scoped>
/* Styles à ajouter, inspirés du design existant */
.historique-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.filters-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; align-items: center; }
.search-input { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 200px; }
.filter-select { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; background: white; }
.activities-list { display: flex; flex-direction: column; gap: 1rem; }
.activity-item { display: flex; gap: 1rem; background: white; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; }
.activity-icon { font-size: 1.5rem; width: 40px; text-align: center; }
.activity-content { flex: 1; }
.activity-header { display: flex; gap: 0.75rem; align-items: center; font-size: 0.9rem; color: #64748b; }
.activity-user { font-weight: 600; color: #0f172a; }
.activity-role { background: #f1f5f9; padding: 0 6px; border-radius: 4px; }
.activity-date { margin-left: auto; }
.activity-description { margin: 0.25rem 0 0.5rem; font-size: 0.95rem; }
.activity-details { background: #f8fafc; padding: 0.5rem; border-radius: 8px; font-size: 0.8rem; overflow-x: auto; }
.activity-meta { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
.badge-subject, .badge-action, .badge-label { background: #f1f5f9; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; }
.pagination { display: flex; justify-content: center; gap: 1rem; align-items: center; margin-top: 1.5rem; }
.empty-state { text-align: center; padding: 3rem; color: #64748b; }
.loading-state { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.btn { padding: 0.4rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: 1px solid #e2e8f0; }
.btn-primary { background: #2563eb; color: white; border-color: #2563eb; }
.btn-outline { background: transparent; color: #334155; }
.btn-outline:hover { background: #f1f5f9; }
.btn-sm { padding: 0.2rem 0.6rem; font-size: 0.8rem; }
</style>