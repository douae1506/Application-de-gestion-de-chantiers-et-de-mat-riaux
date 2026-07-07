<template>
  <div class="page">
    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else-if="groupes.length === 0" class="empty-state">
      <p>Aucune phase planifiée pour l'instant. Créez un projet et planifiez ses phases pour voir apparaître le planning ici.</p>
    </div>

    <div v-else class="planning">
      <div v-for="g in groupes" :key="g.projet_id" class="projet-block">
        <div class="projet-block-header" @click="$router.push(`/responsable/projets/${g.projet_id}`)">
          <h2>{{ g.projet_nom }}</h2>
          <span class="meta">🏗️ {{ g.chantier_nom }}</span>
        </div>

        <div class="timeline">
          <div v-for="ph in g.phases" :key="ph.id" class="timeline-row">
            <div class="timeline-info">
              <span class="phase-nom">{{ ph.nom }}</span>
              <span class="phase-dates">{{ ph.date_debut }} → {{ ph.date_fin_prevue || '—' }}</span>
            </div>
            <div class="timeline-bar-wrap">
              <div class="timeline-bar" :class="'bar-' + ph.statut" :style="{ width: ph.progression + '%' }"></div>
            </div>
            <span class="badge" :class="'st-' + ph.statut">{{ statutLabel(ph.statut) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import responsableService from '@/services/responsableService'

const groupes = ref([])
const loading = ref(true)

function statutLabel(s) {
  return { non_commencee: 'À venir', en_cours: 'En cours', terminee: 'Terminée', bloquee: 'Bloquée' }[s] ?? s
}

onMounted(async () => {
  try {
    const res = await responsableService.getPlanning()
    groupes.value = res.data.data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.loading, .empty-state { padding: 3rem; text-align: center; color: #639fab; background: #fff; border-radius: 12px; border: 1px solid #e0f2fe; }

.planning { display: flex; flex-direction: column; gap: 1.5rem; }
.projet-block { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; overflow: hidden; }
.projet-block-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e0f2fe; background: #f4f9fd; cursor: pointer; display: flex; align-items: baseline; gap: 1rem; flex-wrap: wrap; }
.projet-block-header h2 { font-size: 1rem; font-weight: 700; color: #0a2540; margin: 0; }
.projet-block-header .meta { font-size: 0.8rem; color: #639fab; }
.projet-block-header:hover h2 { color: #0284c7; }

.timeline { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.9rem; }
.timeline-row { display: grid; grid-template-columns: 180px 1fr 90px; align-items: center; gap: 1rem; }
.timeline-info { display: flex; flex-direction: column; }
.phase-nom { font-size: 0.85rem; font-weight: 600; color: #0a2540; }
.phase-dates { font-size: 0.72rem; color: #639fab; }

.timeline-bar-wrap { height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; }
.timeline-bar { height: 100%; border-radius: 5px; transition: width 0.2s; }
.bar-non_commencee { background: #cbd5e1; }
.bar-en_cours { background: #0284c7; }
.bar-terminee { background: #16a34a; }
.bar-bloquee { background: #dc2626; }

.badge { font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 999px; white-space: nowrap; text-align: center; justify-self: end; }
.st-non_commencee { background: #f1f5f9; color: #64748b; }
.st-en_cours { background: #e0f2fe; color: #0284c7; }
.st-terminee { background: #dcfce7; color: #16a34a; }
.st-bloquee { background: #fee2e2; color: #dc2626; }

@media (max-width: 700px) {
  .timeline-row { grid-template-columns: 1fr; gap: 0.4rem; }
  .badge { justify-self: start; }
}
</style>