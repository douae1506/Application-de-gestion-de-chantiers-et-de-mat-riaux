<template>
  <div class="clients-page">

    <!-- HEADER -->
    <div class="page-header">
      <div>
        <h1>Gestion des clients</h1>
        <p>{{ filteredClients.length }} client{{ filteredClients.length > 1 ? 's' : '' }} trouvé{{ filteredClients.length > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary" @click="openModal('create')">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Ajouter un client
      </button>
    </div>

    <!-- STATS -->
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

    <!-- TOOLBAR -->
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

    <!-- TABLE VIEW -->
    <div v-if="view === 'table'" class="table-card">
      <table class="clients-table">
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
          <tr v-for="c in paginatedClients" :key="c.id" @click="goToProfile(c)" :class="{ selected: selected.includes(c.id) }">
            <td><input type="checkbox" :value="c.id" v-model="selected" /></td>
            <td>
              <div class="client-cell">
                <div class="avatar" :style="{ background: avatarColor(c.type_client) }">
                  <img v-if="c.photo" :src="getPhotoUrl(c.photo)" alt="photo" />
                  <span v-else>{{ initials(c) }}</span>
                </div>
                <div>
                  <p class="client-name">{{ c.prenom }} {{ c.nom }}</p>
                  <p class="client-email">{{ c.email }}</p>
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
                <button class="action-btn edit" @click.stop="openModal('edit', c)" title="Modifier">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                </button>
                <button class="action-btn delete" @click.stop="confirmDelete(c)" title="Supprimer">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredClients.length === 0">
            <td colspan="9" class="empty-row">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <p>Aucun client trouvé</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
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

    <!-- CARDS VIEW -->
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
          <p class="cc-email">{{ c.email }}</p>
          <span class="type-badge" :class="'type-' + c.type_client">{{ typeLabel(c.type_client) }}</span>
          <div class="cc-actions">
            <button class="action-btn edit" @click.stop="openModal('edit', c)"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></button>
            <button class="action-btn delete" @click.stop="confirmDelete(c)"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg></button>
          </div>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- MODAL CREATE / EDIT                                      -->
    <!-- ════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">

          <!-- Header -->
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
            <button class="modal-close" @click="closeModal">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <div v-if="formError" class="form-alert">⚠ {{ formError }}</div>
<div class="form-row">

    <div class="form-field">
        <label>Nom</label>
        <input
            v-model="form.nom"
            placeholder="Nom">
    </div>

    <div class="form-field">
        <label>Prénom</label>
        <input
            v-model="form.prenom"
            placeholder="Prénom">
    </div>

</div>
 <!-- Email -->
            <div class="form-field" :class="{ error: formErrors.email }">
              <label>Email <span class="req">*</span></label>
              <input v-model="form.email" type="email" placeholder="client@email.com" />
              <span v-if="formErrors.email" class="fe">{{ formErrors.email }}</span>
            </div>

            <!-- Téléphone -->
            <div class="form-field">
              <label>Téléphone</label>
              <input v-model="form.telephone" type="tel" placeholder="+212 5XX XXX XXX" />
            </div>
            <div class="form-field">
              <label>Téléphone secondaire</label>
              <input v-model="form.telephone_secondaire" type="text" placeholder="+212...">
            </div>
        <!-- Ville / Entreprise -->
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
    <textarea
        rows="2"
        v-model="form.adresse"
        placeholder="Adresse complète">
    </textarea>
</div>

            <!-- Type client -->
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
                <input type="date" v-model="form.dernier_contact"
    >
</div>

            <!-- Notes -->
            <div class="form-field">
              <label>Notes</label>
              <textarea v-model="form.notes" placeholder="Informations complémentaires…" rows="3"></textarea>
            </div>

            
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button class="btn-cancel" @click="closeModal">Annuler</button>
            <button class="btn-save" @click="saveClient" :disabled="saving">
              <span v-if="saving" class="mini-spinner"></span>
              {{ saving ? 'Enregistrement…' : (modalMode === 'create' ? 'Créer le client' : 'Enregistrer') }}
            </button>
          </div>
        </div>
      </div>

      <!-- DELETE CONFIRMATION -->
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="confirm-modal">
          <div class="confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <h3>Supprimer ce client ?</h3>
          <p>Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget?.prenom }} {{ deleteTarget?.nom }}</strong> ? Cette action est irréversible.</p>
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
    const matchStatus = filterStatus.value === '' ||
      String(c.est_actif) === filterStatus.value
    return matchSearch && matchType && matchStatus
  })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredClients.value.length / perPage))
)

const paginatedClients = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredClients.value.slice(start, start + perPage)
})

const countByType = (type) =>
  clients.value.filter(c => c.type_client === type).length

