<template>
  <div class="create-page">
    <div class="page-header">
      <div>
        <h1>Nouveau projet</h1>
        <p>Ajoutez un projet à un chantier existant</p>
      </div>
      <button class="btn btn-secondary" @click="router.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Retour
      </button>
    </div>

    <form @submit.prevent="saveProject" class="form-container">
      <div class="form-main-content">
        <div class="card-section">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
            <div>
              <h3>Informations générales</h3>
              <p>Identité du projet et parties prenantes</p>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Chantier parent <span class="required">*</span></label>
              <select class="form-control" v-model="form.chantier_id" required>
                <option value="">Sélectionner un chantier</option>
                <option v-for="chantier in chantiers" :key="chantier.id" :value="chantier.id">
                  {{ chantier.reference }} - {{ chantier.nom }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Chef de projet <span class="required">*</span></label>
              <select class="form-control" v-model="form.responsable_id" required>
                <option value="">Sélectionner un chef</option>
                <option v-for="user in chefs" :key="user.id" :value="user.id">
                  {{ user.prenom }} {{ user.nom }}
                </option>
              </select>
            </div>

            <div class="form-group span2">
              <label>Nom du projet <span class="required">*</span></label>
              <input class="form-control" v-model="form.nom" type="text" placeholder="Ex: Gros œuvre" required>
            </div>

            <div class="form-group span2">
              <label>Description</label>
              <textarea class="form-control" rows="3" v-model="form.description" placeholder="Détails du projet..."></textarea>
            </div>

            <div class="form-group">
              <label>Catégorie</label>
              <select class="form-control" v-model="form.categorie">
                <option value="">Sélectionner</option>
                <option value="Construction">Construction</option>
                <option value="Rénovation">Rénovation</option>
                <option value="Aménagement">Aménagement</option>
                <option value="Électricité">Électricité</option>
                <option value="Plomberie">Plomberie</option>
                <option value="Autre">Autre</option>
              </select>
            </div>

            <div class="form-group">
              <label>Priorité</label>
              <select class="form-control" v-model="form.priorite">
                <option value="faible">Faible</option>
                <option value="normale" selected>Normale</option>
                <option value="haute">Haute</option>
              </select>
            </div>
          </div>
        </div>

        <div class="card-section">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div>
              <h3>Planification & Budget</h3>
              <p>Calendrier et aspects financiers</p>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Date de début <span class="required">*</span></label>
              <input class="form-control" type="date" v-model="form.date_debut" required>
            </div>

            <div class="form-group">
              <label>Date de fin prévue</label>
              <input class="form-control" type="date" v-model="form.date_fin_prevue">
            </div>

            <div class="form-group span2">
              <label>Budget (MAD)</label>
              <input class="form-control "  type="number" step="0.01" v-model="form.budget" placeholder="0.00">
            </div>

            <div class="form-group span2">
              <label>Observations</label>
              <textarea class="form-control" rows="2" v-model="form.observations" placeholder="Notes internes..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="form-sidebar">
        <div class="card-section sticky-sidebar">
          <div class="section-header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>
            </div>
            <div>
              <h3>Récapitulatif</h3>
            </div>
          </div>

          <div class="sidebar-grid">
            <div class="recap-item">
              <span class="recap-label">Chantier</span>
              <span class="recap-value">{{ selectedChantierName || 'Non sélectionné' }}</span>
            </div>
            <div class="recap-item">
              <span class="recap-label">Chef de projet</span>
              <span class="recap-value">{{ selectedChefName || 'Non sélectionné' }}</span>
            </div>
            <div class="recap-item">
              <span class="recap-label">Budget</span>
              <span class="recap-value">{{ form.budget ? formatMAD(form.budget) : '—' }}</span>
            </div>
            <div class="recap-item">
              <span class="recap-label">Dates</span>
              <span class="recap-value">{{ form.date_debut ? formatDate(form.date_debut) : '—' }} → {{ form.date_fin_prevue ? formatDate(form.date_fin_prevue) : '—' }}</span>
            </div>
          </div>

          <hr class="separator" />

          <button class="btn btn-primary w-full" :disabled="saving" type="submit">
            <span v-if="saving" class="spinner"></span>
            {{ saving ? 'Création...' : 'Créer le projet' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import projetService from '@/services/projetService'
import chantierService from '@/services/chantierService'
import userService from '@/services/userService'

const router = useRouter()
const route = useRoute()
// Si on arrive depuis la page d'un chantier (bouton "Ajouter un projet"),
// on pré-sélectionne ce chantier et on redirige vers son onglet "Projets"
// après la création. Sinon, on redirige vers la liste globale des projets.
const chantierIdFromQuery = route.query.chantierId || null

// États
const saving = ref(false)
const chantiers = ref([])
const chefs = ref([])

// Formulaire
const form = reactive({
  chantier_id: '',
  responsable_id: '',
  nom: '',
  description: '',
  categorie: '',
  budget: null,
  priorite: 'normale',
  date_debut: '',
  date_fin_prevue: '',
  couleur: '#1a56db',
  observations: '',
})

// Computed pour affichage récapitulatif
const selectedChantierName = computed(() => {
  const found = chantiers.value.find(c => c.id == form.chantier_id)
  return found ? found.nom : null
})

const selectedChefName = computed(() => {
  const found = chefs.value.find(u => u.id == form.responsable_id)
  return found ? `${found.prenom} ${found.nom}` : null
})

// Chargement des données
async function loadData() {
  try {
    const [chantiersRes, chefsRes] = await Promise.all([
      chantierService.getChantiers(),
      userService.getChefsProjet()
    ])
    chantiers.value = chantiersRes.data.data || chantiersRes.data
    chefs.value = chefsRes.data.data || chefsRes.data
    if (chantierIdFromQuery) {
      form.chantier_id = chantierIdFromQuery
    }
  } catch (e) {
    console.error('Erreur de chargement', e)
  }
}

// Sauvegarde
async function saveProject() {
  if (!form.chantier_id || !form.nom || !form.date_debut) {
    alert('Veuillez remplir tous les champs obligatoires.')
    return
  }

  saving.value = true
  try {
    const payload = { ...form }
    // On retire le champ chef_projet_id s'il est vide
    if (!payload.responsable_id) delete payload.responsable_id
    await projetService.createProjet(payload)
    if (chantierIdFromQuery) {
      router.push({ name: 'chantier-detail', params: { id: chantierIdFromQuery }, query: { tab: 'projets' } })
    } else {
      router.push({ name: 'AdminProjets' })
    }
  } catch (e) {
    console.error(e.response)

  console.log("Status :", e.response.status)
  console.log("Errors :", e.response.data.errors)
  console.log("Message :", e.response.data.message)

  alert(JSON.stringify(e.response.data.errors))
  } finally {
    saving.value = false
  }
}

// Helpers
function formatMAD(n) {
  if (!n) return '—'
  return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(loadData)
</script>

<style scoped>
/* ── Reprise des styles du formulaire de chantier ── */
.create-page {
  margin: -30px;
  padding: 40px 24px;
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1e293b;
}

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

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}
.btn-secondary {
  background: #ffffff;
  color: #334155;
  border: 1px solid #e2e8f0;
}
.btn-secondary:hover {
  background: #f1f5f9;
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
.w-full {
  width: 100%;
}

/* Form Layout */
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

.card-section {
  background: #ffffff;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
}

.sticky-sidebar {
  position: sticky;
  top: 24px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}
.icon-wrapper {
  background: #f0fdf4;
  color: #166534;
  padding: 10px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-section:nth-child(2) .icon-wrapper {
  background: #eff6ff;
  color: #1e40af;
}
.form-sidebar .icon-wrapper {
  background: #faf5ff;
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

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}
.span2 {
  grid-column: span 2;
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
.required {
  color: #ef4444;
}
.form-control {
  padding: 11px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 14px;
  color: #0f172a;
  background: #fff;
  transition: all 0.2s ease;
  outline: none;
  width: 100%;
  box-sizing: border-box;
}
.form-control:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
}
textarea.form-control {
  resize: vertical;
}

.sidebar-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.recap-item {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  padding: 6px 0;
  border-bottom: 1px solid #f1f5f9;
}
.recap-label {
  color: #64748b;
}
.recap-value {
  font-weight: 600;
  color: #0f172a;
}

.separator {
  border: 0;
  height: 1px;
  background: #e2e8f0;
  margin: 24px 0;
}

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

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .span2 { grid-column: 1; }
  .form-container {
    grid-template-columns: 1fr;
  }
}
</style>