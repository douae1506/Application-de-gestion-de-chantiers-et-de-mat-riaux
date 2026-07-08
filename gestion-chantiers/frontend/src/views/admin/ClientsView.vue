Voici le code complet corrigé et harmonisé sur le même modèle que la page des projets.

Les icônes SVG dans le bouton d'ajout (`.page-header .btn-primary`) et les boutons d'action de la table et des cartes ont été ajustés avec des classes dédiées (`.btn-icon-svg` et `.action-icon-svg`) afin d'éviter tout étirement ou problème d'alignement Flexbox, notamment sur mobile.

```vue
<template>
  <div class="clients-page">

    <div class="page-header">
      <div>
        <h1>Gestion des clients</h1>
        <p>{{ filteredClients.length }} client{{ filteredClients.length > 1 ? 's' : '' }} trouvé{{ filteredClients.length > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn btn-primary" @click="openModal('create')">
        <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon-svg"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Ajouter un client
      </button>
    </div>

    <div class="stats-bar">
      <div class="stat-pill stat-pill--all" :class="{ active: filterType === '' }" @click="filterType = ''">
        <span class="stat-dot"></span> Tous <strong>{{ clients.length }}</strong>
      </div>
      <div class="stat-pill stat-pill--part" :class="{ active: filterType === 'particulier' }" @click="filterType = 'particulier'">
        <span class="stat-dot"></span> Particuliers <strong>{{ countByType('particulier') }}</strong>
      </div>
      <div class="stat-pill stat-pill--ent" :class="{ active: filterType === 'entreprise' }" @click="filterType = 'entreprise'">
        <span class="stat-dot"></span> Entreprises <strong>{{ countByType('entreprise') }}</strong>
      </div>
    </div>

    <div class="toolbar">
      <div class="search-box">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par nom, email…" />
        <button v-if="search" class="clear-btn" @click="search = ''">×</button>
      </div>
      <div class="toolbar-right">
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
              <th><input type="checkbox" @change="toggleAll" :checked="allSelected" /></th>
              <th>Client</th>
              <th>Type</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Ville</th>
              <th>Entreprise</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in paginatedClients" :key="c.id" @click="goToProfile(c)" :class="{ selected: selected.includes(c.id) }" style="cursor: pointer;">
              <td @click.stop><input type="checkbox" :value="c.id" v-model="selected" /></td>
              <td>
                <div class="client-cell">
                  <div class="avatar" :style="{ background: avatarColor(c.type_client) }">
                    <img v-if="c.photo" :src="getPhotoUrl(c.photo)" alt="photo" />
                    <span v-else>{{ initials(c) }}</span>
                  </div>
                  <div>
                    <p class="client-name"><strong>{{ c.prenom }} {{ c.nom }}</strong></p>
                    <p class="client-email text-muted">{{ c.email }}</p>
                  </div>
                </div>
              </td>
              <td><span class="type-badge" :class="'type-' + c.type_client">{{ typeLabel(c.type_client) }}</span></td>
              <td><span class="email-cell">{{ c.email }}</span></td>
              <td><p class="contact-line">{{ c.telephone || '—' }}</p></td>
              <td>{{ c.ville || '—' }}</td>
              <td>{{ c.entreprise || '—' }}</td>
              <td>
                <button class="status-toggle" :class="c.est_actif ? 'active' : 'inactive'" @click.stop="toggleStatus(c)">
                  <span class="status-dot"></span>
                  {{ c.est_actif ? 'Actif' : 'Inactif' }}
                </button>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-action edit" @click.stop="openModal('edit', c)" title="Modifier">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon-svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                  </button>
                  <button class="btn-action delete" @click.stop="confirmDelete(c)" title="Supprimer">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon-svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredClients.length === 0">
              <td colspan="9" class="empty-row">
                <div class="empty-state">
                  <span>👥</span>
                  <h3>Aucun client trouvé</h3>
                  <p>Modifiez vos filtres ou lancez une nouvelle recherche.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination">
        <span class="page-info">{{ (currentPage-1)*perPage+1 }}–{{ Math.min(currentPage*perPage, filteredClients.length) }} sur {{ filteredClients.length }}</span>
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

    <div v-else class="client-cards">
      <div class="client-card" v-for="c in paginatedClients" :key="c.id" @click="goToProfile(c)">
        <div class="cc-header" :style="{ background: cardGradient(c.type_client) }">
          <div class="cc-avatar">
            <img v-if="c.photo" :src="getPhotoUrl(c.photo)" alt="photo" />
            <span v-else>{{ initials(c) }}</span>
          </div>
        </div>
        <div class="cc-body">
          <h4>{{ c.prenom }} {{ c.nom }}</h4>
          <p class="cc-email text-muted">{{ c.email }}</p>
          <span class="type-badge" :class="'type-' + c.type_client">{{ typeLabel(c.type_client) }}</span>
          <div class="cc-actions">
            <button class="btn-action edit" @click.stop="openModal('edit', c)">
              <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon-svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
            </button>
            <button class="btn-action delete" @click.stop="confirmDelete(c)">
              <svg viewBox="0 0 20 20" fill="currentColor" class="action-icon-svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box">

          <div class="modal-header">
            <div class="modal-title-area">
              <div class="modal-icon" :style="{ background: modalMode === 'create' ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'linear-gradient(135deg,#f857a6,#ff5858)' }">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path v-if="modalMode === 'create'" fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  <path v-else d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
              </div>
              <div>
                <h2>{{ modalMode === 'create' ? 'Ajouter un client' : 'Modifier le client' }}</h2>
                <p>{{ modalMode === 'create' ? 'Créer un nouveau client' : `Modifier ${form.prenom} ${form.nom}` }}</p>
              </div>
            </div>
            <button class="modal-close" @click="closeModal">×</button>
          </div>

          <div class="modal-body">
            <div v-if="formError" class="form-alert">⚠ {{ formError }}</div>
            
            <div class="form-row">
              <div class="form-field" :class="{ error: formErrors.nom }">
                <label>Nom <span class="req">*</span></label>
                <input v-model="form.nom" placeholder="Nom">
                <span v-if="formErrors.nom" class="fe">{{ formErrors.nom }}</span>
              </div>
              <div class="form-field" :class="{ error: formErrors.prenom }">
                <label>Prénom <span class="req">*</span></label>
                <input v-model="form.prenom" placeholder="Prénom">
                <span v-if="formErrors.prenom" class="fe">{{ formErrors.prenom }}</span>
              </div>
            </div>

            <div class="form-field" :class="{ error: formErrors.email }">
              <label>Email <span class="req">*</span></label>
              <input v-model="form.email" type="email" placeholder="client@email.com" />
              <span v-if="formErrors.email" class="fe">{{ formErrors.email }}</span>
            </div>

            <div class="form-row">
              <div class="form-field">
                <label>Téléphone</label>
                <input v-model="form.telephone" type="tel" placeholder="+212 5XX XXX XXX" />
              </div>
              <div class="form-field">
                <label>Téléphone secondaire</label>
                <input v-model="form.telephone_secondaire" type="text" placeholder="+212...">
              </div>
            </div>

            <div class="form-row">
              <div class="form-field">
                <label>Ville</label>
                <input v-model="form.ville" placeholder="Casablanca" />
              </div>
              <div class="form-field">
                <label>Entreprise</label>
                <input v-model="form.entreprise" placeholder="Nom de l'entreprise" />
              </div>
            </div>
            
            <div class="form-field">
              <label>Adresse</label>
              <textarea rows="2" v-model="form.adresse" placeholder="Adresse complète"></textarea>
            </div>

            <div class="form-field" :class="{ error: formErrors.type_client }">
              <label>Type de client <span class="req">*</span></label>
              <div class="type-selector">
                <label v-for="t in typeOptions" :key="t.value" class="type-opt" :class="{ selected: form.type_client === t.value }">
                  <input type="radio" v-model="form.type_client" :value="t.value" hidden />
                  <div class="type-opt-icon" :style="{ background: t.bg, color: t.color }">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path :d="t.icon"/></svg>
                  </div>
                  <span>{{ t.label }}</span>
                </label>
              </div>
              <span v-if="formErrors.type_client" class="fe">{{ formErrors.type_client }}</span>
            </div>

            <div class="form-field">
              <label>Dernier contact</label>
              <input type="date" v-model="form.dernier_contact">
            </div>

            <div class="form-field">
              <label>Notes</label>
              <textarea v-model="form.notes" placeholder="Informations complémentaires…" rows="3"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Annuler</button>
            <button class="btn btn-primary" @click="saveClient" :disabled="saving">
              <span v-if="saving" class="mini-spinner"></span>
              {{ saving ? 'Enregistrement…' : (modalMode === 'create' ? 'Créer le client' : 'Enregistrer') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="modal-box">
          <div class="modal-header">
            <h3>Supprimer ce client ?</h3>
            <button class="modal-close" @click="showDeleteConfirm = false">×</button>
          </div>
          <div style="padding: 1.5rem; text-align: center;">
            <p>Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget?.prenom }} {{ deleteTarget?.nom }}</strong> ? Cette action est irréversible.</p>
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
import clientService from '@/services/clientService'
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ── Données ──────────────────────────────────────────────────
const clients = ref([])
const search = ref('')
const filterType = ref('')
const filterStatus = ref('')
const view = ref('table')
const currentPage = ref(1)
const perPage = 6

// ── Filtres ──────────────────────────────────────────────────
const filteredClients = computed(() => {
  return clients.value.filter(c => {
    const q = search.value.toLowerCase()
    const matchSearch = !q ||
      `${c.nom} ${c.prenom} ${c.email}`.toLowerCase().includes(q)
    const matchType = !filterType.value || c.type_client === filterType.value
    const matchStatus = filterStatus.value === '' || String(c.est_actif) === filterStatus.value
    return matchSearch && matchType && matchStatus
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredClients.value.length / perPage)))

const paginatedClients = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredClients.value.slice(start, start + perPage)
})

const countByType = (type) => clients.value.filter(c => c.type_client === type).length

// ── Sélection (checkbox) ────────────────────────────────────
const selected = ref([])
const allSelected = computed(() => paginatedClients.value.length > 0 && paginatedClients.value.every(c => selected.value.includes(c.id)))

function toggleAll(e) {
  if (e.target.checked) {
    selected.value = paginatedClients.value.map(c => c.id)
  } else {
    selected.value = []
  }
}

// ── Chargement ──────────────────────────────────────────────
async function loadClients() {
  try {
    const res = await clientService.getClients()
    clients.value = res.data.data || res.data
  } catch (e) {
    console.error('Erreur chargement clients', e)
  }
}

onMounted(loadClients)

// ── Helpers ──────────────────────────────────────────────────
function getPhotoUrl(path) {
  if (!path) return ''
  return `http://127.0.0.1:8000/storage/${path}`
}