// ── Sélection (checkbox) ────────────────────────────────────
const selected = ref([])
const allSelected = computed(() =>
  paginatedClients.value.every(c => selected.value.includes(c.id))
)

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
    clients.value = res.data.data
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
  router.push(`/clients/${client.id}`)
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
      ville: '',
      entreprise: '',
      type_client: 'particulier',
      notes: '',
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

function handleFile(e) {
  const file = e.target.files[0]
  if (file) form.photo = file
}

function validate() {
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  if (!form.prenom.trim()) formErrors.prenom = 'Le prénom est requis.'
  if (!form.nom.trim()) formErrors.nom = 'Le nom est requis.'
  if (!form.email.trim()) formErrors.email = 'L\'email est requis.'
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
/* ── Page header ──────────────────────────────── */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  gap: 1rem;
  flex-wrap: wrap;
}
.page-header h1 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e2a4a;
  margin: 0 0 0.2rem;
}
.page-header p {
  font-size: 0.875rem;
  color: #8a94b2;
  margin: 0;
}
.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.1rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: opacity 0.15s;
  white-space: nowrap;
}
.btn-primary svg {
  width: 15px;
  height: 15px;
}
.btn-primary:hover {
  opacity: 0.9;
}

/* ── Stats bar ────────────────────────────────── */
.stats-bar {
  display: flex;
  gap: 0.6rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.stat-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 1rem;
  border-radius: 20px;
  border: 1.5px solid #e4e9f2;
  background: #fff;
  font-size: 0.82rem;
  font-weight: 500;
  color: #8a94b2;
  cursor: pointer;
  transition: all 0.15s;
}
.stat-pill strong {
  color: #1e2a4a;
  margin-left: 0.25rem;
}
.stat-pill .stat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.stat-pill--all .stat-dot {
  background: #6366f1;
}
.stat-pill--part .stat-dot {
  background: #6366f1;
}
.stat-pill--ent .stat-dot {
  background: #fb923c;
}
.stat-pill.active {
  border-color: #6366f1;
  background: #eff6ff;
  color: #4f46e5;
}
.stat-pill--ent.active {
  border-color: #fb923c;
  background: #fff7ed;
  color: #ea580c;
}

/* ── Toolbar ──────────────────────────────────── */
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 0.75rem;
  flex-wrap: wrap;
}
.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fff;
  border: 1.5px solid #e4e9f2;
  border-radius: 10px;
  padding: 0.5rem 0.85rem;
  flex: 1;
  max-width: 360px;
  transition: border-color 0.2s;
}
.search-box:focus-within {
  border-color: #6366f1;
}
.search-box svg {
  width: 16px;
  height: 16px;
  color: #8a94b2;
  flex-shrink: 0;
}
.search-box input {
  border: none;
  outline: none;
  font-size: 0.875rem;
  color: #1e2a4a;
  flex: 1;
  background: none;
}
.search-box input::placeholder {
  color: #94a3b8;
}
.clear-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}
.filter-select {
  padding: 0.5rem 0.85rem;
  border: 1.5px solid #e4e9f2;
  border-radius: 10px;
  font-size: 0.875rem;
  color: #1e2a4a;
  background: #fff;
  cursor: pointer;
  outline: none;
}
.view-toggle {
  display: flex;
  background: #f0f2f8;
  border-radius: 8px;
  padding: 3px;
  gap: 2px;
}
.view-toggle button {
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #8a94b2;
  transition: all 0.15s;
}
.view-toggle button svg {
  width: 16px;
  height: 16px;
}
.view-toggle button.active {
  background: #fff;
  color: #6366f1;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* ── Table ────────────────────────────────────── */
.table-card {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e4e9f2;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}
.clients-table {
  width: 100%;
  border-collapse: collapse;
}
.clients-table thead {
  background: #f8fafc;
}
.clients-table th {
  padding: 0.85rem 1rem;
  text-align: left;
  font-size: 0.72rem;
  font-weight: 700;
  color: #8a94b2;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e4e9f2;
  white-space: nowrap;
}
.clients-table td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid #f0f2f8;
  font-size: 0.875rem;
  color: #1e2a4a;
  vertical-align: middle;
}
.clients-table tr:last-child td {
  border-bottom: none;
}
.clients-table tr.selected td {
  background: #f5f3ff;
}
.clients-table tr:hover td {
  background: #fafbff;
}
.clients-table input[type="checkbox"] {
  width: 15px;
  height: 15px;
  accent-color: #6366f1;
  cursor: pointer;
}
.client-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
  overflow: hidden;
}
.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.client-name {
  font-weight: 600;
  color: #1e2a4a;
  margin: 0;
}
.client-email {
  font-size: 0.78rem;
  color: #8a94b2;
  margin: 0.1rem 0 0;
}
.type-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}
.type-particulier {
  background: rgba(99, 102, 241, 0.1);
  color: #4f46e5;
}
.type-entreprise {
  background: rgba(251, 146, 60, 0.1);
  color: #ea580c;
}
.contact-line {
  font-size: 0.82rem;
  color: #475569;
  margin: 0;
}
.email-cell {
  font-size: 0.82rem;
}
.status-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  border: none;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.status-toggle .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.status-toggle.active {
  background: rgba(34, 197, 94, 0.1);
  color: #15803d;
}
.status-toggle.active .status-dot {
  background: #22c55e;
}
.status-toggle.inactive {
  background: rgba(148, 163, 184, 0.12);
  color: #64748b;
}
.status-toggle.inactive .status-dot {
  background: #94a3b8;
}
.action-btns {
  display: flex;
  gap: 0.4rem;
}
.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.action-btn svg {
  width: 14px;
  height: 14px;
}
.action-btn.edit {
  background: rgba(99, 102, 241, 0.08);
  color: #6366f1;
}
.action-btn.edit:hover {
  background: rgba(99, 102, 241, 0.18);
}
.action-btn.delete {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
}
.action-btn.delete:hover {
  background: rgba(239, 68, 68, 0.18);
}
.empty-row {
  text-align: center;
  padding: 3rem !important;
}
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  color: #94a3b8;
}
.empty-state svg {
  width: 48px;
  height: 48px;
}
.empty-state p {
  font-size: 0.875rem;
}

