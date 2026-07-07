<template>
  <div class="page">
    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else-if="projets.length === 0" class="empty-state">
      <p>Vous n'avez aucun projet assigné pour le moment.</p>
      <RouterLink to="/responsable/chantiers" class="link">Consulter mes chantiers</RouterLink>
    </div>

    <div v-else class="grid">
      <div v-for="p in projets" :key="p.id" class="projet-card" @click="$router.push(`/responsable/projets/${p.id}`)">
        <div class="card-top">
          <span class="reference">{{ p.reference }}</span>
          <span class="badge" :class="'st-' + p.statut">{{ p.statut_label }}</span>
        </div>
        <h3>{{ p.nom }}</h3>
        <p class="meta">{{ p.categorie }}</p>
        <p class="meta" v-if="p.chantier">🏗️ {{ p.chantier.nom }} · {{ p.chantier.ville }}</p>

        <div class="progress-row">
          <div class="progress-bar"><div class="progress-fill" :style="{ width: p.progression + '%' }" /></div>
          <span>{{ p.progression }}%</span>
        </div>

        <div class="card-footer">
          <span>{{ p.nb_phases }} phase(s)</span>
          <span>{{ formatMoney(p.budget) }}</span>
        </div>

        <RouterLink :to="`/responsable/projets/${p.id}/consommation`" class="consommation-link" @click.stop>
          📊 Consommation
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import responsableService from '@/services/responsableService'

const projets = ref([])
const loading = ref(true)

function formatMoney(v) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD', maximumFractionDigits: 0 }).format(v || 0)
}

onMounted(async () => {
  try {
    const res = await responsableService.getProjets()
    projets.value = res.data.data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.loading, .empty-state { padding: 3rem; text-align: center; color: #639fab; background: #fff; border-radius: 12px; border: 1px solid #e0f2fe; }
.link { color: #0284c7; font-weight: 600; text-decoration: none; display: inline-block; margin-top: 0.5rem; }
.link:hover { text-decoration: underline; }

.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
.projet-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.1rem; cursor: pointer; transition: all 0.15s; position: relative; }
.projet-card:hover { box-shadow: 0 4px 16px rgba(2, 132, 199, 0.12); border-color: #0284c7; transform: translateY(-2px); }
.card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
.reference { font-size: 0.75rem; color: #639fab; font-weight: 600; }
h3 { font-size: 1rem; font-weight: 700; color: #0a2540; margin: 0 0 0.3rem; }
.meta { font-size: 0.82rem; color: #639fab; margin: 0.1rem 0; }

.progress-row { display: flex; align-items: center; gap: 0.6rem; margin: 0.75rem 0; }
.progress-bar { flex: 1; height: 6px; background: #e0f2fe; border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; background: #0284c7; border-radius: 3px; }
.progress-row span { font-size: 0.78rem; color: #639fab; font-weight: 600; }
.card-footer { display: flex; justify-content: space-between; font-size: 0.78rem; color: #0a2540; font-weight: 600; padding-top: 0.6rem; border-top: 1px solid #f0f9ff; }

.consommation-link { display: block; margin-top: 0.75rem; text-align: center; font-size: 0.8rem; font-weight: 600; color: #0284c7; text-decoration: none; background: #f4f9fd; border-radius: 8px; padding: 0.45rem; }
.consommation-link:hover { background: #e0f2fe; }

.badge { font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 999px; white-space: nowrap; }
.st-non_commence { background: #f1f5f9; color: #64748b; }
.st-en_cours { background: #e0f2fe; color: #0284c7; }
.st-termine { background: #dcfce7; color: #16a34a; }
.st-bloque { background: #fee2e2; color: #dc2626; }
</style>