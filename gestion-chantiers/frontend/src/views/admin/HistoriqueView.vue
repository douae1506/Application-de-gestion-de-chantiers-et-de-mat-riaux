<template>
  <div class="historique-page">
    <div class="page-header">
      <div class="header-title">
        <span class="header-eyebrow">Journal de chantier</span>
        <h1>Historique des actions</h1>
        <p>Toutes les activités enregistrées sur la plateforme</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="refresh">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6M3 22v-6h6M3.5 9a9 9 0 0 1 14.85-3.36L21 8M20.5 15a9 9 0 0 1-14.85 3.36L3 16"/></svg>
          Rafraîchir
        </button>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-row">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
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

    <!-- Timeline -->
    <div v-else-if="activities.length" class="activities-list">
      <div v-for="act in activities" :key="act.id" class="activity-item">
        <div class="activity-rail">
          <div class="activity-icon" :class="iconClass(act.action)">
            <ActionIcon :action="act.action" />
          </div>
          <div class="rail-line"></div>
        </div>
        <div class="activity-card">
          <div class="activity-header">
            <span class="activity-user">{{ act.user_nom || 'Système' }}</span>
            <span class="activity-role" v-if="act.user_role">{{ act.user_role }}</span>
            <span class="activity-date">{{ formatDate(act.created_at) }}</span>
          </div>
          <div class="activity-description" v-html="highlightSearch(act.description)"></div>

          <!-- Affichage lisible des propriétés (aucun JSON brut) -->
          <div v-if="act.properties && hasContent(act.properties)" class="activity-details">
            <!-- Cas des modifications -->
            <div v-if="act.action === 'updated' && act.properties.changes" class="changes-list">
              <div v-for="(change, key) in act.properties.changes" :key="key" class="change-item">
                <span class="change-label">{{ formatFieldName(key) }}</span>
                <span class="change-values">
                  <span class="old-value">{{ formatValue(change.old) }}</span>
                  <span class="arrow">→</span>
                  <span class="new-value">{{ formatValue(change.new) }}</span>
                </span>
              </div>
            </div>

            <!-- Cas des créations -->
            <div v-else-if="act.action === 'created'" class="info-block info-block--created">
              <p class="info-title">Éléments créés</p>
              <div class="prop-grid">
                <div class="prop-row" v-for="(value, key) in cleanProps(act.properties)" :key="key">
                  <span class="prop-label">{{ formatFieldName(key) }}</span>
                  <span class="prop-value">{{ formatValue(value) }}</span>
                </div>
              </div>
            </div>

            <!-- Cas des suppressions -->
            <div v-else-if="act.action === 'deleted'" class="info-block info-block--deleted">
              <p class="info-title">Éléments supprimés</p>
              <div class="prop-grid">
                <div class="prop-row" v-for="(value, key) in cleanProps(act.properties)" :key="key">
                  <span class="prop-label">{{ formatFieldName(key) }}</span>
                  <span class="prop-value">{{ formatValue(value) }}</span>
                </div>
              </div>
            </div>

            <!-- Cas des changements de statut -->
            <div v-else-if="act.action === 'status_changed'" class="status-change">
              <span class="status-pill status-pill--old">{{ formatValue(act.properties.old_status) }}</span>
              <span class="status-arrow">➜</span>
              <span class="status-pill status-pill--new">{{ formatValue(act.properties.new_status) }}</span>
            </div>

            <!-- Cas générique -->
            <div v-else class="info-block">
              <div class="prop-grid">
                <div class="prop-row" v-for="(value, key) in cleanProps(act.properties)" :key="key">
                  <span class="prop-label">{{ formatFieldName(key) }}</span>
                  <span class="prop-value">{{ formatValue(value) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="activity-meta">
            <span class="badge-subject">{{ act.subject_type }}</span>
            <span class="badge-action" :class="actionClass(act.action)">{{ getActionLabel(act.action) }}</span>
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
import { ref, reactive, onMounted, watch, h } from 'vue'
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
  alert('Export CSV à venir...')
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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

function getActionLabel(action) {
  const map = {
    created: 'Création',
    updated: 'Modification',
    deleted: 'Suppression',
    status_changed: 'Changement de statut',
    restored: 'Restauration',
  }
  return map[action] || action
}

function actionClass(action) {
  return {
    'badge-created': action === 'created',
    'badge-updated': action === 'updated',
    'badge-deleted': action === 'deleted',
    'badge-status': action === 'status_changed',
    'badge-restored': action === 'restored',
  }
}

function highlightSearch(text) {
  if (!filters.search) return text
  const regex = new RegExp(`(${filters.search})`, 'gi')
  return text.replace(regex, '<mark>$1</mark>')
}

// ----- Formatage des champs -----
function formatFieldName(key) {
  const fieldMap = {
    'name': 'Nom', 'nom': 'Nom',
    'title': 'Titre', 'titre': 'Titre',
    'description': 'Description',
    'status': 'Statut', 'statut': 'Statut',
    'type': 'Type',
    'price': 'Prix', 'prix': 'Prix',
    'quantity': 'Quantité', 'quantite': 'Quantité',
    'email': 'Email',
    'phone': 'Téléphone', 'telephone': 'Téléphone',
    'address': 'Adresse', 'adresse': 'Adresse',
    'client_id': 'Client',
    'user_id': 'Utilisateur',
    'project_id': 'Projet',
    'chantier_id': 'Chantier',
    'stock_id': 'Stock',
    'depot_id': 'Dépôt',
    'date_debut': 'Date de début',
    'date_fin': 'Date de fin',
    'created_at': 'Date de création',
    'updated_at': 'Date de modification',
    'deleted_at': 'Date de suppression',
    'old_status': 'Ancien statut',
    'new_status': 'Nouveau statut',
    'old': 'Ancienne valeur',
    'new': 'Nouvelle valeur',
    'montant': 'Montant',
    'amount': 'Montant',
    'total': 'Total',
    'categorie': 'Catégorie',
    'category': 'Catégorie',
    'reference': 'Référence',
    'code': 'Code',
    'ville': 'Ville',
    'city': 'Ville',
  }
  return fieldMap[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Transforme un objet imbriqué en texte lisible "clé: valeur, clé: valeur"
// (utilisé UNIQUEMENT en dernier recours, jamais de JSON.stringify)
function objectToReadableText(obj) {
  if (obj === null || obj === undefined) return '—'
  if (Array.isArray(obj)) {
    return obj.map(item => (typeof item === 'object' && item !== null) ? objectToReadableText(item) : String(item)).join(', ')
  }
  if (typeof obj === 'object') {
    const entries = Object.entries(obj).filter(([k]) => !['id', 'created_at', 'updated_at'].includes(k))
    if (!entries.length) return '—'
    return entries.map(([k, v]) => `${formatFieldName(k)} : ${formatValue(v)}`).join(' · ')
  }
  return String(obj)
}

function formatValue(value) {
  if (value === null || value === undefined || value === '') return '—'
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non'
  if (typeof value === 'number') {
    if (value > 1000 || value % 1 !== 0) {
      return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(value)
    }
    return String(value)
  }
  if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}/)) {
    return new Date(value).toLocaleDateString('fr-FR')
  }
  if (typeof value === 'object') {
    if ('nom' in value) return value.nom
    if ('name' in value) return value.name
    if ('label' in value) return value.label
    return objectToReadableText(value)
  }
  return value
}

// Retire les clés techniques/vides avant affichage, pour ne garder que l'essentiel
function cleanProps(props) {
  const hidden = ['changes', 'id']
  const out = {}
  Object.entries(props || {}).forEach(([k, v]) => {
    if (hidden.includes(k)) return
    if (v === null || v === undefined || v === '') return
    out[k] = v
  })
  return out
}

function hasContent(props) {
  if (props.changes) return Object.keys(props.changes).length > 0
  return Object.keys(cleanProps(props)).length > 0 || props.old_status !== undefined
}

// ----- Icônes (SVG, plus de emoji) -----
const ICON_PATHS = {
  created: 'M12 5v14M5 12h14', // plus
  updated: 'M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z', // pencil
  deleted: 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16z', // trash
  status_changed: 'M21 2v6h-6M3 22v-6h6M3.5 9a9 9 0 0 1 14.85-3.36L21 8M20.5 15a9 9 0 0 1-14.85 3.36L3 16', // repeat
  restored: 'M3 12a9 9 0 1 0 3-6.7M3 3v5h5', // undo/restore
}

const ActionIcon = {
  props: { action: String },
  render() {
    const d = ICON_PATHS[this.action] || 'M12 8v4m0 4h.01M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z'
    return h('svg', {
      viewBox: '0 0 24 24',
      width: 22,
      height: 22,
      fill: 'none',
      stroke: 'currentColor',
      'stroke-width': 2,
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
    }, [h('path', { d })])
  }
}

watch(() => [page.value], fetchActivities)
onMounted(fetchActivities)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.historique-page {
  /* Modifications ici pour un arrière-plan blanc */
  --ink: #12172b;
  --muted: #6b7280;
  --paper: #ffffff;        /* L'arrière-plan de la page devient blanc */
  --surface: #ffffff;      /* Les cartes restent blanches */
  --detail-bg: #f8fafc;    /* Fond gris très clair pour détacher les détails */
  --rule: #e2e8f0;         /* Bordures légèrement plus prononcées pour compenser le fond blanc */
  --accent: #f5a524;
  --accent-ink: #92400e;
  --created: #0e9f6e;
  --created-bg: #e5f8f1;
  --updated: #4f5bd5;
  --updated-bg: #eceeff;
  --deleted: #e5484d;
  --deleted-bg: #fcebec;
  --status: #f5a524;
  --status-bg: #fdf1de;
  --restored: #8b5cf6;
  --restored-bg: #f2edfd;

  padding: 2rem clamp(1rem, 3vw, 2.5rem);
  background: var(--paper);
  min-height: 100vh;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--ink);
}