/* ── Pagination ────────────────────────────────── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.25rem;
  border-top: 1px solid #f0f2f8;
}
.page-info {
  font-size: 0.8rem;
  color: #8a94b2;
}
.page-btns {
  display: flex;
  gap: 0.3rem;
}
.page-btns button {
  width: 30px;
  height: 30px;
  border-radius: 7px;
  border: 1.5px solid #e4e9f2;
  background: #fff;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.page-btns button svg {
  width: 14px;
  height: 14px;
}
.page-btns button:hover:not(:disabled) {
  border-color: #6366f1;
  color: #6366f1;
}
.page-btns button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.page-btns button.current {
  background: #6366f1;
  border-color: #6366f1;
  color: #fff;
}

/* ── Cards view ────────────────────────────────── */
.client-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}
.client-card {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e4e9f2;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}
.cc-header {
  padding: 1.5rem 1rem 3rem;
  position: relative;
  display: flex;
  justify-content: flex-end;
}
.cc-avatar {
  position: absolute;
  bottom: -20px;
  left: 50%;
  transform: translateX(-50%);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.25);
}
.cc-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.cc-avatar span {
  font-size: 0.85rem;
  font-weight: 700;
  color: rgba(229, 152, 93, 0.48);
}
.cc-status {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.6rem;
  border-radius: 10px;
  font-size: 0.68rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}
.cc-status.active {
  background: rgba(34, 197, 94, 0.3);
}
.cc-status.inactive {
  background: rgba(148, 163, 184, 0.3);
}
.cc-body {
  padding: 1.5rem 1rem 1rem;
  text-align: center;
}
.cc-body h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e2a4a;
  margin: 0 0 0.2rem;
}
.cc-email {
  font-size: 0.75rem;
  color: #8a94b2;
  margin: 0 0 0.75rem;
}
.cc-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-top: 0.85rem;
}

/* ── Modal ─────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  backdrop-filter: blur(4px);
}
.modal {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 540px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}
.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #f0f2f8;
}
.modal-title-area {
  display: flex;
  align-items: flex-start;
  gap: 0.9rem;
}
.modal-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
}
.modal-icon svg {
  width: 20px;
  height: 20px;
}
.modal-title-area h2 {
  font-size: 1rem;
  font-weight: 700;
  color: #1e2a4a;
  margin: 0 0 0.2rem;
}
.modal-title-area p {
  font-size: 0.8rem;
  color: #8a94b2;
  margin: 0;
}
.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #8a94b2;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background 0.15s;
}
.modal-close:hover {
  background: #f0f2f8;
  color: #1e2a4a;
}
.modal-close svg {
  width: 18px;
  height: 18px;
}
.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.form-alert {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.9rem;
}
.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.form-field label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
}
.req {
  color: #f857a6;
}
.form-field input,
.form-field textarea {
  padding: 0.65rem 0.9rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  color: #1e2a4a;
  outline: none;
  background: #f9fafb;
  transition: all 0.2s;
  width: 100%;
}
.form-field input:focus,
.form-field textarea:focus {
  border-color: #6366f1;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
}
.form-field.error input,
.form-field.error textarea {
  border-color: #ef4444;
}
.fe {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 500;
}
.preview-photo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #f8fafc;
}
.preview-photo img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e5e7eb;
}
.photo-info {
  display: flex;
  flex-direction: column;
}
.photo-label {
  font-size: 12px;
  color: #64748b;
}
.photo-name {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}
.type-selector {
  display: flex;
  gap: 0.5rem;
}
.type-opt {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  padding: 0.75rem 0.5rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  text-align: center;
  font-size: 0.72rem;
  font-weight: 600;
  color: #64748b;
  transition: all 0.15s;
  background: #f9fafb;
}
.type-opt.selected {
  border-color: #6366f1;
  background: #eff6ff;
  color: #4f46e5;
}
.type-opt-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.type-opt-icon svg {
  width: 18px;
  height: 18px;
}
.toggle-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.toggle-switch {
  width: 44px;
  height: 24px;
  border-radius: 12px;
  background: #e2e8f0;
  border: none;
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
  padding: 0;
}
.toggle-switch.on {
  background: linear-gradient(135deg, #22c55e, #16a34a);
}
.toggle-knob {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  position: absolute;
  top: 3px;
  left: 3px;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
.toggle-switch.on .toggle-knob {
  transform: translateX(20px);
}
.toggle-label {
  font-size: 0.875rem;
  color: #475569;
  font-weight: 500;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #f0f2f8;
}
.btn-cancel {
  padding: 0.6rem 1.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-save {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.25rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.btn-save:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
.mini-spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  flex-shrink: 0;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Confirm modal */
.confirm-modal {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  max-width: 380px;
  width: 100%;
  text-align: center;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}
