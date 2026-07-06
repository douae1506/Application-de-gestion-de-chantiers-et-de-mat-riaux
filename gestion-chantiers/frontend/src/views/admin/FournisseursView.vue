<template>
  <div class="fv-wrap">

    <!-- Header -->
    <div class="fv-header">
      <div>
        <h1>Gestion des Fournisseurs</h1>
        <p>Gérez la liste de vos fournisseurs (matériaux, équipements, etc.)</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">+ Nouveau fournisseur</button>
    </div>

    <!-- Statistiques rapides -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
        <div><strong>{{ fournisseurs.length }}</strong><span>Fournisseurs</span></div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="table-card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div><span>Chargement...</span>
      </div>
      <div v-else-if="fournisseurs.length" class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Responsable</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Adresse</th>
              <th>Ville</th>
              <th>Pays</th>
              <th>Code postal</th>
              <th>Site web</th>
              <th>Observations</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
  <tr v-for="f in fournisseurs" :key="f.id">
    <td><strong>{{ f.nom }}</strong></td>

    <td>{{ f.responsable || '—' }}</td>

    <td>
      <a v-if="f.email" :href="'mailto:' + f.email">
        {{ f.email }}
      </a>
      <span v-else>—</span>
    </td>

    <td>{{ f.telephone || '—' }}</td>

    <td>{{ f.adresse || '—' }}</td>

    <td>{{ f.ville || '—' }}</td>

    <td>{{ f.pays || '—' }}</td>

    <td>{{ f.code_postal || '—' }}</td>

    <td>
      <a
        v-if="f.site_web"
        :href="f.site_web"
        target="_blank"
      >
        {{ f.site_web }}
      </a>
      <span v-else>—</span>
    </td>

    <td>{{ f.observations || '—' }}</td>

    <td>
      <div class="action-btns">
        <button class="btn btn-sm btn-outline" @click="openEditModal(f)">
          ✏️
        </button>
        <button class="btn btn-sm btn-danger" @click="supprimer(f.id)">
          🗑
        </button>
      </div>
    </td>
  </tr>
</tbody>
        </table>
      </div>
      <div v-else class="empty-state">
        <span>🏢</span>
        <h3>Aucun fournisseur</h3>
        <p>Créez votre premier fournisseur pour commencer à gérer vos approvisionnements.</p>
        <button class="btn btn-primary" @click="openCreateModal">+ Créer un fournisseur</button>
      </div>
    </div>

    <!-- Modal de création / édition -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>{{ editMode ? '✏️ Modifier le fournisseur' : '+ Nouveau fournisseur' }}</h3>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group col-span-2">
              <label>Nom *</label>
              <input v-model="form.nom" class="form-input" placeholder="Ex : Matériaux du Maroc" />
            </div>
            <div class="form-group">
              <label>Responsable</label>
              <input v-model="form.responsable" class="form-input" placeholder="Nom du contact" />
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input v-model="form.telephone" class="form-input" placeholder="+212 6xx-xxxxxx" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="form.email" class="form-input" placeholder="contact@fournisseur.com" />
            </div>
            <div class="form-group">
              <label>Site web</label>
              <input v-model="form.site_web" class="form-input" placeholder="https://..." />
            </div>
            <div class="form-group">
              <label>Ville</label>
              <input v-model="form.ville" class="form-input" placeholder="Casablanca" />
            </div>
            <div class="form-group">
              <label>Pays</label>
              <input v-model="form.pays" class="form-input" placeholder="Maroc" />
            </div>
            <div class="form-group">
              <label>Code postal</label>
              <input v-model="form.code_postal" class="form-input" placeholder="20000" />
            </div>
            <div class="form-group col-span-2">
              <label>Adresse</label>
              <input v-model="form.adresse" class="form-input" placeholder="Adresse complète" />
            </div>
            <div class="form-group col-span-2">
              <label>Observations</label>
              <textarea v-model="form.observations" class="form-input" rows="2"></textarea>
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
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const fournisseurs = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const editMode = ref(false)
const editId = ref(null)
const formError = ref('')

const form = reactive({
  nom: '',
  responsable: '',
  email: '',
  telephone: '',
  adresse: '',
  ville: '',
  pays: '',
  code_postal: '',
  site_web: '',
  observations: '',
})

// Charger la liste
async function fetchFournisseurs() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/fournisseurs')
    fournisseurs.value = data.data
  } catch (e) {
    console.error('Erreur chargement fournisseurs', e)
  } finally {
    loading.value = false
  }
}

// Ouvrir modal de création
function openCreateModal() {
  editMode.value = false
  editId.value = null
  Object.assign(form, {
    nom: '',
    responsable: '',
    email: '',
    telephone: '',
    adresse: '',
    ville: '',
    pays: '',
    code_postal: '',
    site_web: '',
    observations: '',
  })
  formError.value = ''
  showModal.value = true
}