function initials(c) {
  return `${(c.prenom || '')[0] || ''}${(c.nom || '')[0] || ''}`.toUpperCase()
}

function typeLabel(t) {
  return t === 'particulier' ? 'Particulier' : 'Entreprise'
}

function avatarColor(type) {
  return type === 'particulier'
    ? 'linear-gradient(135deg,#6366f1,#8b5cf6)'
    : 'linear-gradient(135deg,#fb923c,#f97316)'
}

function cardGradient(type) {
  return type === 'particulier'
    ? 'linear-gradient(135deg,#6366f1,#4f46e5)'
    : 'linear-gradient(135deg,#fb923c,#f97316)'
}

// ── Navigation ──────────────────────────────────────────────
function goToProfile(client) {
  router.push({ name: 'client-profile', params: { id: client.id } })
}

// ── MODAL ────────────────────────────────────────────────────
const showModal = ref(false)
const modalMode = ref('create')
const currentPhoto = ref('')
const saving = ref(false)
const formError = ref('')
const formErrors = reactive({})

const form = reactive({
    id: null,
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
    telephone_secondaire: '',
    ville: '',
    adresse: '',
    entreprise: '',
    type_client: 'particulier',
    notes: '',
    dernier_contact: '',
})

const typeOptions = [
  {
    value: 'particulier',
    label: 'Particulier',
    bg: 'rgba(99,102,241,0.12)',
    color: '#6366f1',
    icon: 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'
  },
  {
    value: 'entreprise',
    label: 'Entreprise',
    bg: 'rgba(251,146,60,0.12)',
    color: '#fb923c',
    icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'
  }
]