.confirm-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: #ef4444;
}
.confirm-icon svg {
  width: 28px;
  height: 28px;
}
.confirm-modal h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e2a4a;
  margin: 0 0 0.5rem;
}
.confirm-modal p {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.6;
  margin: 0 0 1.5rem;
}
.confirm-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}
.btn-delete {
  padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* ── Responsive ────────────────────────────────── */
@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  .type-selector {
    flex-wrap: wrap;
  }
}

/* =======================
   CLIENT CARDS
======================= */

.client-cards{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:28px;
    margin-top:25px;
}

.client-card{
    background:#fff;
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 8px 30px rgba(15,23,42,.08);
    transition:.35s;
    cursor:pointer;
    position:relative;
    border:1px solid #eef2f7;
}

.client-card:hover{
    transform:translateY(-8px);
    box-shadow:0 18px 45px rgba(99,102,241,.18);
}

/* HEADER */

.cc-header{
    height:110px;
    position:relative;
    display:flex;
    justify-content:flex-end;
    padding:18px;
}

/* Avatar */

.cc-avatar{
    position:absolute;
    left:50%;
    bottom:-42px;
    transform:translateX(-50%);
    width:85px;
    height:85px;
    border-radius:50%;
    overflow:hidden;
    border:5px solid #fff;
    background:#eef2ff;
    box-shadow:0 10px 20px rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:700;
    color:#4f46e5;
}

.cc-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* Status */

.cc-status{
    height:34px;
    padding:0 15px;
    border-radius:30px;
    display:flex;
    align-items:center;
    font-size:13px;
    font-weight:600;
}

.cc-status.active{
    background:#dcfce7;
    color:#15803d;
}

.cc-status.inactive{
    background:#fee2e2;
    color:#dc2626;
}

/* BODY */

.cc-body{
    padding:55px 22px 22px;
}

.cc-body h4{
    margin:0;
    text-align:center;
    font-size:21px;
    color:#1e293b;
    font-weight:700;
}

.cc-email{
    text-align:center;
    color:#64748b;
    font-size:14px;
    margin:8px 0 18px;
}

/* Infos */

.client-info{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-bottom:20px;
}

.client-info div{
    display:flex;
    align-items:center;
    gap:10px;
    color:#475569;
    font-size:14px;
}

.client-info svg{
    width:18px;
    height:18px;
    color:#6366f1;
}

/* Badge */

.type-badge{
    display:inline-flex;
    margin:auto;
    padding:8px 18px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.type-particulier{
    background:#ede9fe;
    color:#6d28d9;
}

.type-entreprise{
    background:#dbeafe;
    color:#1d4ed8;
}

/* Actions */

.cc-actions{
    display:flex;
    justify-content:center;
    gap:15px;
    margin-top:22px;
    padding-top:18px;
    border-top:1px solid #edf2f7;
}

.action-btn{
    width:46px;
    height:46px;
    border:none;
    border-radius:14px;
    display:flex;
    justify-content:center;
    align-items:center;
    cursor:pointer;
    transition:.25s;
}

.action-btn svg{
    width:20px;
    height:20px;
}

.action-btn.edit{
    background:#eef2ff;
    color:#4f46e5;
}

.action-btn.edit:hover{
    background:#4f46e5;
    color:white;
}

.action-btn.delete{
    background:#fef2f2;
    color:#ef4444;
}

.action-btn.delete:hover{
    background:#ef4444;
    color:white;
}
</style>