<template>
  <div class="app-layout-clean">
    <main class="main-content-full">

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Chargement du chantier...</span>
      </div>

      <!-- Erreur -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchChantier" class="btn btn-primary">Réessayer</button>
      </div>

      <template v-else-if="chantier">
        <!-- Fil d'Ariane & Actions -->
        <div class="top-bar">
          <div class="breadcrumb">
            Chantiers &gt; 
            <span @click="$router.push({ name: 'chantiers' })" style="cursor:pointer;">Liste</span> &gt; 
            <span @click="$router.push({ name: 'chantier-detail', params: { id: chantier.id } })" style="cursor:pointer;">{{ chantier.nom }}</span> &gt; 
            <span class="active">Modification</span>
          </div>
          <div class="action-buttons">
            <button class="btn btn-secondary" @click="$router.push({ name: 'chantier-detail', params: { id: chantier.id } })">← Annuler</button>
            <button class="btn btn-primary" @click="updateChantier" :disabled="submitting">
              {{ submitting ? 'Enregistrement...' : '💾 Enregistrer' }}
            </button>
          </div>
        </div>

        <!-- Formulaire -->
        <div class="detail-header-card">
          <div class="form-container">
            <h2>Modifier le chantier</h2>
            <form @submit.prevent="updateChantier" class="edit-form">

              <!-- Ligne 1 : Référence (lecture seule) + Nom -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Référence</label>
                  <input type="text" v-model="chantier.reference" disabled class="form-input" />
                </div>
                <div class="form-group">
                  <label>Nom *</label>
                  <input type="text" v-model="form.nom" required class="form-input" placeholder="Ex: Siège social" />
                </div>
              </div>

              <!-- Ligne 2 : Client + Architecte -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Client</label>
                  <select v-model="form.client_id" class="form-input">
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                        {{ client.nom }} {{ client.prenom }}
                    </option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Architecte</label>
                  <input type="text" v-model="form.architecte" class="form-input" placeholder="Nom de l'architecte" />
                </div>
              </div>

              <!-- Ligne 3 : Type + Statut (aligné sur les valeurs de création) -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Type de chantier</label>
                  <select v-model="form.type" class="form-input">
                    <option value="residentiel">Résidentiel</option>
                    <option value="commercial">Commercial</option>
                    <option value="industriel">Industriel</option>
                    <option value="public">Public</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Statut</label>
                  <select v-model="form.statut" class="form-input">
                    <option value="planifie">Planifié</option>
                    <option value="en_cours">En cours</option>
                    <option value="suspendu">Suspendu</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                  </select>
                </div>
              </div>

              <!-- Ligne 4 : Surface + Budget total -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Surface (m²)</label>
                  <input type="number" step="0.01" v-model.number="form.surface" class="form-input" placeholder="0" />
                </div>
                <div class="form-group">
                  <label>Budget total (MAD) *</label>
                  <input type="number" step="0.01" v-model.number="form.budget_total" required class="form-input" placeholder="0.00" />
                </div>
              </div>

              <!-- Ligne 5 : Dates -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Date de début *</label>
                  <input type="date" v-model="form.date_debut" required class="form-input" />
                </div>
                <div class="form-group">
                  <label>Date de fin prévue</label>
                  <input type="date" v-model="form.date_fin_prevue" class="form-input" />
                </div>
              </div>

              <!-- Ligne 6 : Adresse -->
              <div class="form-group">
                <label>Adresse</label>
                <input type="text" v-model="form.adresse" class="form-input" placeholder="Rue, numéro, etc." />
              </div>

              <!-- Ligne 7 : Ville, Pays, Code postal -->
              <div class="form-row three-cols">
                <div class="form-group">
                  <label>Ville</label>
                  <input type="text" v-model="form.ville" class="form-input" placeholder="Casablanca" />
                </div>
                <div class="form-group">
                  <label>Pays</label>
                  <input type="text" v-model="form.pays" class="form-input" placeholder="Maroc" />
                </div>
                <div class="form-group">
                  <label>Code postal</label>
                  <input type="text" v-model="form.code_postal" class="form-input" placeholder="20000" />
                </div>
              </div>

              <!-- Ligne 8 : Coordonnées GPS -->
              <div class="form-row two-cols">
                <div class="form-group">
                  <label>Latitude</label>
                  <input type="text" v-model="form.latitude" class="form-input" placeholder="33.5731" />
                </div>
                <div class="form-group">
                  <label>Longitude</label>
                  <input type="text" v-model="form.longitude" class="form-input" placeholder="-7.5898" />
                </div>
              </div>

              <!-- Boutons -->
              <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="$router.push({ name: 'chantier-detail', params: { id: chantier.id } })">Annuler</button>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                  {{ submitting ? 'Enregistrement...' : '💾 Enregistrer' }}
                </button>
              </div>

            </form>
          </div>
        </div>
      </template>

    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import chantierService from '@/services/chantierService'
import clientService from '@/services/clientService'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const chantierId = route.params.id

const chantier = ref(null)
const loading = ref(true)
const error = ref(null)
const submitting = ref(false)

// Liste des clients pour le select
const clients = ref([])

