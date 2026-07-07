<template>
  <div class="page" v-if="!loading && projet">
    <button class="back-btn" @click="$router.push(`/responsable/chantiers/${projet.chantier.id}`)">← Retour au chantier</button>

    <div class="header-card">
      <div>
        <span class="reference">{{ projet.reference }}</span>
        <h1>{{ projet.nom }}</h1>
        <p class="meta">{{ projet.categorie }} · Chantier : {{ projet.chantier?.nom }}</p>
      </div>
      <div class="header-right">
        <span class="badge lg" :class="'st-' + projet.statut">{{ projet.statut_label }}</span>
        <div class="progress-row">
          <div class="progress-bar"><div class="progress-fill" :style="{ width: projet.progression + '%' }" /></div>
          <span>{{ projet.progression }}%</span>
        </div>
        <RouterLink :to="`/responsable/projets/${projet.id}/consommation`" class="btn-outline">📊 Voir la consommation</RouterLink>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat"><span class="stat-label">Budget</span><span class="stat-value">{{ formatMoney(projet.budget) }}</span></div>
      <div class="stat"><span class="stat-label">Coût réel</span><span class="stat-value">{{ formatMoney(projet.cout_reel) }}</span></div>
      <div class="stat"><span class="stat-label">Début</span><span class="stat-value">{{ projet.date_debut }}</span></div>
      <div class="stat"><span class="stat-label">Fin prévue</span><span class="stat-value">{{ projet.date_fin_prevue || '—' }}</span></div>
    </div>

    <!-- Planifier des phases -->
    <div class="section-header">
      <h2>Phases du projet</h2>
      <button class="btn-primary" @click="showPhaseModal = true">+ Planifier une phase</button>
    </div>

    <div v-if="projet.phases.length === 0" class="empty-state">
      <p>Aucune phase planifiée pour ce projet. Commencez par en ajouter une.</p>
    </div>

    <div v-else class="phases-list">
      <div v-for="ph in sortedPhases" :key="ph.id" class="phase-card">
        <div class="phase-top">
          <div>
            <span class="phase-ordre">Étape {{ ph.ordre }}</span>
            <h3>{{ ph.nom }}</h3>
          </div>
          <span class="badge" :class="'st-' + ph.statut">{{ statutLabel(ph.statut) }}</span>
        </div>
        <p class="meta" v-if="ph.description">{{ ph.description }}</p>
        <p class="meta">📅 {{ ph.date_debut }} → {{ ph.date_fin_prevue || '—' }}<span v-if="ph.date_fin_reelle"> · terminée le {{ ph.date_fin_reelle }}</span></p>

        <!-- Mettre à jour l'avancement -->
        <div class="avancement-row">
          <input
            type="range" min="0" max="100" step="5"
            v-model.number="ph.progression"
            @change="updateAvancement(ph)"
          />
          <span class="avancement-value">{{ ph.progression }}%</span>
          <button class="btn-icon" title="Modifier / supprimer la phase" @click="openEditPhase(ph)">✏️</button>
        </div>
      </div>
    </div>

    <!-- Modal Planifier une phase -->
    <div v-if="showPhaseModal" class="modal-overlay" @click.self="closePhaseModal">
      <div class="modal">
        <h2>{{ editingPhase ? 'Modifier la phase' : 'Planifier une nouvelle phase' }}</h2>
        <form @submit.prevent="submitPhase">
          <label>Nom de la phase *</label>
          <input v-model="phaseForm.nom" required maxlength="255" placeholder="Ex: Fondations, Toiture..." />

          <label>Description</label>
          <textarea v-model="phaseForm.description" rows="2"></textarea>

          <div class="form-row">
            <div>
              <label>Ordre</label>
              <input v-model.number="phaseForm.ordre" type="number" min="1" />
            </div>
            <div>
              <label>Responsable terrain</label>
              <input v-model="phaseForm.responsable" maxlength="255" />
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Date de début *</label>
              <input v-model="phaseForm.date_debut" type="date" required />
            </div>
            <div>
              <label>Date de fin prévue</label>
              <input v-model="phaseForm.date_fin_prevue" type="date" />
            </div>
          </div>

          <label>Observations</label>
          <textarea v-model="phaseForm.observations" rows="2"></textarea>

          <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

          <div class="modal-actions">
            <button v-if="editingPhase" type="button" class="btn-danger" @click="deletePhase">Supprimer</button>
            <div style="flex:1"></div>
            <button type="button" class="btn-secondary" @click="closePhaseModal">Annuler</button>
            <button type="submit" class="btn-primary" :disabled="submitting">
              {{ submitting ? 'Enregistrement...' : (editingPhase ? 'Enregistrer' : 'Planifier') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="loading">Chargement...</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import responsableService from '@/services/responsableService'
import { useToast } from '@/composables/useToast'

const route  = useRoute()
const router = useRouter()
const toast  = useToast()

const loading   = ref(true)
const projet    = ref(null)
const showPhaseModal = ref(false)
const editingPhase   = ref(null)
const submitting = ref(false)
const errorMsg   = ref('')

const sortedPhases = computed(() => [...(projet.value?.phases ?? [])].sort((a, b) => (a.ordre ?? 0) - (b.ordre ?? 0)))

const phaseForm = reactive({
  nom: '', description: '', ordre: null, responsable: '',
  date_debut: '', date_fin_prevue: '', observations: '',
})

function formatMoney(v) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD', maximumFractionDigits: 0 }).format(v || 0)
}
function statutLabel(s) {
  return { non_commencee: 'Non commencée', en_cours: 'En cours', terminee: 'Terminée', bloquee: 'Bloquée' }[s] ?? s
}

async function fetchProjet() {
  loading.value = true
  try {
    const res = await responsableService.getProjet(route.params.id)
    projet.value = res.data.data
  } catch (e) {
    toast.error("Impossible de charger ce projet.")
    router.push('/responsable/projets')
  } finally {
    loading.value = false
  }
}

function resetPhaseForm() {
  Object.assign(phaseForm, {
    nom: '', description: '', ordre: (projet.value?.phases.length ?? 0) + 1, responsable: '',
    date_debut: '', date_fin_prevue: '', observations: '',
  })
}

function openEditPhase(ph) {
  editingPhase.value = ph
  Object.assign(phaseForm, {
    nom: ph.nom, description: ph.description, ordre: ph.ordre, responsable: ph.responsable,
    date_debut: ph.date_debut, date_fin_prevue: ph.date_fin_prevue, observations: ph.observations,
  })
  showPhaseModal.value = true
}

function closePhaseModal() {
  showPhaseModal.value = false
  editingPhase.value = null
  errorMsg.value = ''
  resetPhaseForm()
}

async function submitPhase() {
  submitting.value = true
  errorMsg.value = ''
  try {
    if (editingPhase.value) {
      await responsableService.updatePhase(editingPhase.value.id, phaseForm)
      toast.success('Phase mise à jour.')
    } else {
      await responsableService.createPhase(projet.value.id, phaseForm)
      toast.success('Phase planifiée avec succès.')
    }
    closePhaseModal()
    await fetchProjet()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Une erreur est survenue.'
  } finally {
    submitting.value = false
  }
}

async function deletePhase() {
  if (!confirm('Supprimer définitivement cette phase ?')) return
  try {
    await responsableService.deletePhase(editingPhase.value.id)
    toast.success('Phase supprimée.')
    closePhaseModal()
    await fetchProjet()
  } catch (e) {
    toast.error("Impossible de supprimer cette phase.")
  }
}

// Mettre à jour l'avancement
async function updateAvancement(ph) {
  try {
    const res = await responsableService.updateAvancement(ph.id, { progression: ph.progression })
    toast.success(`Avancement de « ${ph.nom} » mis à jour (${ph.progression}%).`)
    // Rafraîchit la progression globale du projet renvoyée par l'API
    projet.value.progression = res.data.data.projet.progression
    projet.value.statut = res.data.data.projet.statut
    projet.value.statut_label = res.data.data.projet.statut_label
    ph.statut = res.data.data.phase.statut
    ph.date_fin_reelle = res.data.data.phase.date_fin_reelle
  } catch (e) {
    toast.error("Impossible de mettre à jour l'avancement.")
    fetchProjet()
  }
}

onMounted(async () => {
  await fetchProjet()
  resetPhaseForm()
})
</script>

<style scoped>
.back-btn { background: none; border: none; color: #0284c7; font-size: 0.85rem; cursor: pointer; margin-bottom: 1rem; padding: 0; font-weight: 500; }
.back-btn:hover { text-decoration: underline; }

.header-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.5rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; }
.reference { font-size: 0.75rem; color: #639fab; font-weight: 600; }
h1 { font-size: 1.4rem; font-weight: 700; color: #0a2540; margin: 0.25rem 0; }
.meta { font-size: 0.85rem; color: #639fab; margin: 0.15rem 0; }
.header-right { display: flex; flex-direction: column; align-items: flex-end; gap: 0.6rem; }

.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat { background: #f4f9fd; border: 1px solid #e0f2fe; border-radius: 10px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.25rem; }
.stat-label { font-size: 0.75rem; color: #639fab; }
.stat-value { font-size: 1rem; font-weight: 700; color: #0a2540; }

.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.section-header h2 { font-size: 1.05rem; font-weight: 700; color: #0a2540; margin: 0; }

.phases-list { display: flex; flex-direction: column; gap: 1rem; }
.phase-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.1rem 1.25rem; }
.phase-top { display: flex; justify-content: space-between; align-items: flex-start; }
.phase-ordre { font-size: 0.72rem; color: #639fab; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
h3 { font-size: 1rem; font-weight: 700; color: #0a2540; margin: 0.2rem 0 0.35rem; }

.avancement-row { display: flex; align-items: center; gap: 0.85rem; margin-top: 0.85rem; }
.avancement-row input[type="range"] { flex: 1; accent-color: #0284c7; }
.avancement-value { font-size: 0.85rem; font-weight: 700; color: #0284c7; min-width: 42px; text-align: right; }
.btn-icon { background: #f4f9fd; border: 1px solid #e0f2fe; border-radius: 8px; width: 32px; height: 32px; cursor: pointer; font-size: 0.9rem; }
.btn-icon:hover { background: #e0f2fe; }

.empty-state, .loading { padding: 3rem; text-align: center; color: #639fab; background: #fff; border-radius: 12px; border: 1px solid #e0f2fe; }

.badge { font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 999px; white-space: nowrap; }
.badge.lg { font-size: 0.8rem; padding: 0.35rem 0.9rem; }
.st-non_commencee, .st-non_commence { background: #f1f5f9; color: #64748b; }
.st-en_cours { background: #e0f2fe; color: #0284c7; }
.st-terminee, .st-termine { background: #dcfce7; color: #16a34a; }
.st-bloquee, .st-bloque { background: #fee2e2; color: #dc2626; }

.btn-primary { background: #0284c7; color: #fff; border: none; padding: 0.6rem 1.1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-primary:hover { background: #0369a1; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #f1f5f9; color: #0a2540; border: none; padding: 0.6rem 1.1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-danger { background: #fee2e2; color: #dc2626; border: none; padding: 0.6rem 1.1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.btn-danger:hover { background: #fecaca; }
.btn-outline { border: 1px solid #0284c7; color: #0284c7; background: #fff; padding: 0.5rem 0.9rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
.btn-outline:hover { background: #e0f2fe; }

.modal-overlay { position: fixed; inset: 0; background: rgba(10, 37, 64, 0.4); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
.modal { background: #fff; border-radius: 14px; padding: 1.75rem; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; }
.modal h2 { font-size: 1.15rem; font-weight: 700; color: #0a2540; margin: 0 0 1.25rem; }
.modal label { display: block; font-size: 0.82rem; font-weight: 600; color: #0a2540; margin: 0.85rem 0 0.35rem; }
.modal input, .modal textarea, .modal select {
  width: 100%; border: 1px solid #e0f2fe; border-radius: 8px; padding: 0.55rem 0.75rem;
  font-size: 0.88rem; color: #0a2540; box-sizing: border-box; font-family: inherit;
}
.modal input:focus, .modal textarea:focus, .modal select:focus { outline: none; border-color: #0284c7; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
.error-msg { color: #dc2626; font-size: 0.82rem; margin-top: 0.75rem; }
</style>