/* ---------- Header ---------- */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
  margin-bottom: 1.75rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--rule);
}

.header-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent-ink);
  background: var(--status-bg);
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  margin-bottom: 0.6rem;
}

.page-header h1 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.9rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0 0 0.3rem 0;
  letter-spacing: -0.01em;
}

.page-header p {
  color: var(--muted);
  margin: 0;
  font-size: 0.92rem;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  flex-shrink: 0;
}

/* ---------- Filters ---------- */
.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  margin-bottom: 2rem;
  align-items: center;
  background: var(--surface);
  padding: 0.85rem;
  border-radius: 14px;
  border: 1px solid var(--rule);
  /* Ombre subtile ajoutée pour détacher la ligne du fond blanc */
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.search-wrap {
  flex: 1;
  min-width: 220px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  color: var(--muted);
  pointer-events: none;
}

.search-input {
  padding: 0.6rem 0.75rem 0.6rem 2.3rem;
  border: 1px solid var(--rule);
  border-radius: 9px;
  width: 100%;
  font-size: 0.88rem;
  font-family: inherit;
  background: #f8fafc; /* Un gris très léger pour le champ de recherche */
  transition: all 0.15s;
}

.search-input:focus {
  outline: none;
  border-color: var(--updated);
  background: var(--surface);
  box-shadow: 0 0 0 3px rgba(79, 91, 213, 0.12);
}