// Formulaire réactif (copie des données du chantier)
const form = reactive({
  nom: '',
  reference: '',
  client_id: null,
  architecte: '',
  type: 'residentiel',   // correspond aux options du formulaire de création
  statut: 'planifie',
  surface: null,
  budget_total: null,
  date_debut: '',
  date_fin_prevue: '',
  adresse: '',
  ville: '',
  pays: '',
  code_postal: '',
  latitude: '',
  longitude: '',
})

// Fonction utilitaire pour extraire la date au format YYYY-MM-DD
function formatDate(dateStr) {
  if (!dateStr) return ''
  // Si c'est déjà une chaîne de 10 caractères (YYYY-MM-DD)
  if (dateStr.length === 10 && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) return dateStr
  try {
    const d = new Date(dateStr)
    if (!isNaN(d)) {
      return d.toISOString().split('T')[0]
    }
  } catch (e) {}
  // Fallback : extraction des 10 premiers caractères
  return dateStr.substring(0, 10) || ''
}

// Récupération du chantier et des clients
async function fetchChantier() {
  loading.value = true
  error.value = null
  try {
    const { data } = await chantierService.getChantier(chantierId)
    chantier.value = data.data
    const raw = chantier.value

    console.log(JSON.stringify(raw, null, 2))

    // Nettoyer client_id : si c'est 0, null, undefined ou chaîne vide -> null
    let clientId = raw.client_id ?? raw.client?.id ?? null
    if (clientId === 0 || clientId === '0' || clientId === null || clientId === '') {
      clientId = null
    }

    // Remplir le formulaire
    Object.assign(form, {
      nom: raw.nom || '',
      reference: raw.reference || '',
      client_id: clientId,
      architecte: raw.architecte || '',
      type: raw.type || 'residentiel',
      statut: raw.statut || 'planifie',
      surface: raw.surface !== null && raw.surface !== undefined ? Number(raw.surface) : null,
      budget_total: raw.budget_total !== null && raw.budget_total !== undefined ? Number(raw.budget_total) : null,
      date_debut: formatDate(raw.date_debut),
      date_fin_prevue: formatDate(raw.date_fin_prevue),
      adresse: raw.adresse || '',
      ville: raw.ville || '',
      pays: raw.pays || '',
      code_postal: raw.code_postal || '',
      latitude: raw.latitude || '',
      longitude: raw.longitude || '',
    })

    console.log('📝 Formulaire après assignation :', form)
  } catch (e) {
    error.value = "Impossible de charger le chantier."
    console.error(e)
  } finally {
    loading.value = false
  }
}

// Charger la liste des clients pour le select
async function fetchClients() {
  try {
    const { data } = await clientService.getClients()
    clients.value = data.data || []
    console.log(clients.value)
  } catch (e) {
    console.warn("Impossible de charger les clients", e)
  }
}

// Mise à jour
async function updateChantier() {
  // Validation simple
  if (!form.nom || !form.budget_total || !form.date_debut) {
    alert('Veuillez remplir tous les champs obligatoires (*).')
    return
  }

  submitting.value = true
  try {
    const payload = { ...form }
    // Supprimer les champs vides pour éviter les erreurs de validation
    if (!payload.architecte) delete payload.architecte
    if (!payload.adresse) delete payload.adresse
    if (!payload.ville) delete payload.ville
    if (!payload.pays) delete payload.pays
    if (!payload.code_postal) delete payload.code_postal
    if (!payload.latitude) delete payload.latitude
    if (!payload.longitude) delete payload.longitude
    if (!payload.date_fin_prevue) delete payload.date_fin_prevue
    if (payload.surface === null || payload.surface === '') delete payload.surface

    const { data } = await api.put(`/admin/chantiers/${chantierId}`, payload)
    // Rediriger vers le détail
    router.push({ name: 'chantier-detail', params: { id: chantierId } })
  } catch (e) {
    const msg = e.response?.data?.message || 'Erreur lors de la mise à jour.'
    alert(msg)
    console.error(e)
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchChantier()
  fetchClients()
})
</script>

<style scoped>
/* Réutilise les styles globaux de l'application */
.app-layout-clean {
  min-height: 100vh;
  background-color: #f4f7fc;
  font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
  margin: -30px !important;
  padding: 0 1.5rem;
}

.main-content-full {
  max-width: 100%;
  margin: 0 auto;
  padding: 0;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 0.5rem;
  margin-bottom: 1.5rem;
}

.breadcrumb {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}
.breadcrumb .active {
  color: #0f172a;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  transition: background-color 0.2s;
}
.btn-secondary {
  background-color: #ffffff;
  color: #334155;
}
.btn-secondary:hover {
  background-color: #f8fafc;
}
.btn-primary {
  background-color: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}
.btn-primary:hover {
  background-color: #1d4ed8;
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.detail-header-card {
  background-color: #ffffff;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.form-container h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f172a;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  gap: 1rem;
}
.two-cols {
  grid-template-columns: 1fr 1fr;
}
.three-cols {
  grid-template-columns: 1fr 1fr 1fr;
}
@media (max-width: 768px) {
  .two-cols, .three-cols {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}
.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}
.form-input {
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  background: #f8fafc;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
.form-input:disabled {
  background-color: #f1f5f9;
  color: #64748b;
  cursor: not-allowed;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid #e2e8f0;
  padding-top: 1.5rem;
  margin-top: 0.5rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  color: #64748b;
  gap: 1rem;
}
.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 3rem;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
}
</style>