<template>
  <div class="page">
    <div class="page-header">
      <h1>Bienvenue, {{ auth.user?.prenom }} 👋</h1>
      <p class="subtitle">Voici un aperçu de vos chantiers et projets.</p>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <template v-else>
      <!-- KPI cards -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/></svg></div>
          <div>
            <p class="kpi-value">{{ data.kpis.chantiers }}</p>
            <p class="kpi-label">Chantiers assignés</p>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon indigo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg></div>
          <div>
            <p class="kpi-value">{{ data.kpis.projets }}</p>
            <p class="kpi-label">Projets ({{ data.kpis.en_cours }} en cours)</p>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
          <div>
            <p class="kpi-value">{{ data.kpis.progression }}%</p>
            <p class="kpi-label">Progression moyenne</p>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" :class="data.kpis.en_retard > 0 ? 'red' : 'amber'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
          </div>
          <div>
            <p class="kpi-value">{{ data.kpis.en_retard }}</p>
            <p class="kpi-label">Projets en retard</p>
          </div>
        </div>
      </div>

      <div class="budget-row">
        <div class="budget-card">
          <span class="budget-label">Budget cumulé</span>
          <span class="budget-value">{{ formatMoney(data.kpis.budget_total) }}</span>
        </div>
        <div class="budget-card">
          <span class="budget-label">Coût réel cumulé</span>
          <span class="budget-value">{{ formatMoney(data.kpis.cout_reel) }}</span>
        </div>
      </div>

      <div class="two-col">
        <!-- Chantiers récents -->
        <div class="card">
          <div class="card-header">
            <h2>Chantiers récents</h2>
            <RouterLink to="/responsable/chantiers" class="link">Voir tout</RouterLink>
          </div>
          <div v-if="data.chantiers_recents.length === 0" class="empty">Aucun chantier assigné.</div>
          <ul v-else class="list">
            <li v-for="c in data.chantiers_recents" :key="c.id" class="list-item" @click="$router.push(`/responsable/chantiers/${c.id}`)">
              <div>
                <p class="item-title">{{ c.nom }}</p>
                <p class="item-sub">{{ c.ville }} · {{ c.statut_label }}</p>
              </div>
              <div class="progress-mini">
                <div class="progress-bar"><div class="progress-fill" :style="{ width: c.progression + '%' }" /></div>
                <span>{{ c.progression }}%</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Prochaines phases -->
        <div class="card">
          <div class="card-header">
            <h2>Prochaines phases</h2>
            <RouterLink to="/responsable/planning" class="link">Voir le planning</RouterLink>
          </div>
          <div v-if="data.prochaines_phases.length === 0" class="empty">Aucune phase à venir.</div>
          <ul v-else class="list">
            <li v-for="ph in data.prochaines_phases" :key="ph.id" class="list-item" @click="$router.push(`/responsable/projets/${ph.projet_id}`)">
              <div>
                <p class="item-title">{{ ph.nom }}</p>
                <p class="item-sub">{{ ph.projet }} · {{ ph.date_debut }}</p>
              </div>
              <span class="badge" :class="'st-' + ph.statut">{{ statutLabel(ph.statut) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import responsableService from '@/services/responsableService'

const auth = useAuthStore()
const loading = ref(true)
const data = ref({
  kpis: { chantiers: 0, projets: 0, en_cours: 0, termines: 0, bloques: 0, en_retard: 0, progression: 0, budget_total: 0, cout_reel: 0 },
  chantiers_recents: [],
  prochaines_phases: [],
})

function formatMoney(v) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD', maximumFractionDigits: 0 }).format(v || 0)
}

function statutLabel(s) {
  return { non_commencee: 'À venir', en_cours: 'En cours', terminee: 'Terminée', bloquee: 'Bloquée' }[s] ?? s
}

onMounted(async () => {
  try {
    const res = await responsableService.getDashboard()
    data.value = res.data.data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0a2540; margin: 0 0 0.25rem; }
.subtitle { color: #639fab; margin: 0 0 1.5rem; font-size: 0.9rem; }
.loading, .empty { padding: 2rem; text-align: center; color: #639fab; }

.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.kpi-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
.kpi-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icon svg { width: 22px; height: 22px; }
.kpi-icon.blue { background: #e0f2fe; color: #0284c7; }
.kpi-icon.indigo { background: #e0e7ff; color: #4f46e5; }
.kpi-icon.green { background: #dcfce7; color: #16a34a; }
.kpi-icon.amber { background: #fef3c7; color: #d97706; }
.kpi-icon.red { background: #fee2e2; color: #dc2626; }
.kpi-value { font-size: 1.4rem; font-weight: 700; color: #0a2540; margin: 0; }
.kpi-label { font-size: 0.8rem; color: #639fab; margin: 0; }

.budget-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.budget-card { background: #f4f9fd; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; }
.budget-label { font-size: 0.85rem; color: #639fab; }
.budget-value { font-size: 1.1rem; font-weight: 700; color: #0a2540; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

.card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; overflow: hidden; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #e0f2fe; }
.card-header h2 { font-size: 1rem; font-weight: 600; color: #0a2540; margin: 0; }
.link { font-size: 0.82rem; color: #0284c7; text-decoration: none; font-weight: 500; }
.link:hover { text-decoration: underline; }

.list { list-style: none; margin: 0; padding: 0; }
.list-item { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1.25rem; border-bottom: 1px solid #f0f9ff; cursor: pointer; transition: background 0.15s; }
.list-item:last-child { border-bottom: none; }
.list-item:hover { background: #f4f9fd; }
.item-title { font-size: 0.9rem; font-weight: 600; color: #0a2540; margin: 0; }
.item-sub { font-size: 0.78rem; color: #639fab; margin: 0.15rem 0 0; }

.progress-mini { display: flex; align-items: center; gap: 0.5rem; min-width: 90px; }
.progress-bar { width: 60px; height: 6px; background: #e0f2fe; border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; background: #0284c7; border-radius: 3px; }
.progress-mini span { font-size: 0.78rem; color: #639fab; font-weight: 600; }

.badge { font-size: 0.72rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 999px; white-space: nowrap; }
.st-non_commencee { background: #f1f5f9; color: #64748b; }
.st-en_cours { background: #e0f2fe; color: #0284c7; }
.st-terminee { background: #dcfce7; color: #16a34a; }
.st-bloquee { background: #fee2e2; color: #dc2626; }
</style>