.filter-select {
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--rule);
  border-radius: 9px;
  background: var(--surface);
  font-size: 0.85rem;
  font-family: inherit;
  color: var(--ink);
  min-width: 145px;
  transition: all 0.15s;
}

.filter-select:focus {
  outline: none;
  border-color: var(--updated);
  box-shadow: 0 0 0 3px rgba(79, 91, 213, 0.12);
}

.filters-row > span {
  color: #b8bfd1;
  font-size: 0.85rem;
}

/* ---------- Timeline ---------- */
.activities-list {
  display: flex;
  flex-direction: column;
}

.activity-item {
  display: flex;
  gap: 1.1rem;
}

.activity-rail {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
}

.rail-line {
  width: 2px;
  flex: 1;
  background: var(--rule);
  margin: 0.35rem 0;
}

.activity-item:last-child .rail-line {
  display: none;
}

.activity-icon {
  width: 42px;
  height: 42px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  box-shadow: 0 0 0 4px var(--surface);
}

.icon-created { background: var(--created-bg); color: var(--created); }
.icon-updated { background: var(--updated-bg); color: var(--updated); }
.icon-deleted { background: var(--deleted-bg); color: var(--deleted); }
.icon-status  { background: var(--status-bg); color: var(--status); }
.icon-restored{ background: var(--restored-bg); color: var(--restored); }

.activity-card {
  flex: 1;
  min-width: 0;
  background: var(--surface);
  border: 1px solid var(--rule);
  border-radius: 14px;
  padding: 1.1rem 1.25rem;
  margin-bottom: 1.1rem;
  /* Légère ombre par défaut pour la détacher du fond blanc */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
}

.activity-item:hover .activity-card {
  border-color: #cbd5e1;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
  transform: translateY(-1px);
}

.activity-header {
  display: flex;
  gap: 0.6rem;
  align-items: center;
  font-size: 0.86rem;
  color: var(--muted);
  flex-wrap: wrap;
}

.activity-user {
  font-weight: 700;
  color: var(--ink);
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.92rem;
}

.activity-role {
  background: #f1f5f9;
  padding: 0.1rem 0.5rem;
  border-radius: 5px;
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--muted);
  border: 1px solid var(--rule);
}

.activity-date {
  margin-left: auto;
  font-size: 0.76rem;
  color: #9aa1b5;
  font-variant-numeric: tabular-nums;
}

.activity-description {
  margin: 0.4rem 0 0.6rem;
  font-size: 0.96rem;
  color: var(--ink);
  line-height: 1.55;
}

.activity-details {
  background: var(--detail-bg); /* Utilisation du fond gris très clair */
  padding: 0.85rem 1rem;
  border-radius: 10px;
  font-size: 0.88rem;
  margin-top: 0.5rem;
  border: 1px solid var(--rule);
}