function openModal(mode, client = null) {
  modalMode.value = mode
  formError.value = ''
  Object.keys(formErrors).forEach(k => delete formErrors[k])

  if (mode === 'create') {
    currentPhoto.value = ''
    Object.assign(form, {
      id: null,
      nom: '',
      prenom: '',
      email: '',
      telephone: '',
      telephone_secondaire: '',
      ville: '',
      adresse: '',
      entreprise: '',
      type_client: 'particulier',
      notes: '',
      dernier_contact: '',
      est_actif: true,
      photo: null
    })
  } else {
    currentPhoto.value = client.photo || ''
    Object.assign(form, {
      ...client,
      photo: null
    })
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function validate() {
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  if (!form.prenom?.trim()) formErrors.prenom = 'Le prénom est requis.'
  if (!form.nom?.trim()) formErrors.nom = 'Le nom est requis.'
  if (!form.email?.trim()) formErrors.email = 'L\'email est requis.'
  if (!form.type_client) formErrors.type_client = 'Sélectionnez un type.'
  return Object.keys(formErrors).length === 0
}

// ── Sauvegarde ──────────────────────────────────────────────
async function saveClient() {
  if (!validate()) return
  saving.value = true

  try {
    const data = new FormData()
    for (const key of Object.keys(form)) {
      const val = form[key]
      if (key === 'photo' && val instanceof File) {
        data.append('photo', val)
      } else if (key === 'est_actif') {
        data.append('est_actif', val ? 1 : 0)
      } else if (val !== null && val !== undefined) {
        data.append(key, val)
      }
    }

    if (modalMode.value === 'create') {
      await clientService.createClient(data)
    } else {
      await clientService.updateClient(form.id, data)
    }

    await loadClients()
    closeModal()
  } catch (error) {
    console.error('Erreur sauvegarde client', error.response?.data || error)
    formError.value = 'Une erreur est survenue lors de l\'enregistrement.'
  } finally {
    saving.value = false
  }
}

// ── Basculer statut ──────────────────────────────────────────
async function toggleStatus(client) {
  try {
    await clientService.toggleStatus(client.id)
    await loadClients()
  } catch (error) {
    console.error('Erreur changement statut', error)
  }
}

// ── Suppression ──────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteTarget = ref(null)

function confirmDelete(client) {
  deleteTarget.value = client
  showDeleteConfirm.value = true
}

async function doDelete() {
  try {
    await clientService.deleteClient(deleteTarget.value.id)
    await loadClients()
    showDeleteConfirm.value = false
  } catch (error) {
    console.error('Erreur suppression', error)
  }
}
</script>

<style scoped>
/* ── Page Layout ──────────────────────────────── */
.clients-page {
  margin: -42px;
  padding: 40px 24px;
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ── Header ───────────────────────────────────── */
.page-header { 
  display: flex; 
  align-items: center; 
  justify-content: space-between; 
  margin-bottom: 1.25rem; 
  gap: 1rem; 
  flex-wrap: wrap; 
}
.page-header h1 { font-size: 1.4rem; font-weight: 700; color: #1e2a4a; margin: 0 0 .2rem; }
.page-header p { font-size: .875rem; color: #8a94b2; margin: 0; }

.page-header .btn-primary {
  align-self: center;
  height: fit-content;
}

/* Fixation explicite de la taille du SVG dans le bouton d'ajout */
.btn-icon-svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* Fixation explicite des icônes d'actions (edit/delete) */
.action-icon-svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

/* ── Stats ────────────────────────────────────── */
.stats-bar { display: flex; gap: .6rem; margin-bottom: 1rem; flex-wrap: wrap; }
.stat-pill { display: flex; align-items: center; gap: .5rem; padding: .45rem 1rem; border-radius: 20px; border: 1.5px solid #e4e9f2; background: #fff; font-size: .82rem; font-weight: 500; color: #8a94b2; cursor: pointer; transition: all .15s; }
.stat-pill strong { color: #1e2a4a; margin-left: .25rem; }
.stat-pill .stat-dot { width: 8px; height: 8px; border-radius: 50%; }
.stat-pill--all .stat-dot { background: #6366f1; }
.stat-pill--part .stat-dot { background: #8b5cf6; }
.stat-pill--ent .stat-dot { background: #f97316; }
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
.view-toggle { display: flex; background: #f0f2f8; border-radius: 8px; padding: 3px; gap: 2px; }
.view-toggle button { width: 32px; height: 32px; border: none; background: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #8a94b2; transition: all .15s; }
.view-toggle button svg { width: 16px; height: 16px; }
.view-toggle button.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* ── Badges & Cells ───────────────────────────── */
.type-badge { display: inline-flex; align-items: center; padding: .25rem .75rem; border-radius: 20px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
.type-particulier { background: #e0e7ff; color: #4338ca; }
.type-entreprise { background: #ffedd5; color: #c2410c; }

.client-cell { display: flex; align-items: center; gap: .75rem; }
.avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem; font-weight: 600; overflow: hidden; flex-shrink: 0; }
.avatar img { width: 100%; height: 100%; object-fit: cover; }
.client-name { margin: 0; font-size: .88rem; color: #0f172a; }
.client-email { margin: 0; font-size: .75rem; }

.status-toggle { display: inline-flex; align-items: center; gap: .4rem; padding: .25rem .75rem; border-radius: 15px; border: 1px solid #e2e8f0; background: #fff; font-size: .75rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.status-toggle.active { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
.status-toggle.inactive { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
.status-toggle .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* ── Table Base Layout ────────────────────────── */
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
.data-table tbody tr.selected td { background: #eff6ff; }
.data-table tbody tr:last-child td { border-bottom: none; }

/* ── Actions buttons ──────────────────────────── */
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

/* ── Globals Buttons ──────────────────────────── */
.btn { padding: .5rem 1rem; font-size: .85rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #e2e8f0; transition: all .2s; display: inline-flex; align-items: center; gap: .4rem; }
.btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #fff; color: #334155; }
.btn-secondary:hover { background: #f8fafc; }
.btn-danger { background: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
.btn-danger:hover { background: #ffe4e6; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

/* ── Modal Structure ──────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 999; backdrop-filter: blur(4px); }
.modal-box { background: #fff; border-radius: 16px; width: 560px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; line-height: 1; }
.modal-title-area { display: flex; align-items: center; gap: 1rem; }
.modal-title-area h2 { margin: 0; font-size: 1.2rem; font-weight: 700; color: #0f172a; }
.modal-title-area p { margin: 2px 0 0; font-size: .8rem; color: #64748b; }
.modal-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
.modal-icon svg { width: 20px; height: 20px; }

.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: .75rem; background: #f8fafc; }

/* Form Elements */
.form-row { display: flex; gap: 1rem; }
.form-row .form-field { flex: 1; }
.form-field { display: flex; flex-direction: column; gap: .35rem; }
.form-field label { font-size: .8rem; font-weight: 600; color: #475569; }
.form-field label .req { color: #ef4444; }
.form-field input, .form-field textarea { padding: .55rem .75rem; border: 1.5px solid #e4e9f2; border-radius: 8px; font-size: .88rem; outline: none; transition: border-color .2s; font-family: inherit; }
.form-field input:focus, .form-field textarea:focus { border-color: #6366f1; }
.form-field.error input { border-color: #ef4444; }
.form-field .fe { font-size: .75rem; color: #ef4444; font-weight: 500; }
.form-alert { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: .75rem; border-radius: 8px; font-size: .85rem; font-weight: 500; }

/* Type selector logic */
.type-selector { display: flex; gap: .75rem; }
.type-opt { flex: 1; display: flex; align-items: center; gap: .75rem; padding: .6rem 1rem; border: 1.5px solid #e4e9f2; border-radius: 10px; cursor: pointer; transition: all .2s; font-size: .85rem; font-weight: 600; color: #475569; }
.type-opt.selected { border-color: #6366f1; background: #f5f3ff; color: #4f46e5; }
.type-opt-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.type-opt-icon svg { width: 16px; height: 16px; }

.mini-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Empty State ──────────────────────────────── */
.empty-row { padding: 0 !important; }
.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }
.empty-state h3 { margin: 0 0 .5rem; color: #0f172a; }
.empty-state p { color: #64748b; margin: 0; font-size: .88rem; }

/* ── Pagination ───────────────────────────────── */
.pagination { display: flex; align-items: center; justify-content: space-between; padding: .85rem 1.25rem; border-top: 1px solid #f0f2f8; }
.page-info { font-size: .8rem; color: #8a94b2; }
.page-btns { display: flex; gap: .3rem; }
.page-btns button { width: 30px; height: 30px; border-radius: 7px; border: 1.5px solid #e4e9f2; background: #fff; color: #475569; font-size: .8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.page-btns button svg { width: 14px; height: 14px; }
.page-btns button:hover:not(:disabled) { border-color: #2563eb; color: #2563eb; }
.page-btns button:disabled { opacity: .4; cursor: not-allowed; }
.page-btns button.current { background: #2563eb; border-color: #2563eb; color: #fff; }

/* ── Cartes ────────────────────────────────────── */
.client-cards { display: grid; grid-template-columns: repeat(auto-fill,minmax(260px,1fr)); gap: 1rem; }
.client-card { background: #fff; border-radius: 16px; border: 1px solid #e4e9f2; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); cursor: pointer; transition: box-shadow .2s; }
.client-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.06); }
.cc-header { height: 50px; position: relative; padding: 0 1rem; }
.cc-avatar { width: 54px; height: 54px; border-radius: 50%; border: 3px solid #fff; background: #eee; position: absolute; bottom: -24px; left: 1rem; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; font-weight: 700; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.cc-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cc-body { padding: 2rem 1rem 1rem; display: flex; flex-direction: column; gap: .35rem; }
.cc-body h4 { font-size: .95rem; font-weight: 700; color: #1e2a4a; margin: 0; }
.cc-email { font-size: .78rem; margin: 0; }
.cc-body .type-badge { align-self: flex-start; margin-top: .25rem; }
.cc-actions { display: flex; gap: .5rem; justify-content: flex-end; margin-top: .75rem; border-top: 1px solid #f1f5f9; padding-top: .75rem; }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 768px) {
  .clients-page { padding: 16px; margin: -16px; }
  .page-header { flex-direction: row; align-items: center; justify-content: space-between; }
  .stats-bar { gap: .4rem; }
  .stat-pill { padding: .3rem .8rem; font-size: .75rem; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-box { max-width: 100%; }
  .toolbar-right { justify-content: space-between; }
  
  /* Masquage des colonnes secondaires de la table sur mobile */
  .data-table th:nth-child(1), .data-table td:nth-child(1),
  .data-table th:nth-child(4), .data-table td:nth-child(4),
  .data-table th:nth-child(5), .data-table td:nth-child(5),
  .data-table th:nth-child(7), .data-table td:nth-child(7) { display: none; }
  
  .form-row { flex-direction: column; gap: 1rem; }
  .client-cards { grid-template-columns: 1fr; }
}
</style>

```