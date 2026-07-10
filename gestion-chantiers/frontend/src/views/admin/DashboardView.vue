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

        <!-- Calendrier compact -->
        <div class="v-card calendar-card">
          <div class="card-header flex-between">
            <h3 class="calendar-title-bold">{{ calendarMonthName }} {{ calendarYear }}</h3>
            <div class="calendar-nav-arrows">
              <button class="arrow-btn" @click="changeMonth(-1)">‹</button>
              <button class="arrow-btn today-btn" @click="goToday">Aujourd'hui</button>
              <button class="arrow-btn" @click="changeMonth(1)">›</button>
            </div>
          </div>
          <div class="card-body calendar-card-body">
            <div class="calendar-wrapper compact">
              <div class="calendar-weekdays-row">
                <div v-for="day in ['L','M','M','J','V','S','D']" :key="day">{{ day }}</div>
              </div>
              <div class="calendar-days-matrix">
                <button
                  v-for="day in calendarDays"
                  :key="day.date.toISOString()"
                  type="button"
                  class="calendar-day-node"
                  :class="{
                    'is-inactive-month': !day.currentMonth,
                    'is-today': day.isToday,
                    'is-picked': isSameDay(day.date, selectedDate)
                  }"
                  @click="selectDay(day)"
                >
                  <span class="day-number">{{ day.day }}</span>
                  <span class="day-dot-row" v-if="eventsByDate[dateKey(day.date)]?.length">
                    <span
                      v-for="(ev, i) in eventsByDate[dateKey(day.date)].slice(0, 3)"
                      :key="i"
                      class="day-dot"
                      :style="{ background: typeColor(ev.type) }"
                    ></span>
                  </span>
                </button>
              </div>
            </div>

            <!-- Événements du jour sélectionné -->
            <div class="selected-day-panel">
              <div class="selected-day-header">
                <span class="selected-day-label">{{ formatSelectedDate(selectedDate) }}</span>
                <button class="add-event-btn" @click="openAddEvent">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                  Événement
                </button>
              </div>

              <div v-if="eventsLoading" class="events-loading">Chargement…</div>

              <ul v-else-if="selectedDayEvents.length" class="selected-day-events">
                <li v-for="ev in selectedDayEvents" :key="ev.id" class="mini-event-row">
                  <span class="mini-event-dot" :style="{ background: typeColor(ev.type) }"></span>
                  <div class="mini-event-info">
                    <span class="mini-event-title">{{ ev.titre }}</span>
                    <span class="mini-event-meta">
                      <span v-if="ev.heure">{{ ev.heure }}</span>
                      <span v-if="ev.chantier_nom"> · {{ ev.chantier_nom }}</span>
                      <span v-else class="personal-tag">· Personnel</span>
                    </span>
                  </div>
                  <button class="mini-event-delete" title="Supprimer" @click="deleteEvent(ev)">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                  </button>
                </li>
              </ul>

              <p v-else class="no-events-text">Aucun événement ce jour-là.</p>

              <div class="calendar-legend">
                <span class="legend-chip"><span class="chip-dot" :style="{ background: typeColor('reunion') }"></span>Réunion</span>
                <span class="legend-chip"><span class="chip-dot" :style="{ background: typeColor('livraison') }"></span>Livraison</span>
                <span class="legend-chip"><span class="chip-dot" :style="{ background: typeColor('inspection') }"></span>Inspection</span>
                <span class="legend-chip"><span class="chip-dot" :style="{ background: typeColor('autre') }"></span>Autre</span>
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

    <!-- ─── MODALE : Ajouter un événement ─── -->
    <div v-if="showEventModal" class="modal-overlay" @click.self="closeAddEvent">
      <div class="modal-box">
        <div class="modal-header">
          <h3>Nouvel événement</h3>
          <button class="modal-close" @click="closeAddEvent">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <form class="modal-form" @submit.prevent="submitEvent">
          <div class="mode-toggle">
            <button
              type="button"
              class="mode-btn"
              :class="{ active: eventMode === 'chantier' }"
              @click="eventMode = 'chantier'"
            >
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M4 21V8l8-5 8 5v13M9 21v-6h6v6"/></svg>
              Lié à un chantier
            </button>
            <button
              type="button"
              class="mode-btn"
              :class="{ active: eventMode === 'personnel' }"
              @click="eventMode = 'personnel'"
            >
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
              Personnel
            </button>
          </div>

          <label class="field" v-if="eventMode === 'chantier'">
            <span>Chantier</span>
            <select v-model="eventForm.chantier_id" required>
              <option value="" disabled>Sélectionner un chantier</option>
              <option v-for="c in chantiersList" :key="c.id" :value="c.id">{{ c.nom }}</option>
            </select>
          </label>
          <p v-else class="mode-hint">Visible uniquement par vous, sans rattachement à un chantier.</p>

          <label class="field">
            <span>Titre</span>
            <input v-model="eventForm.titre" type="text" required maxlength="255" placeholder="Réunion de chantier" />
          </label>

          <div class="field-row">
            <label class="field">
              <span>Date</span>
              <input v-model="eventForm.date" type="date" required />
            </label>
            <label class="field">
              <span>Heure</span>
              <input v-model="eventForm.heure" type="time" />
            </label>
          </div>

          <label class="field">
            <span>Type</span>
            <select v-model="eventForm.type" required>
              <option value="reunion">Réunion</option>
              <option value="livraison">Livraison</option>
              <option value="inspection">Inspection</option>
              <option value="autre">Autre</option>
            </select>
          </label>

          <label class="field">
            <span>Description</span>
            <textarea v-model="eventForm.description" rows="3" placeholder="Détails (optionnel)"></textarea>
          </label>

          <p v-if="formError" class="form-error">{{ formError }}</p>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeAddEvent">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="submitting">
              {{ submitting ? 'Enregistrement…' : 'Ajouter l\'événement' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
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
const selectedDate = ref(new Date())

const calendarMonthName = computed(() => {
  const date = new Date(calendarYear.value, calendarMonth.value)
  return date.toLocaleString('fr-FR', { month: 'long' })
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

function changeMonth(delta) {
  let m = calendarMonth.value + delta
  let y = calendarYear.value
  if (m < 0) { m = 11; y-- }
  if (m > 11) { m = 0; y++ }
  calendarMonth.value = m
  calendarYear.value = y
}

function goToday() {
  const now = new Date()
  calendarMonth.value = now.getMonth()
  calendarYear.value = now.getFullYear()
  selectedDate.value = now
}

function isSameDay(a, b) {
  return a.toDateString() === b.toDateString()
}

function selectDay(day) {
  selectedDate.value = day.date
  if (!day.currentMonth) {
    calendarMonth.value = day.date.getMonth()
    calendarYear.value = day.date.getFullYear()
  }
}

function formatSelectedDate(date) {
  return date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })
}

function dateKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

// ─── Événements ──────────────────────────────────────────
// NOTE : ceci suppose l'existence d'une route agrégée `/admin/events` qui
// retourne les événements de tous les chantiers (avec `chantier_nom`),
// filtrable par mois/année. Si cette route n'existe pas encore côté API,
// il suffit d'ajouter un contrôleur équivalent à EventController::index
// mais sans le scope par chantier (ou de boucler sur les chantiers actifs).
const events = ref([])
const eventsLoading = ref(false)
const chantiersList = ref([])

const eventsByDate = computed(() => {
  const map = {}
  for (const ev of events.value) {
    const key = ev.date?.slice(0, 10)
    if (!key) continue
    if (!map[key]) map[key] = []
    map[key].push(ev)
  }
  return map
})

const selectedDayEvents = computed(() => {
  return eventsByDate.value[dateKey(selectedDate.value)] || []
})

async function fetchEvents() {
  eventsLoading.value = true
  try {
    const { data } = await api.get('/admin/events', {
      params: { month: calendarMonth.value + 1, year: calendarYear.value }
    })
    events.value = data.data || []
  } catch (e) {
    console.error('Erreur chargement événements', e)
    events.value = []
  } finally {
    eventsLoading.value = false
  }
}

async function fetchChantiersList() {
  try {
    const { data } = await api.get('/admin/chantiers', { params: { per_page: 100 } })
    chantiersList.value = data.data || []
  } catch (e) {
    console.error('Erreur chargement chantiers', e)
    chantiersList.value = []
  }
}

const typeColors = {
  reunion: '#2563eb',
  livraison: '#16a34a',
  inspection: '#f59e0b',
  autre: '#8b5cf6',
}
function typeColor(type) {
  return typeColors[type] || '#94a3b8'
}

// ─── Modale d'ajout ──────────────────────────────────────
const showEventModal = ref(false)
const submitting = ref(false)
const formError = ref('')
const eventMode = ref('chantier') // 'chantier' | 'personnel'
const eventForm = reactive({
  chantier_id: '',
  titre: '',
  description: '',
  date: '',
  heure: '',
  type: 'reunion',
})

function openAddEvent() {
  formError.value = ''
  eventMode.value = 'chantier'
  eventForm.chantier_id = ''
  eventForm.titre = ''
  eventForm.description = ''
  eventForm.date = dateKey(selectedDate.value)
  eventForm.heure = ''
  eventForm.type = 'reunion'
  if (!chantiersList.value.length) fetchChantiersList()
  showEventModal.value = true
}

function closeAddEvent() {
  showEventModal.value = false
}

async function submitEvent() {
  if (eventMode.value === 'chantier' && !eventForm.chantier_id) {
    formError.value = 'Merci de choisir un chantier.'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    // Route globale : un événement "personnel" est envoyé sans chantier_id
    // (l'API le rattache alors à l'utilisateur connecté). Voir la note API
    // plus haut : /admin/events doit accepter chantier_id nullable.
    await api.post('/admin/events', {
      chantier_id: eventMode.value === 'chantier' ? eventForm.chantier_id : null,
      titre: eventForm.titre,
      description: eventForm.description || null,
      date: eventForm.date,
      heure: eventForm.heure || null,
      type: eventForm.type,
    })
    showEventModal.value = false
    await fetchEvents()
  } catch (e) {
    console.error('Erreur création événement', e)
    formError.value = e?.response?.data?.message || "Impossible d'ajouter l'événement."
  } finally {
    submitting.value = false
  }
}

async function deleteEvent(ev) {
  if (!confirm(`Supprimer "${ev.titre}" ?`)) return
  try {
    await api.delete(`/admin/events/${ev.id}`)
    await fetchEvents()
  } catch (e) {
    console.error('Erreur suppression événement', e)
    alert("Impossible de supprimer l'événement.")
  }
}

watch([calendarMonth, calendarYear], fetchEvents)

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
onMounted(() => {
  fetchDashboard()
  fetchEvents()
})
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

/* ═══════════ CALENDRIER COMPACT ═══════════ */
.calendar-card { max-width: 100%; }
.calendar-card .card-header { background: linear-gradient(180deg, #fbfcfe 0%, #ffffff 100%); }
.calendar-title-bold { text-transform: capitalize; letter-spacing: -0.01em; }
.calendar-nav-arrows { display: flex; align-items: center; gap: 0.35rem; }
.arrow-btn {
  background: #f8fafc; border: 1px solid #e2e8f0; font-size: 1rem; color: #64748b;
  cursor: pointer; width: 24px; height: 24px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center; line-height: 1;
  transition: all 0.15s;
}
.arrow-btn:hover { background: #eef2ff; border-color: #c7d2fe; color: #4f46e5; }
.arrow-btn.today-btn {
  width: auto; padding: 0 0.55rem; font-size: 0.66rem; font-weight: 700;
  color: #4f46e5; text-transform: uppercase; letter-spacing: 0.03em;
}
.calendar-card-body { padding: 1rem 1.1rem 1.1rem; }
.calendar-wrapper.compact { width:100%;
    max-width:220px;
    margin:auto;}

.calendar-weekdays-row {
  display: grid; grid-template-columns: repeat(7, 1fr);
  text-align: center; margin-bottom: 0.4rem;
}
.calendar-weekdays-row div { font-size: 0.64rem; font-weight: 700; color: #b0b7c9; letter-spacing: 0.02em; }

.calendar-days-matrix {
  display: grid; grid-template-columns: repeat(7, 1fr);
  gap: 3px; text-align: center;
}
.calendar-day-node {
  position:relative;
    width:28px;
    height:28px;
    display:flex;
    align-items:center;
    justify-content:center;
    border:none;
    border-radius:8px;
    background:transparent;
    cursor:pointer;
    transition:.2s;
    font-size:.72rem;
    color:#475569;
    padding:0;
}

.calendar-day-node:hover { background: #f5f6fb; }
.is-inactive-month { color: #d7dbe6; }
.is-inactive-month:hover { background: transparent; }

/* Aujourd'hui : discret, marqué par la couleur du texte + un point */
.is-today .day-number { color: #4f46e5; font-weight: 800; }
.is-today::after {
  content: '';
  position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%);
  width: 3px; height: 3px; border-radius: 50%; background: #4f46e5;
}
.is-today.is-picked::after { display: none; }
.calendar-day-node:hover{
    background:#f1f5f9;
}

.calendar-day-node .day-number{
    font-weight:500;
}

.is-picked{
    background:#2563eb;
    color:#fff;
}

.is-picked .day-number{
    color:white;
    font-weight:700;
}

.is-today{
    border:2px solid #2563eb;
}

.is-today.is-picked{
    border:none;
}
/* Jour sélectionné : anneau doux, devient plein s'il est aussi "aujourd'hui" */
.is-picked {
  border-color: #c7d2fe;
  background: #eef2ff;
}
.is-picked .day-number { color: #4338ca; font-weight: 700; }
.is-picked.is-today {
  background: #4f46e5; border-color: #4f46e5;
}
.is-picked.is-today .day-number { color: white; }

.day-dot-row { display: flex; gap: 2px; height: 4px; }
.day-dot { width: 4px; height: 4px; border-radius: 50%; }

.selected-day-panel {
  margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9;
}
.selected-day-header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.55rem;
}
.selected-day-label {
  font-size: 0.76rem; font-weight: 700; color: #334155; text-transform: capitalize;
}
.add-event-btn {
  display: inline-flex; align-items: center; gap: 0.3rem;
  font-size: 0.7rem; font-weight: 700; color: #4f46e5;
  background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 7px;
  padding: 0.28rem 0.6rem; cursor: pointer; transition: background 0.15s;
}
.add-event-btn:hover { background: #e0e7ff; }

.selected-day-events { list-style: none; margin: 0 0 0.7rem 0; padding: 0; display: flex; flex-direction: column; gap: 0.4rem; }
.mini-event-row {
  display: flex; align-items: center; gap: 0.55rem;
  padding: 0.45rem 0.55rem; border-radius: 8px; background: #f8fafc;
  border: 1px solid #f1f5f9;
}
.mini-event-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.mini-event-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.mini-event-title { font-size: 0.78rem; font-weight: 600; color: #0f172a; }
.mini-event-meta { font-size: 0.68rem; color: #94a3b8; }
.personal-tag { color: #8b5cf6; font-weight: 600; }
.mini-event-delete {
  background: none; border: none; color: #cbd5e1; cursor: pointer;
  padding: 2px; display: flex; border-radius: 4px;
}
.mini-event-delete:hover { color: #dc2626; background: #fee2e2; }

.no-events-text, .events-loading { font-size: 0.78rem; color: #94a3b8; padding: 0.3rem 0 0.7rem; }

.calendar-legend {
  display: flex; flex-wrap: wrap; gap: 0.5rem 0.75rem;
  padding-top: 0.65rem; border-top: 1px dashed #f1f5f9;
}
.legend-chip {
  display: inline-flex; align-items: center; gap: 0.3rem;
  font-size: 0.68rem; color: #94a3b8; font-weight: 500;
}
.chip-dot { width: 6px; height: 6px; border-radius: 50%; }
.day-dot-row{
    position:absolute;
    bottom:3px;
    display:flex;
    gap:2px;
}

.day-dot{
    width:4px;
    height:4px;
    border-radius:50%;
}
.calendar-weekdays-row{
    display:grid;
    grid-template-columns:repeat(7,1fr);
    margin-bottom:6px;
}

.calendar-weekdays-row div{
    font-size:.63rem;
    font-weight:700;
    color:#94a3b8;
}
.calendar-days-matrix{
    display:grid;
    grid-template-columns:repeat(7,30px);
    justify-content:center;
    gap:4px;
}
.selected-day-panel{
    margin-top:12px;
    border-top:1px solid #eef2f7;
    padding-top:10px;
    max-height:170px;
    overflow:auto;
}
.mini-event-row{
    padding:8px;
    border-radius:10px;
    background:#f8fafc;
    margin-bottom:6px;
}

.mini-event-title{
    font-size:.73rem;
}

.mini-event-meta{
    font-size:.65rem;
}
.calendar-card{
    max-width:320px;
    margin:auto;
}

.calendar-card-body{
    padding:16px;
}
/* ═══════════ MODALE ÉVÉNEMENT ═══════════ */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 100; padding: 1rem;
}
.modal-box {
  background: white; border-radius: 14px; width: 100%; max-width: 420px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
}
.modal-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; }
.modal-close {
  background: #f8fafc; border: none; width: 26px; height: 26px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer;
}
.modal-close:hover { background: #f1f5f9; }
.modal-form { padding: 1.1rem 1.25rem; display: flex; flex-direction: column; gap: 0.9rem; }
.mode-toggle {
  display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;
  background: #f8fafc; padding: 0.3rem; border-radius: 10px; border: 1px solid #f1f5f9;
}
.mode-btn {
  display: flex; align-items: center; justify-content: center; gap: 0.35rem;
  font-family: inherit; font-size: 0.76rem; font-weight: 600; color: #64748b;
  background: transparent; border: none; border-radius: 7px; padding: 0.5rem 0.4rem;
  cursor: pointer; transition: all 0.15s;
}
.mode-btn:hover { color: #334155; }
.mode-btn.active { background: white; color: #4338ca; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
.mode-hint { font-size: 0.78rem; color: #94a3b8; margin: -0.3rem 0 0; }
.field { display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.8rem; font-weight: 600; color: #475569; }
.field input, .field select, .field textarea {
  font-family: inherit; font-size: 0.85rem; font-weight: 400; color: #0f172a;
  border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 0.7rem; background: #f8fafc;
}
.field input:focus, .field select:focus, .field textarea:focus {
  outline: none; border-color: #2563eb; background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-error { color: #dc2626; font-size: 0.8rem; margin: 0; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 0.25rem; }
.btn-cancel, .btn-submit {
  font-size: 0.82rem; font-weight: 600; padding: 0.55rem 1.1rem; border-radius: 8px; cursor: pointer; border: none;
}
.btn-cancel { background: #f1f5f9; color: #475569; }
.btn-cancel:hover { background: #e2e8f0; }
.btn-submit { background: #2563eb; color: white; }
.btn-submit:hover { background: #1d4ed8; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

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