.changes-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.change-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.3rem 0;
  border-bottom: 1px dashed var(--rule);
}

.change-item:last-child {
  border-bottom: none;
}

.change-label {
  font-weight: 600;
  color: var(--muted);
  min-width: 120px;
  font-size: 0.83rem;
}

.change-values {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.old-value {
  color: var(--deleted);
  text-decoration: line-through;
  opacity: 0.75;
  font-size: 0.85rem;
}

.new-value {
  color: var(--created);
  font-weight: 700;
  font-size: 0.85rem;
}

.arrow {
  color: #b8bfd1;
}

.info-block .info-title {
  margin: 0 0 0.55rem 0;
  color: var(--ink);
  font-weight: 700;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-family: 'Space Grotesk', sans-serif;
}

.info-block--created .info-title { color: var(--created); }
.info-block--deleted .info-title { color: var(--deleted); }

.prop-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.3rem 1.5rem;
}

.prop-row {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.3rem 0;
  border-bottom: 1px dashed var(--rule);
  font-size: 0.83rem;
}

.prop-row:last-child { border-bottom: none; }

.prop-label {
  color: var(--muted);
  font-weight: 500;
}

.prop-value {
  color: var(--ink);
  font-weight: 600;
  text-align: right;
}

.status-change {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.status-pill {
  padding: 0.3rem 0.85rem;
  border-radius: 999px;
  font-size: 0.83rem;
  font-weight: 700;
}

.status-pill--old {
  background: var(--deleted-bg);
  color: #991b1b;
  text-decoration: line-through;
  opacity: 0.8;
}

.status-pill--new {
  background: var(--created-bg);
  color: #065f46;
}

.status-arrow {
  color: #b8bfd1;
}

.activity-meta {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-top: 0.85rem;
}

.badge-subject,
.badge-action,
.badge-label {
  padding: 0.22rem 0.6rem;
  border-radius: 6px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.badge-subject {
  background: #f1f5f9;
  color: var(--muted);
  border: 1px solid var(--rule);
}

.badge-created { background: var(--created-bg); color: #065f46; }
.badge-updated { background: var(--updated-bg); color: #363f9c; }
.badge-deleted { background: var(--deleted-bg); color: #991b1b; }
.badge-status  { background: var(--status-bg); color: var(--accent-ink); }
.badge-restored{ background: var(--restored-bg); color: #5b21b6; }

.badge-label {
  background: transparent;
  color: var(--muted);
  border: 1px solid var(--rule);
}

/* ---------- Pagination / states ---------- */
.pagination {
  display: flex;
  justify-content: center;
  gap: 1rem;
  align-items: center;
  margin-top: 0.5rem;
  padding: 0.9rem;
  background: var(--surface);
  border-radius: 14px;
  border: 1px solid var(--rule);
  font-size: 0.85rem;
  color: var(--muted);
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--muted);
  background: var(--surface);
  border-radius: 14px;
  border: 1px dashed var(--rule);
}

.empty-state span {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 1rem;
  opacity: 0.6;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 3rem;
  background: var(--surface);
  border-radius: 14px;
  border: 1px solid var(--rule);
  color: var(--muted);
  font-size: 0.88rem;
}

.spinner {
  width: 26px;
  height: 26px;
  border: 3px solid var(--rule);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ---------- Buttons ---------- */
.btn {
  padding: 0.55rem 1.05rem;
  border-radius: 9px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid var(--rule);
  transition: all 0.15s;
  font-size: 0.84rem;
  font-family: inherit;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.btn-primary {
  background: var(--ink);
  color: white;
  border-color: var(--ink);
}

.btn-primary:hover {
  background: #262c47;
}

.btn-outline {
  background: var(--surface);
  color: var(--ink);
}

.btn-outline:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-sm {
  padding: 0.35rem 0.85rem;
  font-size: 0.8rem;
  background: var(--surface);
  color: var(--ink);
}

.btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

mark {
  background: #fde5b0;
  padding: 0.1rem 0.15rem;
  border-radius: 3px;
}

@media (max-width: 768px) {
  .historique-page { padding: 1.25rem; }
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .header-actions { width: 100%; }
  .btn { flex: 1; justify-content: center; }
  .filters-row { flex-direction: column; }
  .filter-select { width: 100%; min-width: unset; }
  .change-item { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
  .change-values { width: 100%; justify-content: flex-start; }
  .prop-grid { grid-template-columns: 1fr; }
  .prop-row { flex-direction: column; gap: 0.1rem; }
  .prop-value { text-align: left; }
  .activity-header { flex-direction: column; align-items: flex-start; }
  .activity-date { margin-left: 0; }
  .activity-rail { display: none; }
}
</style>