// Ouvrir modal d'édition
function openEditModal(f) {
  editMode.value = true
  editId.value = f.id
  Object.assign(form, {
    nom: f.nom,
    responsable: f.responsable || '',
    email: f.email || '',
    telephone: f.telephone || '',
    adresse: f.adresse || '',
    ville: f.ville || '',
    pays: f.pays || '',
    code_postal: f.code_postal || '',
    site_web: f.site_web || '',
    observations: f.observations || '',
  })
  formError.value = ''
  showModal.value = true
}

// Sauvegarder (création ou mise à jour)
async function save() {
  formError.value = ''
  if (!form.nom) {
    formError.value = 'Le nom est requis.'
    return
  }

  saving.value = true
  try {
    if (editMode.value) {
      await api.put(`/admin/fournisseurs/${editId.value}`, form)
    } else {
      await api.post('/admin/fournisseurs', form)
    }
    showModal.value = false
    await fetchFournisseurs()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement.'
  } finally {
    saving.value = false
  }
}

// Supprimer
async function supprimer(id) {
  if (!confirm('Supprimer définitivement ce fournisseur ?')) return
  try {
    await api.delete(`/admin/fournisseurs/${id}`)
    await fetchFournisseurs()
  } catch (e) {
    alert('Erreur lors de la suppression.')
  }
}

onMounted(() => {
  fetchFournisseurs()
})
</script>

<style scoped>
.fv-wrap {
  min-height: 100vh;
  background: #f4f7fc;
  margin: -30px !important;
  padding: 0 1.5rem;
  font-family: ui-sans-serif, system-ui, sans-serif;
}
.fv-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem 0 1rem;
}
.fv-header h1 {
  margin: 0 0 .25rem;
  font-size: 1.6rem;
  font-weight: 800;
  color: #0f172a;
}
.fv-header p {
  margin: 0;
  color: #64748b;
  font-size: .9rem;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon svg {
  width: 22px;
  height: 22px;
}
.stat-icon.blue {
  background: #eff6ff;
  color: #2563eb;
}
.stat-card strong {
  display: block;
  font-size: 1.4rem;
  font-weight: 800;
  color: #0f172a;
}
.stat-card span {
  font-size: .8rem;
  color: #64748b;
}

.table-card {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  margin-bottom: 2rem;
}
.table-wrap {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .88rem;
}
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
}
.data-table td {
  padding: .75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.data-table tbody tr:hover td {
  background: #f8fafc;
}
.data-table tbody tr:last-child td {
  border-bottom: none;
}
.data-table a {
  color: #2563eb;
  text-decoration: none;
}
.data-table a:hover {
  text-decoration: underline;
}
.action-btns {
  display: flex;
  gap: .4rem;
}

.loading-state {
  display: flex;
  align-items: center;
  gap: 1rem;
  justify-content: center;
  min-height: 200px;
  color: #64748b;
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin .8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}
.empty-state span {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}
.empty-state h3 {
  margin: 0 0 .5rem;
  color: #0f172a;
}
.empty-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.btn {
  padding: .5rem 1rem;
  font-size: .85rem;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  transition: all .2s;
}
.btn-primary {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}
.btn-primary:hover {
  background: #1d4ed8;
}
.btn-secondary {
  background: #fff;
  color: #334155;
}
.btn-secondary:hover {
  background: #f8fafc;
}
.btn-danger {
  background: #fff1f2;
  color: #e11d48;
  border-color: #ffe4e6;
}
.btn-danger:hover {
  background: #ffe4e6;
}
.btn-outline {
  background: transparent;
  border: 1px solid #2563eb;
  color: #2563eb;
}
.btn-outline:hover {
  background: #2563eb;
  color: #fff;
}
.btn-sm {
  padding: .25rem .6rem;
  font-size: .78rem;
}
.btn:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.modal-box {
  background: #fff;
  border-radius: 16px;
  width: 560px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 1;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #64748b;
}
.modal-body {
  padding: 1.25rem 1.5rem;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: .75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.col-span-2 {
  grid-column: span 2;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: .4rem;
}
.form-group label {
  font-size: .85rem;
  font-weight: 600;
  color: #475569;
}
.form-input {
  padding: .6rem .75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: .9rem;
  color: #0f172a;
  background: #f8fafc;
  width: 100%;
  box-sizing: border-box;
}
.form-input:focus {
  outline: 2px solid #2563eb;
  border-color: #2563eb;
}
.form-error {
  color: #e11d48;
  font-size: .85rem;
  margin-top: .5rem;
}

@media (max-width: 768px) {
  .stat-grid {
    grid-template-columns: 1fr 1fr;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .col-span-2 {
    grid-column: span 1;
  }
}
</style>