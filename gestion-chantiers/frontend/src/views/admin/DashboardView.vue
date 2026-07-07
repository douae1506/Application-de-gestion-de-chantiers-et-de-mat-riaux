<template>
  <div class="dashboard-page">

    <header class="page-header">
      <div class="header-titles">
        <h1>Tableau de bord</h1>
        <p>Vue d'ensemble de vos projets et activités</p>
      </div>
      <div class="header-actions">
        <div class="date-picker-mock">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-calendar">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <span>{{ dateRange }}</span>
          <span class="arrow-down">▼</span>
        </div>
      </div>
    </header>

    <!-- État de chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement des métriques...</span>
    </div>

    <template v-else>
      <!-- ─── KPI CARDS ─── -->
      <section class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-header-row">
            <div class="kpi-badge chantiers">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M3 21h18M3 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M13 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 7h18M4 7v14M20 7v14M9 12h2v3H9zM13 12h2v3h-2z"/>
              </svg>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Chantiers</span>
              <h2 class="kpi-value">{{ stats.kpis.chantiers.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive">↗ <span>{{ stats.kpis.chantiers.new }} nouveaux</span></span>
        </div>

        <div class="kpi-card">
          <div class="kpi-header-row">
            <div class="kpi-badge projets">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Projets</span>
              <h2 class="kpi-value">{{ stats.kpis.projets.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive">↗ <span>{{ stats.kpis.projets.new }} nouveaux</span></span>
        </div>

        <div class="kpi-card">
          <div class="kpi-header-row">
            <div class="kpi-badge budget">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>
              </svg>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Budget total</span>
              <h2 class="kpi-value">{{ formatMAD(stats.kpis.budget.total) }}</h2>
            </div>
          </div>
          <span class="kpi-trend" :class="stats.kpis.budget.evolution >= 0 ? 'positive' : 'negative'">
            {{ stats.kpis.budget.evolution >= 0 ? '↗' : '↘' }} <span>{{ Math.abs(stats.kpis.budget.evolution) }}% vs mois dernier</span>
          </span>
        </div>

        <div class="kpi-card">
          <div class="kpi-header-row">
            <div class="kpi-badge depenses">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Dépenses</span>
              <h2 class="kpi-value">{{ formatMAD(stats.kpis.depenses.total) }}</h2>
            </div>
          </div>
          <span class="kpi-trend" :class="stats.kpis.depenses.evolution >= 0 ? 'positive' : 'negative'">
            {{ stats.kpis.depenses.evolution >= 0 ? '↗' : '↘' }} <span>{{ Math.abs(stats.kpis.depenses.evolution) }}% vs mois dernier</span>
          </span>
        </div>

        <div class="kpi-card">
          <div class="kpi-header-row">
            <div class="kpi-badge employes">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Employés</span>
              <h2 class="kpi-value">{{ stats.kpis.employes.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive">↗ <span>{{ stats.kpis.employes.new }} nouveaux</span></span>
        </div>
      </section>

      <!-- ─── GRAPHIQUES DU HAUT ─── -->
      <section class="charts-upper-grid">
        <!-- Camembert Répartition -->
        <div class="v-card">
          <div class="card-header">
            <h3>Répartition des chantiers par statut</h3>
          </div>
          <div class="card-body">
            <div class="pie-wrapper">
              <div class="pie-svg-container">
                <svg viewBox="0 0 120 120" class="pie-svg">
                  <circle cx="60" cy="60" r="45" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                  <circle
                    v-for="item in chartStats"
                    :key="item.label"
                    cx="60" cy="60" r="45"
                    fill="none"
                    :stroke="item.color"
                    stroke-width="14"
                    :stroke-dasharray="item.dasharray"
                    :stroke-dashoffset="-item.offset"
                    stroke-linecap="round"
                    class="pie-segment"
                  />
                </svg>
                <div class="pie-center-text">
                  <span class="center-qty">{{ stats.kpis.chantiers.total }}</span>
                  <span class="center-lbl">Total</span>
                </div>
              </div>
              <div class="pie-legend">
                <div v-for="item in chartStats" :key="item.label" class="legend-row">
                  <span class="legend-indicator" :style="{ background: item.color }"></span>
                  <span class="legend-name">{{ item.label }}</span>
                  <span class="legend-count">{{ item.value }}</span>
                  <span class="legend-percentage">({{ item.percent }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Avancement global -->
        <div class="v-card">
          <div class="card-header">
            <h3>Avancement global des projets</h3>
          </div>
          <div class="card-body display-center">
            <div class="progress-wrapper">
              <div class="progress-ring-container">
                <svg viewBox="0 0 120 120" class="ring-svg">
                  <circle cx="60" cy="60" r="48" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                  <circle cx="60" cy="60" r="48" fill="none" stroke="#2563eb" stroke-width="10"
                    stroke-dasharray="0 301.5"
                    :stroke-dasharray="`${stats.progression.globale * 3.015} 301.5`"
                    stroke-linecap="round"
                    class="ring-progress-bar"
                  />
                </svg>
                <div class="ring-content">
                  <span class="ring-number">{{ stats.progression.globale }}%</span>
                  <span class="ring-text">Avancement global</span>
                </div>
              </div>
              <div class="progress-metrics-list">
                <div class="metric-row-item">
                  <span class="label">Projets en cours</span>
                  <strong class="val">{{ stats.progression.en_cours }}</strong>
                </div>
                <div class="metric-row-item">
                  <span class="label">Projets terminés</span>
                  <strong class="val">{{ stats.progression.termines }}</strong>
                </div>
                <div class="metric-row-item danger-text">
                  <span class="label">En retard</span>
                  <strong class="val">{{ stats.progression.retard }}</strong>
                </div>
                <div class="metric-row-item">
                  <span class="label">À venir</span>
                  <strong class="val">{{ stats.progression.avenir }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget vs Dépenses -->
        <div class="v-card">
          <div class="card-header flex-between">
            <h3>Budget vs Dépenses</h3>
            <div class="chart-labels-guide">
              <span class="guide-item"><span class="guide-dot budget"></span> Budget</span>
              <span class="guide-item"><span class="guide-dot depenses"></span> Dépenses</span>
            </div>
          </div>
          <div class="card-body bar-chart-body">
            <div class="y-axis-graduation">
              <span>{{ formatMAD(maxBudgetValue) }}</span>
              <span>{{ formatMAD(maxBudgetValue * 0.75) }}</span>
              <span>{{ formatMAD(maxBudgetValue * 0.5) }}</span>
              <span>{{ formatMAD(maxBudgetValue * 0.25) }}</span>
              <span>0</span>
            </div>
            <div class="bar-chart-container">
              <div v-for="(item, index) in stats.budget_vs_depenses" :key="index" class="bar-group-wrapper">
                <div class="bar-columns">
                  <div class="bar-track">
                    <div class="bar-fill budget" :style="{ height: barHeight(item.budget) + '%' }"></div>
                  </div>
                  <div class="bar-track">
                    <div class="bar-fill depenses" :style="{ height: barHeight(item.depenses) + '%' }"></div>
                  </div>
                </div>
                <span class="bar-axis-label">{{ item.month }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ─── GRILLE DU BAS ─── -->
      <section class="split-bottom-grid">
        <!-- Chantiers récents -->
        <div class="v-card">
          <div class="card-header flex-between">
            <h3>Chantiers récents</h3>
            <router-link to="/admin/chantiers" class="see-all-link">Voir tout</router-link>
          </div>
          <div class="card-body padding-none">
            <div class="chantiers-list">
              <div v-for="c in stats.chantiers_recents" :key="c.nom" class="chantier-item-row">
                <div class="mock-image-container">🏗️</div>
                <div class="item-details">
                  <h4>{{ c.nom }}</h4>
                  <div class="meta-subtext">
                    <span>📍 {{ c.ville }}</span>
                    <span class="separator">•</span>
                    <span>📅 {{ c.date_debut }} - {{ c.date_fin_prevue }}</span>
                  </div>
                </div>
                <div class="item-status-progression">
                  <span class="percentage-txt">{{ c.progression }}%</span>
                  <span class="badge-status" :class="c.statut_class || 'en-cours'">{{ c.statut_label || 'En cours' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tâches à venir -->
        <div class="v-card">
          <div class="card-header flex-between">
            <h3>Tâches à venir</h3>
            <router-link to="/admin/evenements" class="see-all-link">Voir tout</router-link>
          </div>
          <div class="card-body padding-none">
            <div class="tasks-list">
              <div v-for="t in stats.taches_avenir" :key="t.titre" class="task-item-row">
                <div class="task-checkbox-circle" :style="{ borderColor: t.color || '#94a3b8' }"></div>
                <div class="task-main-info">
                  <h4>{{ t.titre }}</h4>
                  <span class="task-relation">{{ t.chantier_nom }}</span>
                </div>
                <div class="task-deadline" :style="{ color: t.color || '#94a3b8' }">
                  {{ t.date }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendrier -->
        <div class="v-card">
          <div class="card-header flex-between">
            <h3 class="calendar-title-bold">{{ calendarMonthName }} {{ calendarYear }}</h3>
            <div class="calendar-nav-arrows">
              <button class="arrow-btn" @click="calendarMonth--">‹</button>
              <button class="arrow-btn" @click="calendarMonth++">›</button>
            </div>
          </div>
          <div class="card-body">
            <div class="calendar-wrapper">
              <div class="calendar-weekdays-row">
                <div v-for="day in ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim']" :key="day">{{ day }}</div>
              </div>
              <div class="calendar-days-matrix">
                <div
                  v-for="day in calendarDays"
                  :key="day.date.toISOString()"
                  class="calendar-day-node"
                  :class="{ 'is-inactive-month': !day.currentMonth, 'is-selected-today': day.isToday }"
                >
                  <span>{{ day.day }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Activités récentes -->
        <div class="v-card">
          <div class="card-header">
            <h3>Activités récentes</h3>
          </div>
          <div class="card-body padding-none">
            <div class="activity-feed">
              <div v-for="act in stats.activites_recentes" :key="act.description" class="activity-feed-item">
                <div class="activity-icon-avatar" :class="act.type || 'default'">
                  <span v-if="act.type === 'new-chantier'">🏗️</span>
                  <span v-else-if="act.type === 'task-done'">✅</span>
                  <span v-else-if="act.type === 'doc'">📄</span>
                  <span v-else-if="act.type === 'payment'">💰</span>
                  <span v-else>📋</span>
                </div>
                <div class="activity-node-body">
                  <p class="activity-desc-text">
                    <strong>{{ act.description }}</strong>
                  </p>
                  <span class="activity-time-stamp">{{ act.time_ago || 'Récent' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

// ─── États ──────────────────────────────────────────────
const loading = ref(true)
const stats = ref({
  kpis: {
    chantiers: { total: 0, new: 0 },
    projets: { total: 0, new: 0 },
    budget: { total: 0, evolution: 0 },
    depenses: { total: 0, evolution: 0 },
    employes: { total: 0, new: 0 }
  },
  statut_repartition: {},
  progression: { globale: 0, en_cours: 0, termines: 0, retard: 0, avenir: 0 },
  budget_vs_depenses: [],
  chantiers_recents: [],
  taches_avenir: [],
  activites_recentes: []
})

// ─── Calendrier ──────────────────────────────────────────
const calendarMonth = ref(new Date().getMonth())
const calendarYear = ref(new Date().getFullYear())

const calendarMonthName = computed(() => {
  const date = new Date(calendarYear.value, calendarMonth.value)
  return date.toLocaleString('fr-FR', { month: 'long' }) // 'long' en minuscule ✅
})

const calendarDays = computed(() => {
  const year = calendarYear.value
  const month = calendarMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const days = []

  const firstDayOfWeek = firstDay.getDay() || 7
  const prevMonthDays = firstDayOfWeek - 1
  const prevMonthLastDay = new Date(year, month, 0).getDate()

  for (let i = prevMonthDays - 1; i >= 0; i--) {
    days.push({ day: prevMonthLastDay - i, currentMonth: false, isToday: false, date: new Date(year, month - 1, prevMonthLastDay - i) })
  }

  const today = new Date()
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const date = new Date(year, month, i)
    days.push({
      day: i,
      currentMonth: true,
      isToday: date.toDateString() === today.toDateString(),
      date
    })
  }

  const remaining = 42 - days.length
  for (let i = 1; i <= remaining; i++) {
    days.push({ day: i, currentMonth: false, isToday: false, date: new Date(year, month + 1, i) })
  }

  return days
})

// ─── Helpers ─────────────────────────────────────────────
function formatMAD(n) {
  const num = Number(n)
  if (isNaN(num)) return '0 MAD'
  if (num >= 1_000_000) return (num / 1_000_000).toFixed(2).replace('.', ',') + ' M MAD'
  if (num >= 1_000) return (num / 1_000).toFixed(1).replace('.', ',') + ' k MAD'
  return num.toFixed(0) + ' MAD'
}

// Camembert
const statutLabels = {
  planifie: 'Planifié',
  en_cours: 'En cours',
  suspendu: 'Suspendu',
  termine: 'Terminé',
  annule: 'Annulé'
}

const statutColors = {
  planifie: '#3b82f6',
  en_cours: '#10b981',
  suspendu: '#f59e0b',
  termine: '#6366f1',
  annule: '#ef4444'
}

const chartStats = computed(() => {
  const repartition = stats.value.statut_repartition || {}
  const total = Object.values(repartition).reduce((a, b) => a + b, 0) || 1
  let offset = 0
  return Object.entries(repartition).map(([key, value]) => {
    const percent = (value / total) * 100
    const dasharray = `${percent * 2.827} 282.7`
    const currentOffset = offset
    offset += percent * 2.827
    return {
      label: statutLabels[key] || key,
      value,
      percent: Math.round(percent),
      color: statutColors[key] || '#94a3b8',
      dasharray,
      offset: currentOffset
    }
  }).filter(item => item.value > 0)
})

// Barres Budget vs Dépenses
const maxBudgetValue = computed(() => {
  const all = stats.value.budget_vs_depenses.flatMap(item => [item.budget, item.depenses])
  return Math.max(...all, 1)
})

function barHeight(value) {
  const num = Number(value) || 0
  const max = maxBudgetValue.value
  return Math.max(2, (num / max) * 100)
}

// ─── Récupération des données ────────────────────────────
async function fetchDashboard() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard/stats')
    stats.value = data
  } catch (e) {
    console.error('Erreur chargement dashboard', e)
    alert('Impossible de charger les données du tableau de bord.')
  } finally {
    loading.value = false
  }
}

// ─── Date range ──────────────────────────────────────────
const dateRange = computed(() => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth() - 1, 1)
  const end = now
  return `${start.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })} - ${end.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })}`
})

// ─── Montage ──────────────────────────────────────────────
onMounted(fetchDashboard)
</script>

<style scoped>
/* =======================
   DESIGN SYSTEM PROFESSIONNEL
   ======================= */
.dashboard-page {
  padding: 1.5rem 2.5rem;
  background-color: #f8fafc;
  color: #1e293b;
  font-family: 'Inter', system-ui, sans-serif;
  min-height: 100vh;
  margin: -40px;
}

/* HEADER */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.header-titles h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.header-titles p {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0.25rem 0 0 0;
}
.date-picker-mock {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #334155;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.icon-calendar { width: 16px; height: 16px; color: #64748b; }
.arrow-down { font-size: 0.65rem; color: #94a3b8; margin-left: 0.25rem; }

/* LOADING */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  gap: 1rem;
  color: #64748b;
}
.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* KPI CARDS */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}
.kpi-card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.kpi-header-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.kpi-badge {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kpi-badge svg { width: 20px; height: 20px; }
.kpi-badge.chantiers { background: #e0f2fe; color: #0284c7; }
.kpi-badge.projets { background: #dcfce7; color: #16a34a; }
.kpi-badge.budget { background: #ffedd5; color: #ea580c; }
.kpi-badge.depenses { background: #f3e8ff; color: #7c3aed; }
.kpi-badge.employes { background: #ccfbf1; color: #0d9488; }

.kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 500; }
.kpi-value { font-size: 1.35rem; font-weight: 700; color: #0f172a; margin: 0; }
.kpi-trend { font-size: 0.75rem; font-weight: 600; margin-top: 0.75rem; display: flex; align-items: center; gap: 0.25rem; }
.kpi-trend.positive { color: #16a34a; }
.kpi-trend.negative { color: #dc2626; }
.kpi-trend span { color: #94a3b8; font-weight: 400; }

/* V-CARD REUSABLE */
.v-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
}
.card-header {
  padding: 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}
.card-header h3 { font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0; }
.card-body { padding: 1.25rem; flex: 1; }
.card-body.padding-none { padding: 0; }
.flex-between { display: flex; justify-content: space-between; align-items: center; }

/* CHARTS UPPER GRID */
.charts-upper-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.3fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

/* PIE CHART */
.pie-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
}
.pie-svg-container {
  width: 120px;
  height: 120px;
  position: relative;
}
.pie-svg { transform: rotate(-90deg); width: 100%; height: 100%; }
.pie-center-text {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}
.center-qty { font-size: 1.5rem; font-weight: 700; color: #0f172a; display: block; line-height: 1; }
.center-lbl { font-size: 0.75rem; color: #94a3b8; }
.pie-legend { width: 100%; display: flex; flex-direction: column; gap: 0.4rem; }
.legend-row { display: flex; align-items: center; font-size: 0.8rem; }
.legend-indicator { width: 8px; height: 8px; border-radius: 50%; margin-right: 0.5rem; }
.legend-name { color: #64748b; flex: 1; }
.legend-count { font-weight: 600; color: #0f172a; margin-right: 0.25rem; }
.legend-percentage { color: #94a3b8; }

/* PROGRESS RING */
.progress-wrapper {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  width: 100%;
}
.progress-ring-container { position: relative; width: 110px; height: 110px; flex-shrink: 0; }
.ring-svg { transform: rotate(-90deg); width: 100%; height: 100%; }
.ring-content {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%); text-align: center; width: 80%;
}
.ring-number { font-size: 1.35rem; font-weight: 700; color: #0f172a; display: block; }
.ring-text { font-size: 0.65rem; color: #94a3b8; display: block; line-height: 1.1; }
.progress-metrics-list { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; }
.metric-row-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.825rem;
  color: #475569;
  border-bottom: 1px dashed #f1f5f9;
  padding-bottom: 0.25rem;
}
.metric-row-item.danger-text { color: #dc2626; }
.metric-row-item .val { color: #0f172a; }

/* BAR CHART */
.bar-chart-body { position: relative; display: flex; gap: 1rem; padding-top: 1.5rem; }
.y-axis-graduation {
  display: flex; flex-direction: column; justify-content: space-between;
  height: 140px; font-size: 0.7rem; color: #94a3b8; text-align: right; width: 45px;
}
.bar-chart-container {
  flex: 1; display: flex; justify-content: space-between; align-items: flex-end; height: 140px;
}
.chart-labels-guide { display: flex; gap: 0.75rem; }
.guide-item { font-size: 0.75rem; font-weight: 500; color: #64748b; display: flex; align-items: center; gap: 0.25rem; }
.guide-dot { width: 8px; height: 8px; border-radius: 2px; }
.guide-dot.budget { background: #2563eb; }
.guide-dot.depenses { background: #10b981; }

.bar-group-wrapper { display: flex; flex-direction: column; align-items: center; flex: 1; }
.bar-columns { display: flex; gap: 4px; align-items: flex-end; height: 120px; }
.bar-track { height: 100%; display: flex; align-items: flex-end; width: 10px; }
.bar-fill { width: 100%; border-radius: 2px 2px 0 0; }
.bar-fill.budget { background: #2563eb; }
.bar-fill.depenses { background: #10b981; }
.bar-axis-label { font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem; }

/* BOTTOM GRID */
.split-bottom-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 1.5rem;
}
.see-all-link { font-size: 0.8rem; font-weight: 500; color: #2563eb; text-decoration: none; }

/* CHANTIERS RECENTS */
.chantiers-list { display: flex; flex-direction: column; }
.chantier-item-row {
  display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; gap: 1rem;
}
.mock-image-container {
  width: 44px; height: 44px; background: #f1f5f9; border-radius: 6px;
  display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
}
.item-details { flex: 1; }
.item-details h4 { margin: 0; font-size: 0.875rem; font-weight: 600; color: #0f172a; }
.meta-subtext { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: flex; gap: 0.4rem; }
.item-status-progression { text-align: right; display: flex; flex-direction: column; gap: 0.25rem; }
.percentage-txt { font-size: 0.85rem; font-weight: 600; color: #2563eb; }
.badge-status {
  font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; display: inline-block;
}
.badge-status.en-cours { background: #dcfce7; color: #15803d; }
.badge-status.planifie { background: #dbeafe; color: #1e40af; }
.badge-status.termine { background: #ede9fe; color: #5b21b6; }
.badge-status.suspendu { background: #fef3c7; color: #92400e; }
.badge-status.annule { background: #fee2e2; color: #991b1b; }

/* TACHES */
.tasks-list { display: flex; flex-direction: column; }
.task-item-row {
  display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; gap: 1rem;
}
.task-checkbox-circle {
  width: 16px; height: 16px; border-radius: 50%; border: 2px solid #94a3b8; flex-shrink: 0;
}
.task-main-info { flex: 1; }
.task-main-info h4 { margin: 0; font-size: 0.875rem; font-weight: 500; color: #0f172a; }
.task-relation { font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 0.15rem; }
.task-deadline { font-size: 0.8rem; font-weight: 600; white-space: nowrap; }

/* CALENDRIER */
.calendar-nav-arrows { display: flex; gap: 0.25rem; }
.arrow-btn {
  background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; width: 24px; height: 24px;
}
.calendar-wrapper { width: 100%; }
.calendar-weekdays-row { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 0.5rem; }
.calendar-weekdays-row div { font-size: 0.75rem; font-weight: 600; color: #94a3b8; }
.calendar-days-matrix { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
.calendar-day-node {
  aspect-ratio: 1.3; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 500; border-radius: 6px; color: #334155;
}
.is-inactive-month { color: #cbd5e1; }
.is-selected-today { background: #2563eb; color: white !important; font-weight: 600; }

/* ACTIVITES */
.activity-feed { display: flex; flex-direction: column; padding: 0.5rem 0; }
.activity-feed-item {
  display: flex; gap: 1rem; padding: 0.85rem 1.25rem; border-bottom: 1px solid #f1f5f9;
}
.activity-feed-item:last-child { border-bottom: none; }
.activity-icon-avatar {
  width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;
}
.activity-icon-avatar.new-chantier { background: #e0f2fe; }
.activity-icon-avatar.task-done { background: #dcfce7; }
.activity-icon-avatar.doc { background: #fef3c7; }
.activity-icon-avatar.payment { background: #f3e8ff; }
.activity-icon-avatar.default { background: #f1f5f9; }

.activity-node-body { flex: 1; }
.activity-desc-text { margin: 0; font-size: 0.825rem; color: #334155; line-height: 1.3; }
.activity-time-stamp { font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 0.15rem; }

/* RESPONSIVE */
@media (max-width: 1200px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
  .charts-upper-grid { grid-template-columns: 1fr; }
  .split-bottom-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .dashboard-page { padding: 1rem; }
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .date-picker-mock { width: 100%; justify-content: center; }
}
</style>