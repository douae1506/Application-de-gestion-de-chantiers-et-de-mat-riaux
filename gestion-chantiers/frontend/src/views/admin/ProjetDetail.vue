<template>
  <div class="project-premium-container">
    <!-- Écran de chargement -->
    <div v-if="loading" class="premium-loader-wrapper">
      <div class="premium-spinner"></div>
    </div>

    <!-- Écran d'erreur -->
    <div v-else-if="error" class="premium-error-card">
      <div class="error-badge-icon">✕</div>
      <h3>Une erreur est survenue</h3>
      <p>{{ error }}</p>
      <button @click="fetchProject" class="btn-premium-secondary">Réessayer</button>
    </div>

    <!-- Contenu Principal -->
    <template v-else-if="project">
      
      <!-- 1. BARRE SUPÉRIEURE (Navigation & Actions) -->
      <div class="premium-top-navigation">
        <button class="btn-back-minimal" @click="goBack">
          <svg viewBox="0 0 20 20" fill="currentColor" class="icon-left"><path fill-rule="evenodd" d="M17 10a1 1 0 01-1 1H4.414l4.293 4.293a1 1 0 01-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L4.414 9H16a1 1 0 011 1z" clip-rule="evenodd"/></svg>
          Retour aux projets
        </button>
        
        <div class="premium-header-title-row">
          <div class="title-meta">
            <h2>Détail du projet</h2>
            <p class="subtitle">Informations complètes et suivi du projet</p>
          </div>
          <div class="premium-actions-group">
            <button class="btn-premium-action btn-edit-premium" @click="goToEdit">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
              Modifier
            </button>
            <button class="btn-premium-action btn-delete-premium" @click="confirmDelete">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>

      <!-- 2. EN-TÊTE PRINCIPAL (Hero Card) -->
      <div class="premium-hero-card">
        <div class="hero-left-section">
          <div class="project-avatar-cube">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <rect x="9" y="9" width="6" height="12"></rect>
              <line x1="9" y1="13" x2="15" y2="13"></line>
              <line x1="9" y1="17" x2="15" y2="17"></line>
            </svg>
          </div>
          
          <div class="project-identity-details">
            <div class="title-badge-row">
              <h3>{{ project.nom }}</h3>
              <span class="status-pill-glass" :class="'status-' + project.statut">
                {{ project.statut_label || project.statut }}
              </span>
            </div>
            
            <div class="location-row" v-if="project.chantier">
              <svg viewBox="0 0 20 20" fill="currentColor" class="icon-loc"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
              <span>{{ project.chantier.nom }}</span>
            </div>

            <div class="project-meta-features-row">
              <div class="feature-item-owner">
                <div class="owner-avatar"><span>👤</span></div>
                <div class="owner-info">
                  <span class="lbl">Chef de projet</span>
                  <span class="val">{{ project.responsable ? project.responsable.prenom + ' ' + project.responsable.nom : 'Non assigné' }}</span>
                </div>
              </div>
              <div class="feature-item">
                <span class="lbl">Date de fin prévue</span>
                <span class="val">{{ project.date_fin_prevue ? formatDate(project.date_fin_prevue) : 'Non définie' }}</span>
              </div>
              <div class="feature-item">
                <span class="lbl">Budget alloué</span>
                <span class="val text-dark font-bold">{{ formatMAD(project.budget) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="hero-right-progress">
          <div class="circular-progress-box">
            <svg viewBox="0 0 36 36" class="circular-chart">
              <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
              <path class="circle" :stroke-dasharray="project.progression + ', 100'" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
            </svg>
            <div class="percentage-inner-text">
              <span class="pct-num">{{ project.progression }}%</span>
            </div>
          </div>
          <span class="progress-sub-label">Progression globale</span>
        </div>
      </div>

      <!-- 3. ONGLES DE NAVIGATION -->
      <div class="premium-tabs-menu">
        <button class="tab-item" :class="{ active: activeTab === 'apercu' }" @click="activeTab = 'apercu'">Aperçu</button>
        <button class="tab-item" :class="{ active: activeTab === 'phases' }" @click="activeTab = 'phases'">Phases</button>
        <button class="tab-item" :class="{ active: activeTab === 'depenses' }" @click="activeTab = 'depenses'">Dépenses</button>
      </div>

      <!-- ─── CONTENU DES ONGLETS ─── -->

      <!-- ONGLET APERÇU -->
      <div v-if="activeTab === 'apercu'" class="premium-tab-content">
        <div class="premium-section-card">
          <div class="section-card-header"><h3>Informations générales</h3></div>
          <div class="info-grid">
            <div class="info-item"><span class="info-label">Référence : </span><span class="info-value">{{ project.reference || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Nom du projet : </span><span class="info-value">{{ project.nom }}</span></div>
            <div class="info-item"><span class="info-label">Chantier: </span><span class="info-value">{{ project.chantier?.nom || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Catégorie : </span><span class="info-value">{{ project.categorie || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Statut : </span><span class="status-pill-glass" :class="'status-' + project.statut">{{ project.statut_label || project.statut }}</span></div>
            <div class="info-item"><span class="info-label">Progression : </span><span class="info-value">{{ project.progression }}%</span></div>
            <div class="info-item"><span class="info-label">Date de début : </span><span class="info-value">{{ project.date_debut ? formatDate(project.date_debut) : '—' }}</span></div>
            <div class="info-item"><span class="info-label">Date de fin prévue : </span><span class="info-value">{{ project.date_fin_prevue ? formatDate(project.date_fin_prevue) : '—' }}</span></div>
            <div class="info-item"><span class="info-label">Budget : </span><span class="info-value">{{ formatMAD(project.budget) }}</span></div>
            <div class="info-item"><span class="info-label">Coût réel : </span><span class="info-value">{{ formatMAD(project.cout_reel) }}</span></div>
            <div class="info-item full-width"><span class="info-label">Description : </span><span class="info-value">{{ project.description || 'Aucune description' }}</span></div>
          </div>
        </div>

        <div v-if="project.chantier" class="premium-section-card">
          <div class="section-card-header"><h3>Chantier associé</h3></div>
          <div class="info-grid">
            <div class="info-item"><span class="info-label">Nom : </span><span class="info-value">{{ project.chantier.nom }}</span></div>
            <div class="info-item"><span class="info-label">Référence : </span><span class="info-value">{{ project.chantier.reference }}</span></div>
            <div class="info-item"><span class="info-label">Adresse : </span><span class="info-value">{{ project.chantier.adresse || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Ville : </span><span class="info-value">{{ project.chantier.ville || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Pays : </span><span class="info-value">{{ project.chantier.pays || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Code postal : </span><span class="info-value">{{ project.chantier.code_postal || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Surface : </span><span class="info-value">{{ project.chantier.surface ? project.chantier.surface + ' m²' : '—' }}</span></div>
            <div class="info-item"><span class="info-label">Budget total : </span><span class="info-value">{{ formatMAD(project.chantier.budget_total) }}</span></div>
          </div>
        </div>
      </div>

      <!-- ONGLET PHASES (avec gestion complète) -->
      <div v-if="activeTab === 'phases'" class="premium-tab-content">
        <div class="premium-section-card">
          <div class="section-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Phases du projet</h3>
            <button class="btn-premium-action btn-edit-premium" @click="openAddPhase">
              + Ajouter une phase
            </button>
          </div>

          <div v-if="project.phases && project.phases.length > 0" class="premium-timeline-wrapper">
            <div class="timeline-row-item" v-for="(phase, index) in project.phases" :key="phase.id">
              <div class="timeline-left-node">
                <div class="node-circle-index" :class="'node-' + phase.statut">{{ index + 1 }}</div>
                <div v-if="index !== project.phases.length - 1" class="node-vertical-line"></div>
              </div>
              <div class="timeline-row-main-content" style="grid-template-columns: 1.5fr 0.8fr 0.8fr 0.8fr 1fr 0.5fr;">
                <!-- Nom / Réf -->
                <div class="phase-identity-cell">
                  <span class="phase-title-text">{{ phase.nom }}</span>
                </div>
                <!-- Responsable -->
               
                <!-- Dates -->
                <div class="phase-cell">
                  <span class="lbl">Dates</span>
                  <span class="val">{{ phase.date_debut ? formatDate(phase.date_debut) : '—' }} → {{ phase.date_fin_prevue ? formatDate(phase.date_fin_prevue) : '—' }}</span>
                  <span v-if="phase.date_fin_reelle" class="phase-dates-subtext">(réelle: {{ formatDate(phase.date_fin_reelle) }})</span>
                </div>
                <!-- Progression -->
                <div class="phase-progress-cell">
                  <span class="progress-pct-label">{{ phase.progression }}%</span>
                  <div class="horizontal-progress-track">
                    <div class="horizontal-progress-fill" :style="{ width: phase.progression + '%' }" :class="'fill-' + phase.statut"></div>
                  </div>
                </div>
                <!-- Statut & Validation -->
                <div class="phase-status-cell">
                  <span class="pill-status-static" :class="'badge-' + phase.statut">
                    {{ phase.statut === 'en_cours' ? 'En cours' : phase.statut === 'terminee' ? 'Terminée' : phase.statut === 'non_commencee' ? 'Non commencée' : 'Bloquée' }}
                  </span>
                  <span v-if="phase.validation" class="badge-validation">✓ Validée</span>
                </div>
                <!-- Actions -->
                <div class="phase-actions-cell">
                  <button class="action-btn edit" @click="openEditPhase(phase)" title="Modifier">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                  </button>
                  <button class="action-btn delete" @click="deletePhase(phase.id)" title="Supprimer">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="premium-empty-state"><p>Aucune phase configurée pour ce projet.</p></div>
        </div>
      </div>

      <!-- ONGLET DÉPENSES -->
      <div v-if="activeTab === 'depenses'" class="premium-tab-content">
        <!-- Résumé financier -->
        <div class="premium-section-card">
          <div class="section-card-header"><h3>Suivi financier</h3></div>
          <div class="financial-simple-grid">
            <div class="financial-box">
              <span class="financial-label">Budget total</span>
              <span class="financial-value primary">{{ formatMAD(project.budget) }}</span>
            </div>
            <div class="financial-box">
              <span class="financial-label">Dépensé (cumul)</span>
              <span class="financial-value">{{ formatMAD(totalExpenses) }}</span>
            </div>
            <div class="financial-box">
              <span class="financial-label">Reste à dépenser</span>
              <span class="financial-value success">{{ formatMAD(project.budget - totalExpenses) }}</span>
            </div>
            <div class="financial-box">
              <span class="financial-label">% dépensé</span>
              <span class="financial-value">{{ expensePercent }}%</span>
              <div class="mini-progress-bar">
                <div class="mini-progress-fill" :style="{ width: expensePercent + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ajout d'une dépense -->
        <div class="premium-section-card">
          <div class="section-card-header"><h3>Ajouter une dépense</h3></div>
          <form @submit.prevent="addExpense" class="expense-form">
            <div class="form-row">
              <div class="form-group">
                <label>Libellé *</label>
                <input v-model="newExpense.nom" type="text" class="form-input" placeholder="Ex: Achat fer" required />
              </div>
              <div class="form-group">
                <label>Montant (MAD) *</label>
                <input v-model.number="newExpense.montant" type="number" step="0.01" class="form-input" placeholder="0.00" required />
              </div>
              <div class="form-group">
                <label>Date (optionnelle)</label>
                <input v-model="newExpense.date" type="date" class="form-input" />
              </div>
              <div class="form-group submit-group">
                <button type="submit" class="btn-primary" :disabled="expenseLoading">
                  <span v-if="expenseLoading" class="spinner"></span>
                  {{ expenseLoading ? 'Ajout...' : 'Ajouter' }}
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Liste des dépenses -->
        <div class="premium-section-card">
          <div class="section-card-header"><h3>Toutes les dépenses ({{ project.expenses?.length || 0 }})</h3></div>
          <div v-if="project.expenses && project.expenses.length > 0" class="expense-table-wrapper">
            <table class="expense-table">
              <thead>
                <tr><th>#</th><th>Libellé</th><th>Montant</th><th>Date</th><th>Action</th></tr>
              </thead>
              <tbody>
                <tr v-for="(exp, idx) in project.expenses" :key="exp.id">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ exp.nom }}</td>
                  <td>{{ formatMAD(exp.montant) }}</td>
                  <td>{{ exp.date ? formatDate(exp.date) : '—' }}</td>
                  <td>
                    <button class="action-btn delete" @click="deleteExpense(exp.id)" title="Supprimer">
                      <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr><td colspan="2"><strong>Total</strong></td><td><strong>{{ formatMAD(totalExpenses) }}</strong></td><td colspan="2"></td></tr>
              </tfoot>
            </table>
          </div>
          <div v-else class="premium-empty-state"><p>Aucune dépense enregistrée.</p></div>
        </div>
      </div>

    </template>

    <!-- Modal de confirmation de suppression du projet -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="premium-blur-overlay" @click.self="showDeleteConfirm = false">
        <div class="premium-delete-modal">
          <div class="modal-alert-graphic">⚠️</div>
          <h3>Confirmation requise</h3>
          <p>Voulez-vous vraiment supprimer le projet <strong>{{ project?.nom }}</strong> ?</p>
          <div class="modal-buttons-layout">
            <button class="btn-premium-cancel" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-premium-danger-confirm" @click="doDelete">Confirmer</button>
          </div>
        </div>
      </div>

      <!-- Modal pour ajouter/modifier une phase -->
      <div v-if="showPhaseModal" class="premium-blur-overlay" @click.self="showPhaseModal = false">
        <div class="premium-modal phase-modal">
          <div class="modal-header">
            <h3>{{ editingPhaseId ? 'Modifier la phase' : 'Ajouter une phase' }}</h3>
            <button class="modal-close" @click="showPhaseModal = false">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group full-width">
                <label>Nom *</label>
                <input v-model="phaseForm.nom" class="form-control" required />
              </div>
              <div class="form-group full-width">
                <label>Description</label>
                <textarea v-model="phaseForm.description" class="form-control" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>Ordre</label>
                <input v-model.number="phaseForm.ordre" type="number" class="form-control" min="1" />
              </div>
              <div class="form-group">
                <label>Progression (%)</label>
                <input v-model.number="phaseForm.progression" type="number" class="form-control" min="0" max="100" />
              </div>
              <div class="form-group">
                <label>Date de début *</label>
                <input v-model="phaseForm.date_debut" type="date" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Date de fin prévue</label>
                <input v-model="phaseForm.date_fin_prevue" type="date" class="form-control" />
              </div>
              <div class="form-group">
                <label>Statut</label>
                <select v-model="phaseForm.statut" class="form-control">
                  <option value="non_commencee">Non commencée</option>
                  <option value="en_cours">En cours</option>
                  <option value="terminee">Terminée</option>
                  <option value="bloquee">Bloquée</option>
                </select>
              </div>
              <div class="form-group full-width">
                <label>Observations</label>
                <textarea v-model="phaseForm.observations" class="form-control" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-premium-cancel" @click="showPhaseModal = false">Annuler</button>
            <button class="btn-primary" @click="savePhase" :disabled="phaseLoading">
              <span v-if="phaseLoading" class="spinner"></span>
              {{ phaseLoading ? 'Enregistrement...' : (editingPhaseId ? 'Modifier' : 'Ajouter') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import projetService from '@/services/projetService'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const project = ref(null)
const loading = ref(true)
const error = ref(null)
const showDeleteConfirm = ref(false)
const activeTab = ref('apercu')

// ─── États pour les dépenses ──────────────────────────────
const expenseLoading = ref(false)
const newExpense = ref({ nom: '', montant: null, date: '' })

const totalExpenses = computed(() => {
  if (!project.value?.expenses) return 0
  return project.value.expenses.reduce((sum, e) => sum + (e.montant || 0), 0)
})

const expensePercent = computed(() => {
  console.log("Budget :", project.value?.budget)
  console.log("Total dépenses :", totalExpenses.value)
  if (!project.value?.budget || project.value.budget === 0) return 0
  return Math.min(100, Math.round((totalExpenses.value / project.value.budget) * 100))
})

// ─── États pour les phases ────────────────────────────────
const showPhaseModal = ref(false)
const editingPhaseId = ref(null)
const phaseLoading = ref(false)

const phaseForm = reactive({
  id: null,
  nom: '',
  description: '',
  ordre: 1,
  date_debut: '',
  date_fin_prevue: '',
  date_fin_reelle: '',
  progression: 0,
  responsable: '',
  statut: 'non_commencee',
  validation: false,
  observations: '',
})

function toDateInputString(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  // Vérifier que la date est valide
  if (isNaN(d.getTime())) return ''
  // Extraire année, mois, jour en local (ne pas utiliser toISOString car il donne UTC)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}
// ─── Méthodes pour les phases ─────────────────────────────
function openAddPhase() {
  editingPhaseId.value = null
  Object.assign(phaseForm, {
    id: null,
    nom: '',
    description: '',
    ordre: (project.value.phases?.length || 0) + 1,
    date_debut: '',
    date_fin_prevue: '',
    date_fin_reelle: '',
    progression: 0,
    responsable: '',
    statut: 'non_commencee',
    validation: false,
    observations: '',
  })
  showPhaseModal.value = true
}

function openEditPhase(phase) {
   editingPhaseId.value = phase.id
  Object.assign(phaseForm, {
    ...phase,
    // Convertir les dates pour les champs input[type="date"]
    date_debut: toDateInputString(phase.date_debut),
    date_fin_prevue: toDateInputString(phase.date_fin_prevue),
    date_fin_reelle: toDateInputString(phase.date_fin_reelle),
  })
  showPhaseModal.value = true
}

async function savePhase() {
  if (!phaseForm.nom || !phaseForm.date_debut) {
    alert('Le nom et la date de début sont obligatoires.')
    return
  }
  phaseLoading.value = true
  try {
    // Construire le payload proprement
    const payload = {
      nom: phaseForm.nom,
      description: phaseForm.description || null,
      ordre: phaseForm.ordre || 1,
      date_debut: phaseForm.date_debut, // déjà en YYYY-MM-DD
      date_fin_prevue: phaseForm.date_fin_prevue || null,
      date_fin_reelle: phaseForm.date_fin_reelle || null,
      progression: phaseForm.progression || 0,
      responsable: phaseForm.responsable || null,
      statut: phaseForm.statut || 'non_commencee',
      validation: phaseForm.validation || false,
      observations: phaseForm.observations || null,
    }

    // Vérification supplémentaire : si date_fin_prevue est avant date_debut
    if (payload.date_fin_prevue && payload.date_fin_prevue < payload.date_debut) {
      alert('La date de fin prévue ne peut pas être antérieure à la date de début.')
      phaseLoading.value = false
      return
    }

    let response
    if (editingPhaseId.value) {
      response = await api.put(`/admin/phases/${editingPhaseId.value}`, payload)
      const index = project.value.phases.findIndex(p => p.id === editingPhaseId.value)
      if (index !== -1) {
        project.value.phases[index] = response.data.data
      }
    } else {
      response = await api.post(`/admin/projets/${project.value.id}/phases`, payload)
      if (!project.value.phases) project.value.phases = []
      project.value.phases.push(response.data.data)
    }
    showPhaseModal.value = false
  } catch (e) {
    console.error('Erreur savePhase', e.response?.data)
    // Afficher les erreurs de validation proprement
    if (e.response?.data?.errors) {
      const errors = Object.values(e.response.data.errors).flat().join('\n')
      alert('Erreurs de validation :\n' + errors)
    } else {
      alert(e.response?.data?.message || 'Erreur lors de la sauvegarde de la phase')
    }
  } finally {
    phaseLoading.value = false
  }
}

async function deletePhase(phaseId) {
  if (!confirm('Supprimer cette phase ?')) return
  try {
    await api.delete(`/admin/phases/${phaseId}`)
    project.value.phases = project.value.phases.filter(p => p.id !== phaseId)
  } catch (e) {
    alert('Erreur lors de la suppression')
  }
}

// ─── Récupération du projet ────────────────────────────────
async function fetchProject() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    const { data } = await projetService.getProjet(id)
    project.value = data.data || data
  } catch (e) {
    error.value = 'Impossible de charger les données du projet.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

// ─── Gestion des dépenses ──────────────────────────────────
async function addExpense() {
  if (!newExpense.value.nom || !newExpense.value.montant) return
  expenseLoading.value = true
  try {
    const payload = {
      nom: newExpense.value.nom,
      montant: newExpense.value.montant,
      date: newExpense.value.date || null
    }
    const { data } = await api.post(`/admin/projets/${project.value.id}/expenses`, payload)
    if (!project.value.expenses) project.value.expenses = []
    project.value.expenses.unshift(data.data)
    newExpense.value = { nom: '', montant: null, date: '' }
  } catch (e) {
    alert('Erreur lors de l\'ajout de la dépense')
    console.error(e)
  } finally {
    expenseLoading.value = false
  }
}

async function deleteExpense(expenseId) {
  if (!confirm('Supprimer cette dépense ?')) return
  try {
    await api.delete(`/admin/expenses/${expenseId}`)
    project.value.expenses = project.value.expenses.filter(e => e.id !== expenseId)
  } catch (e) {
    alert('Erreur lors de la suppression')
  }
}

// ─── Navigation ─────────────────────────────────────────────
function goBack() { router.push({ name: 'AdminProjets' }) }
function goToEdit() { router.push({ name: 'projet-edit', params: { id: project.value.id } }) }
function confirmDelete() { showDeleteConfirm.value = true }
async function doDelete() {
  try {
    await projetService.deleteProjet(project.value.id)
    router.push({ name: 'AdminProjets' })
  } catch (e) {
    alert('Erreur lors de la suppression')
  } finally {
    showDeleteConfirm.value = false
  }
}

// ─── Helpers ─────────────────────────────────────────────────
function formatMAD(n) {
  if (n === null || n === undefined) return '—'
  return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD', maximumFractionDigits: 2 }).format(n)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => { fetchProject() })
</script>

<style scoped>
/* ─── NOUVEAU STYLE PREMIUM ────────────────────────────────── */
/* FONTS & RESET */
@import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

.project-premium-container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: #f6f9fc;
  min-height: 100vh;
  padding: 2rem 2.5rem;
  margin: -48px;
  color: #1e293b;
  line-height: 1.5;
  margin:-30px;
}

/* ─── LOADER ─── */
.premium-loader-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 50vh;
}
.premium-spinner {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 4px solid #e2e8f0;
  border-top-color: #0f172a;
  animation: spin 0.8s cubic-bezier(0.6, 0.2, 0.1, 1) infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── ERROR ─── */
.premium-error-card {
  max-width: 480px;
  margin: 4rem auto;
  background: white;
  border-radius: 24px;
  padding: 3rem;
  text-align: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 40px -12px rgba(0,0,0,0.04);
}
.error-badge-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fff1f2;
  color: #e11d48;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 1.25rem;
}
.btn-premium-secondary {
  background: transparent;
  border: 1px solid #e2e8f0;
  padding: 0.6rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  color: #1e293b;
  transition: all 0.2s;
}
.btn-premium-secondary:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
}

/* ─── TOP NAVIGATION ─── */
.premium-top-navigation {
  margin-bottom: 2.5rem;
}
.btn-back-minimal {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: transparent;
  border: none;
  color: #64748b;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
  margin-bottom: 1.5rem;
  transition: all 0.2s;
}
.btn-back-minimal:hover { color: #0f172a; transform: translateX(-4px); }
.btn-back-minimal svg { width: 16px; height: 16px; }

.premium-header-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}
.title-meta h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}
.title-meta .subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0.25rem 0 0;
}

.premium-actions-group {
  display: flex;
  gap: 0.75rem;
}
.btn-premium-action {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-premium-action svg { width: 16px; height: 16px; }
.btn-edit-premium {
  background: white;
  color: #1e293b;
  border-color: #e2e8f0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.btn-edit-premium:hover {
  background: #0f172a;
  color: white;
  border-color: #0f172a;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -8px rgba(15,23,42,0.15);
}
.btn-delete-premium {
  background: #fff1f2;
  color: #e11d48;
  border-color: transparent;
}
.btn-delete-premium:hover {
  background: #ffe4e6;
  transform: translateY(-2px);
}

/* ─── HERO CARD ─── */
.premium-hero-card {
  background: white;
  border-radius: 28px;
  padding: 2.5rem 3rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 40px -12px rgba(0,0,0,0.02);
  margin-bottom: 2.5rem;
}
.hero-left-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex: 1;
}
.project-avatar-cube {
  width: 72px;
  height: 72px;
  background: #f0fdf4;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #10b981;
  border: 1px solid #dcfce7;
  flex-shrink: 0;
}
.project-avatar-cube svg { width: 32px; height: 32px; }

.project-identity-details { flex: 1; }
.title-badge-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}
.title-badge-row h3 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}
.status-pill-glass {
  padding: 0.3rem 0.8rem;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  border: 1px solid transparent;
}
.status-en_cours, .badge-en_cours { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.status-terminee, .badge-terminee { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.status-bloquee, .badge-bloquee { background: #ffe4e6; color: #b91c1c; border-color: #fecaca; }
.status-non_commencee, .badge-non_commencee { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

.location-row {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #64748b;
  font-size: 0.85rem;
  background: #f8fafc;
  padding: 0.3rem 0.8rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}
.icon-loc { width: 14px; height: 14px; }

.project-meta-features-row {
  display: flex;
  gap: 3rem;
  flex-wrap: wrap;
}
.feature-item-owner { display: flex; align-items: center; gap: 0.75rem; }
.owner-avatar {
  width: 36px;
  height: 36px;
  background: #f1f5f9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}
.feature-item, .owner-info { display: flex; flex-direction: column; }
.feature-item .lbl, .owner-info .lbl {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.feature-item .val, .owner-info .val {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
}
.feature-item .val.text-dark { color: #0f172a; font-weight: 700; }

.hero-right-progress {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding-left: 2.5rem;
  border-left: 1px solid #e2e8f0;
}
.circular-progress-box {
  position: relative;
  width: 85px;
  height: 85px;
}
.circle-bg { fill: none; stroke: #f1f5f9; stroke-width: 3; }
.circle { fill: none; stroke: #10b981; stroke-width: 3; stroke-linecap: round; }
.percentage-inner-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.pct-num { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
.progress-sub-label {
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.04em;
}

/* ─── TABS ─── */
.premium-tabs-menu {
  display: flex;
  gap: 2rem;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 2rem;
}
.tab-item {
  background: transparent;
  border: none;
  padding: 1rem 0.25rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #94a3b8;
  cursor: pointer;
  position: relative;
  transition: color 0.2s;
}
.tab-item:hover { color: #1e293b; }
.tab-item.active { color: #0f172a; }
.tab-item.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #0f172a;
}

/* ─── SECTION CARD ─── */
.premium-section-card {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px -8px rgba(0,0,0,0.02);
  margin-bottom: 2rem;
}
.section-card-header h3 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 1.5rem 0;
}

/* ─── APERÇU ─── */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.5rem 2rem;
}
.info-item.full-width { grid-column: 1 / -1; }
.info-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.info-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin-top: 0.15rem;
}

/* ─── PHASES ─── */
.premium-timeline-wrapper {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}
.timeline-row-item { display: block; }
.timeline-left-node { display: none; }

.timeline-row-main-content {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 1.5rem;
  display: flex !important;
  flex-direction: column;
  gap: 1rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.timeline-row-main-content:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px -12px rgba(15,23,42,0.08);
  border-color: #cbd5e1;
}

.phase-identity-cell {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px dashed #f1f5f9;
  padding-bottom: 0.75rem;
}
.phase-title-text {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
}
.phase-dates-subtext {
  font-size: 0.7rem;
  color: #64748b;
  background: #f8fafc;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}

.phase-cell {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid #f1f5f9;
}
.phase-cell .lbl {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
}
.phase-cell .val {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
}

.phase-progress-cell {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.progress-pct-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #0f172a;
  align-self: flex-end;
}
.horizontal-progress-track {
  height: 5px;
  background: #f1f5f9;
  border-radius: 99px;
}
.horizontal-progress-fill.fill-en_cours { background: #0284c7; }
.horizontal-progress-fill.fill-terminee { background: #10b981; }
.horizontal-progress-fill.fill-bloquee { background: #e11d48; }

.phase-status-cell {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.25rem;
}
.pill-status-static {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}
.badge-validation {
  font-size: 0.65rem;
  font-weight: 700;
  color: #15803d;
  background: #dcfce7;
  padding: 0.15rem 0.6rem;
  border-radius: 6px;
}

.phase-actions-cell {
  display: flex;
  gap: 0.4rem;
  opacity: 0.5;
  transition: opacity 0.2s;
}
.timeline-row-main-content:hover .phase-actions-cell { opacity: 1; }
.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: white;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.action-btn:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
}
.action-btn.delete:hover {
  background: #fff1f2;
  border-color: #fecaca;
  color: #e11d48;
}

/* ─── DÉPENSES ─── */
.financial-simple-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}
.financial-box {
  background: #fafafa;
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  border: 1px solid #f1f5f9;
}
.financial-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  display: block;
  margin-bottom: 0.25rem;
}
.financial-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
}
.financial-value.primary { color: #0284c7; }
.financial-value.success { color: #10b981; }
.mini-progress-bar {
  width: 100%;
  height: 6px;
  background: #f1f5f9;
  border-radius: 99px;
  margin-top: 0.75rem;
  overflow: hidden;
}
.mini-progress-fill {
  height: 100%;
  background: #0284c7;
  border-radius: 99px;
}

.expense-form .form-row {
  display: grid;
  grid-template-columns: 2fr 1.2fr 1.2fr auto;
  gap: 1rem;
  align-items: end;
}
.expense-form .form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}
.expense-form .form-group label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #475569;
}
.form-input {
  padding: 0.6rem 1rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  font-size: 0.9rem;
  background: white;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input:focus {
  outline: none;
  border-color: #0f172a;
  box-shadow: 0 0 0 4px rgba(15,23,42,0.04);
}
.btn-primary {
  background: #0f172a;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 0.6rem 1.5rem;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-primary:hover {
  background: #1e293b;
  transform: translateY(-1px);
}
.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  display: inline-block;
}

.expense-table-wrapper { overflow-x: auto; }
.expense-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}
.expense-table th {
  text-align: left;
  padding: 0.75rem 0.5rem;
  border-bottom: 2px solid #e2e8f0;
  color: #64748b;
  font-weight: 700;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.expense-table td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid #f1f5f9;
}
.expense-table tfoot td {
  border-top: 2px solid #e2e8f0;
  font-weight: 700;
}
.expense-table .action-btn.delete {
  background: rgba(239,68,68,0.06);
  border: none;
  border-radius: 8px;
  padding: 0.2rem;
  cursor: pointer;
}
.expense-table .action-btn.delete:hover { background: rgba(239,68,68,0.12); }

/* ─── MODALS ─── */
.premium-blur-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.2);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}
.premium-delete-modal,
.premium-modal {
  background: white;
  border-radius: 24px;
  max-width: 480px;
  width: 100%;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 30px 60px -20px rgba(0,0,0,0.1);
}
.premium-modal { max-width: 720px; }
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 1rem;
  margin-bottom: 1.5rem;
}
.modal-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #94a3b8;
}
.modal-body { margin-bottom: 1.5rem; }
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  border-top: 1px solid #f1f5f9;
  padding-top: 1.5rem;
}

.btn-premium-cancel {
  background: transparent;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 1.25rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}
.btn-premium-cancel:hover { background: #f1f5f9; }
.btn-premium-danger-confirm {
  background: #e11d48;
  color: white;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}
.btn-premium-danger-confirm:hover { background: #be123c; }

.modal-alert-graphic {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #fff7ed;
  color: #ea580c;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  margin: 0 auto 1rem;
}

/* Modal Phase */
.phase-modal .form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
.phase-modal .form-grid .full-width { grid-column: 1 / -1; }
.phase-modal .form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}
.phase-modal .form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}
.phase-modal .form-control {
  padding: 0.6rem 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9rem;
}
.phase-modal .form-control:focus {
  outline: none;
  border-color: #0f172a;
  box-shadow: 0 0 0 4px rgba(15,23,42,0.04);
}

/* ─── RESPONSIVE ─── */
@media (max-width: 1024px) {
  .project-premium-container { padding: 1.5rem; }
  .premium-hero-card { flex-direction: column; align-items: stretch; }
  .hero-right-progress {
    border-left: none;
    padding-left: 0;
    margin-top: 1.5rem;
    flex-direction: row;
    gap: 2rem;
    align-items: center;
    justify-content: center;
  }
  .project-meta-features-row { gap: 1.5rem; }
}
@media (max-width: 768px) {
  .premium-header-title-row { flex-direction: column; align-items: stretch; }
  .premium-actions-group { justify-content: flex-start; }
  .premium-tabs-menu { gap: 0.5rem; }
  .tab-item { padding: 0.6rem 0.25rem; font-size: 0.85rem; }
  .timeline-row-main-content { grid-template-columns: 1fr !important; }
  .expense-form .form-row { grid-template-columns: 1fr; }
  .phase-modal .form-grid { grid-template-columns: 1fr; }
  .phase-modal .form-grid .full-width { grid-column: 1; }
  .premium-hero-card { padding: 1.5rem; }
  .info-grid { grid-template-columns: 1fr; }
}
</style>