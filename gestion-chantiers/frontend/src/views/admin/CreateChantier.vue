<template>
  <div class="create-page">
    <div class="page-header">
      <div>
        <h1>Nouveau chantier</h1>
        <p>Configurez et lancez un nouveau projet de construction</p>
      </div>
      <button class="btn btn-secondary" @click="router.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Retour
      </button>
    </div>

    <form @submit.prevent="saveChantier" class="form-container">
      <div class="form-main-content">
        
        <div class="card-section">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div>
              <h3>Informations générales</h3>
              <p>Identité du projet et parties prenantes</p>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Client <span class="required">*</span></label>
              <select class="form-control" v-model="form.client_id" required>
                <option value="">Sélectionner un client</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.entreprise || (client.prenom + ' ' + client.nom) }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Responsable</label>
              <select class="form-control" v-model="form.responsable_id">
                <option value="">Sélectionner un responsable</option>
                <option v-for="user in responsables" :key="user.id" :value="user.id">
                  {{ user.prenom }} {{ user.nom }}
                </option>
              </select>
            </div>
            <div class="form-group">
    <label>Architecte</label>
    <input class="form-control" v-model="form.architecte" placeholder="Nom de l'architecte">
  </div>

            <div class="form-group">
              <label>Nom du chantier <span class="required">*</span></label>
              <input class="form-control" v-model="form.nom" type="text" placeholder="Ex: Résidence Les Palmiers" required>
            </div>

            <div class="form-group span2">
              <label>Description</label>
              <textarea class="form-control" rows="3" v-model="form.description" placeholder="Détails additionnels sur le projet..."></textarea>
            </div>

            <div class="form-group">
              <label>Type de chantier</label>
              <select class="form-control" v-model="form.type">
                <option value="residentiel">Résidentiel</option>
                <option value="commercial">Commercial</option>
                <option value="industriel">Industriel</option>
                <option value="public">Public</option>
              </select>
            </div>
          </div>
        </div>

        <div class="card-section">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            </div>
            <div>
              <h3>Localisation</h3>
              <p>Adresse physique géographique du chantier</p>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group span2">
              <label>Adresse <span class="required">*</span></label>
              <input class="form-control" v-model="form.adresse" placeholder="Rue, avenue, numéro..." required>
            </div>

            <div class="form-group">
              <label>Ville <span class="required">*</span></label>
              <input class="form-control" v-model="form.ville" required>
            </div>

            <div class="form-group">
              <label>Code postal</label>
              <input class="form-control" v-model="form.code_postal">
            </div>

            <div class="form-group">
              <label>Pays</label>
              <input class="form-control" v-model="form.pays">
            </div>

            <div class="form-group-row span2">
              <div class="form-group">
                <label>Latitude</label>
                <input class="form-control" type="number" step="any" v-model="form.latitude" placeholder="0.0000">
              </div>
              <div class="form-group">
                <label>Longitude</label>
                <input class="form-control" type="number" step="any" v-model="form.longitude" placeholder="0.0000">
              </div>
            </div>
          </div>
        </div>

        
      </div>

      <div class="form-sidebar">
        <div class="card-section sticky-sidebar">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div>
              <h3>Planification & Budget</h3>
            </div>
          </div>

          <div class="sidebar-grid">
            <div class="form-group">
              <label>Surface (m²)</label>
              <input class="form-control" type="number" v-model="form.surface" placeholder="0">
            </div>

            <div class="form-group">
              <label>Budget total (MAD)</label>
              <div class="input-currency-wrapper">
                <input class="form-control" type="number" v-model="form.budget_total" placeholder="0.00">
              </div>
            </div>

            <div class="form-group">
              <label>Date début</label>
              <input class="form-control" type="date" v-model="form.date_debut">
            </div>

            <div class="form-group">
              <label>Date fin prévue</label>
              <input class="form-control" type="date" v-model="form.date_fin_prevue">
            </div>

           
          </div>

          <hr class="separator" />

          <button class="btn btn-primary w-full" :disabled="saving" type="submit">
            <span v-if="saving" class="spinner"></span>
            {{ saving ? 'Enregistrement...' : 'Créer le chantier' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import userService from '@/services/userService'
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import chantierService from '@/services/chantierService'
import clientService from '@/services/clientService'

const responsables = ref([])
const router = useRouter()
const saving = ref(false)
const clients = ref([])

const form = reactive({
  client_id: '',
  responsable_id: '',
  architecte: '',  
  nom: '',
  description: '',
  type: 'residentiel',
  adresse: '',
  ville: '',
  pays: 'Maroc',
  code_postal: '',
  latitude: '',
  longitude: '',
  surface: '',
  budget_total: '',
  date_debut: '',
  date_fin_prevue: '',
  documents: [],
  materiaux: []
})

async function fetchResponsables() {
  try {
    const response = await userService.getUsers()
    const allUsers = response.data.data
    // Garder seulement les rôles 'responsable' et 'admin'
    responsables.value = allUsers.filter(user => 
      user.role === 'responsable' || user.role === 'admin'
    )
  } catch (error) {
    console.error(error)
    console.log(error.response)
    console.log(error.response?.data)
    console.log(error.response?.data?.message)
    console.log(error.response?.data?.errors)
  }
}

async function fetchClients() {
  try {
    const { data } = await clientService.getClients()
    clients.value = data.data
  } catch (error) {
    console.error(error)
    console.log(error.response)
    console.log(error.response?.data)
    console.log(error.response?.data?.message)
    console.log(error.response?.data?.errors)
  }
}

function handleDocuments(e) {
  form.documents = [...e.target.files]
}

function addMateriau() {
  form.materiaux.push({
    nom: '',
    quantite: '',
    unite: ''
  })
}

function removeMateriau(index) {
  form.materiaux.splice(index, 1)
}

async function saveChantier() {
  saving.value = true
  try {
    const payload = { ...form}
    await chantierService.createChantier(payload)
    router.push({ name: 'AdminChantiers' })
  } catch (error) {
    console.log(error.response)
    console.log(error.response?.data)
    console.log(error.response?.data?.message)
    console.log(error.response?.data?.errors)
    console.error(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchClients()
  fetchResponsables()
})
</script>

<style scoped>
/* Base Setup */
.create-page {
  
  margin: -30px;
  padding: 40px 24px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1e293b;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 6px 0;
  letter-spacing: -0.02em;
}

.page-header p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Form Layout System (Split Main & Sidebar) */
.form-container {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
  align-items: start;
}

@media (max-width: 1024px) {
  .form-container {
    grid-template-columns: 1fr;
  }
}

.form-main-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Card Style */
.card-section {
  background: #ffffff;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
}

.sticky-sidebar {
  position: sticky;
  top: 24px;
}

/* Section Header & Icons */
.section-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}

.section-header.space-between {
  justify-content: space-between;
}

.section-header.space-between .flex-gap {
  display: flex;
  align-items: center;
  gap: 16px;
}

.icon-wrapper {
  background-color: #f0fdf4; /* Light green tint accent */
  color: #166534;
  padding: 10px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-section:nth-child(2) .icon-wrapper {
  background-color: #eff6ff; /* Blue tint */
  color: #1e40af;
}

.form-sidebar .icon-wrapper {
  background-color: #faf5ff; /* Purple tint */
  color: #6b21a8;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.section-header p {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

/* Grids & Inputs */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.sidebar-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.span2 {
  grid-column: span 2;
}

.form-group-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 13px;
  font-weight: 550;
  color: #475569;
}

.form-group label .required {
  color: #ef4444;
}

/* Inputs styling */
.form-control {
  padding: 11px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 14px;
  color: #0f172a;
  background-color: #fff;
  transition: all 0.2s ease;
  outline: none;
}

.form-control:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
}

.form-control::placeholder {
  color: #94a3b8;
}

.form-control.compact {
  padding: 8px 12px;
  border-radius: 6px;
}

textarea.form-control {
  resize: vertical;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-primary {
  background: #0284c7;
  color: white;
}

.btn-primary:hover {
  background: #0369a1;
}

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-secondary {
  background: #ffffff;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.btn-add {
  background: #f0fdf4;
  color: #166534;
  border: 1px dashed #bbf7d0;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #dcfce7;
}

.btn-delete {
  background: #fef2f2;
  color: #991b1b;
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.btn-delete:hover {
  background: #fee2e2;
}

.w-full {
  width: 100%;
}

/* Materials Table Style */
.materiaux-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 12px;
}

.materiaux-table th {
  background: #f8fafc;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  text-align: left;
  padding: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.materiaux-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
}

.empty-state {
  text-align: center;
  padding: 24px;
  color: #94a3b8;
  font-size: 14px;
  border: 2px dashed #e2e8f0;
  border-radius: 10px;
}

/* Custom File Upload Styling */
.file-upload-zone {
  position: relative;
  border: 2px dashed #cbd5e1;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  background: #f8fafc;
  cursor: pointer;
  transition: border-color 0.2s;
}

.file-upload-zone:hover {
  border-color: #0284c7;
}

.hidden-file-input {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  opacity: 0;
  cursor: pointer;
}

.file-upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #64748b;
  cursor: pointer;
}

.file-upload-label svg {
  color: #94a3b8;
}

.file-upload-label span {
  font-size: 13px;
  font-weight: 600;
  color: #0284c7;
}

.file-upload-label small {
  font-size: 11px;
}

.separator {
  border: 0;
  height: 1px;
  background: #e2e8f0;
  margin: 24px 0;
}

/* Spinner elements for loading */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
