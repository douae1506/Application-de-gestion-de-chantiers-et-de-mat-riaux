<template>
  <div class="dashboard-page">

    <!-- ─── HERO HEADER CLAIR ─── -->
    <header class="page-header light-header">
      <div class="header-glow-light"></div>
      <div class="header-titles">
        <span class="header-eyebrow light"><Sparkles :size="13" :stroke-width="2.4" /> Vue d'ensemble</span>
        <h1 class="light-title">Tableau de bord</h1>
        <p class="light-subtitle">Suivez vos chantiers, projets et activités en un coup d'œil</p>
      </div>
      <div class="header-actions" ref="datePickerRef">
        <div
          class="date-picker-mock light"
          :class="{ active: showDatePicker }"
          @click="showDatePicker = !showDatePicker"
        >
          <CalendarDays :size="16" :stroke-width="2.2" class="icon-calendar-light" />
          <span>{{ dateRangeLabel }}</span>
          <ChevronDown :size="14" :stroke-width="2.4" class="arrow-down-light" :class="{ rotated: showDatePicker }" />
        </div>

        <!-- ─── POPOVER PLAGE DE DATES ─── -->
        <div v-if="showDatePicker" class="date-picker-popover" @click.stop>
          <div class="date-picker-presets">
            <button
              v-for="preset in datePresets"
              :key="preset.key"
              type="button"
              class="preset-btn"
              :class="{ selected: activePreset === preset.key }"
              @click="applyPreset(preset.key)"
            >
              {{ preset.label }}
            </button>
          </div>
          <div class="date-picker-divider"></div>
          <div class="date-picker-custom">
            <label class="date-field">
              <span>Du</span>
              <input type="date" v-model="customStart" :max="customEnd || undefined" @change="activePreset = 'custom'" />
            </label>
            <label class="date-field">
              <span>Au</span>
              <input type="date" v-model="customEnd" :min="customStart || undefined" @change="activePreset = 'custom'" />
            </label>
          </div>
          <div class="date-picker-actions">
            <button type="button" class="btn-reset" @click="resetDateRange">Réinitialiser</button>
            <button type="button" class="btn-apply" :disabled="!customStart || !customEnd" @click="confirmDateRange">Appliquer</button>
          </div>
        </div>
      </div>
    </header>

    <!-- État de chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement des métriques...</span>
    </div>

    <template v-else-if="stats.scope !== 'magasinier'">
      <!-- ─── KPI CARDS ─── -->
      <section class="kpi-grid">
        <div class="kpi-card tone-blue">
          <div class="kpi-header-row">
            <div class="kpi-badge">
              <Building2 :size="20" :stroke-width="2.2" />
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Chantiers</span>
              <h2 class="kpi-value">{{ stats.kpis.chantiers.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive"><TrendingUp :size="13" :stroke-width="2.6" /> <span>{{ stats.kpis.chantiers.new }} nouveaux</span></span>
        </div>

        <div class="kpi-card tone-indigo">
          <div class="kpi-header-row">
            <div class="kpi-badge">
              <FolderKanban :size="20" :stroke-width="2.2" />
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Projets</span>
              <h2 class="kpi-value">{{ stats.kpis.projets.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive"><TrendingUp :size="13" :stroke-width="2.6" /> <span>{{ stats.kpis.projets.new }} nouveaux</span></span>
        </div>

        <div class="kpi-card tone-emerald">
          <div class="kpi-header-row">
            <div class="kpi-badge">
              <Wallet :size="20" :stroke-width="2.2" />
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Budget total</span>
              <h2 class="kpi-value">{{ formatMAD(stats.kpis.budget.total) }}</h2>
            </div>
          </div>
          <span class="kpi-trend" :class="stats.kpis.budget.evolution >= 0 ? 'positive' : 'negative'">
            <TrendingUp v-if="stats.kpis.budget.evolution >= 0" :size="13" :stroke-width="2.6" />
            <TrendingDown v-else :size="13" :stroke-width="2.6" />
            <span>{{ Math.abs(stats.kpis.budget.evolution) }}% vs mois dernier</span>
          </span>
        </div>

        <div class="kpi-card tone-amber">
          <div class="kpi-header-row">
            <div class="kpi-badge">
              <Receipt :size="20" :stroke-width="2.2" />
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Dépenses</span>
              <h2 class="kpi-value">{{ formatMAD(stats.kpis.depenses.total) }}</h2>
            </div>
          </div>
          <span class="kpi-trend" :class="stats.kpis.depenses.evolution >= 0 ? 'positive' : 'negative'">
            <TrendingUp v-if="stats.kpis.depenses.evolution >= 0" :size="13" :stroke-width="2.6" />
            <TrendingDown v-else :size="13" :stroke-width="2.6" />
            <span>{{ Math.abs(stats.kpis.depenses.evolution) }}% vs mois dernier</span>
          </span>
        </div>

        <div v-if="stats.scope === 'admin'" class="kpi-card tone-violet">
          <div class="kpi-header-row">
            <div class="kpi-badge">
              <Users :size="20" :stroke-width="2.2" />
            </div>
            <div class="kpi-body">
              <span class="kpi-label">Employés</span>
              <h2 class="kpi-value">{{ stats.kpis.employes.total }}</h2>
            </div>
          </div>
          <span class="kpi-trend positive"><TrendingUp :size="13" :stroke-width="2.6" /> <span>{{ stats.kpis.employes.new }} nouveaux</span></span>
        </div>
      </section>

      <!-- ─── GRAPHIQUES DU HAUT ─── -->
      <section class="charts-upper-grid">
        <!-- Camembert Répartition -->
        <div class="v-card">
          <div class="card-header">
            <span class="card-header-icon tone-blue"><ChartPie :size="15" :stroke-width="2.2" /></span>
            <h3>Répartition des chantiers par statut</h3>
          </div>
          <div class="card-body">
            <div class="pie-wrapper">
              <div class="pie-svg-container">
                <svg viewBox="0 0 120 120" class="pie-svg">
                  <defs>
                    <linearGradient v-for="item in chartStats" :key="'grad-'+item.label" :id="'pieGrad-' + slug(item.label)" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" :stop-color="item.colorStart" />
                      <stop offset="100%" :stop-color="item.colorEnd" />
                    </linearGradient>
                  </defs>
                  <circle cx="60" cy="60" r="45" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                  <circle
                    v-for="item in chartStats"
                    :key="item.label"
                    cx="60" cy="60" r="45"
                    fill="none"
                    :stroke="`url(#pieGrad-${slug(item.label)})`"
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
                  <span class="legend-indicator" :style="{ background: `linear-gradient(135deg, ${item.colorStart}, ${item.colorEnd})` }"></span>
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
            <span class="card-header-icon tone-indigo"><Target :size="15" :stroke-width="2.2" /></span>
            <h3>Avancement global des projets</h3>
          </div>
          <div class="card-body display-center">
            <div class="progress-wrapper">
              <div class="progress-ring-container">
                <svg viewBox="0 0 120 120" class="ring-svg">
                  <defs>
                    <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#60a5fa" />
                      <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                  </defs>
                  <circle cx="60" cy="60" r="48" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                  <circle cx="60" cy="60" r="48" fill="none" stroke="url(#ringGrad)" stroke-width="10"
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
            <div class="card-header-left">
              <span class="card-header-icon tone-emerald"><ChartColumn :size="15" :stroke-width="2.2" /></span>
              <h3>Budget vs Dépenses</h3>
            </div>
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

      <!-- ─── GRILLE DU BAS AVEC CALENDRIER ULTRA COMPACT ─── -->
      <section class="bottom-grid-compact">
        <!-- Chantiers récents -->
        <div class="v-card">
          <div class="card-header flex-between">
            <div class="card-header-left">
              <span class="card-header-icon tone-blue"><HardHat :size="14" :stroke-width="2.2" /></span>
              <h3>Chantiers récents</h3>
            </div>
            <router-link to="/admin/chantiers" class="see-all-link">Voir tout</router-link>
          </div>
          <div class="card-body padding-none">
            <div class="chantiers-list">
              <div v-for="c in stats.chantiers_recents" :key="c.nom" class="chantier-item-row">
                <div class="mock-image-container"><Building2 :size="16" :stroke-width="2" /></div>
                <div class="item-details">
                  <h4>{{ c.nom }}</h4>
                  <div class="meta-subtext">
                    <span><MapPin :size="10" :stroke-width="2.3" /> {{ c.ville }}</span>
                    <span class="separator">•</span>
                    <span><CalendarDays :size="10" :stroke-width="2.3" /> {{ c.date_debut }}</span>
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
            <div class="card-header-left">
              <span class="card-header-icon tone-violet"><ListChecks :size="14" :stroke-width="2.2" /></span>
              <h3>Tâches à venir</h3>
            </div>
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

        <!-- Calendrier ultra compact + Activités + Notifications sur la même ligne -->
        <div class="triple-col-grid ultra-compact" :class="{ 'two-col': stats.scope === 'responsable' }">
          <!-- Calendrier ultra compact -->
          <div class="v-card calendar-compact">
            <div class="card-header flex-between ultra-compact-header">
              <h3 class="calendar-title-bold ultra">{{ calendarMonthName }} {{ calendarYear }}</h3>
              <div class="calendar-nav-arrows ultra">
                <button type="button" class="arrow-btn ultra" @click="changeMonth(-1)"><ChevronLeft :size="12" :stroke-width="2.4" /></button>
                <button type="button" class="arrow-btn today-btn ultra" @click="goToday">Auj</button>
                <button type="button" class="arrow-btn ultra" @click="changeMonth(1)"><ChevronRight :size="12" :stroke-width="2.4" /></button>
              </div>
            </div>
            <div class="card-body calendar-compact-body ultra">
              <div class="calendar-wrapper compact ultra">
                <div class="calendar-weekdays-row ultra">
                  <div v-for="(day, i) in ['L','M','M','J','V','S','D']" :key="i">{{ day }}</div>
                </div>
                <div class="calendar-days-matrix ultra">
                  <button
                    v-for="day in calendarDays"
                    :key="day.date.toISOString()"
                    type="button"
                    class="calendar-day-node ultra"
                    :class="{
                      'is-inactive-month': !day.currentMonth,
                      'is-today': day.isToday,
                      'is-picked': isSameDay(day.date, selectedDate)
                    }"
                    @click="selectDay(day)"
                  >
                    <span class="day-number ultra">{{ day.day }}</span>
                    <span class="day-dot-row ultra" v-if="eventsByDate[dateKey(day.date)]?.length">
                      <span
                        v-for="(ev, i) in eventsByDate[dateKey(day.date)].slice(0, 2)"
                        :key="i"
                        class="day-dot ultra"
                        :style="{ background: typeColor(ev.type) }"
                      ></span>
                    </span>
                  </button>
                </div>
              </div>

              <!-- Événements du jour sélectionné - ultra compact -->
              <div class="selected-day-panel ultra">
                <div class="selected-day-header ultra">
                  <span class="selected-day-label ultra">{{ formatSelectedDate(selectedDate) }}</span>
                  <button type="button" class="add-event-btn ultra" @click="openAddEvent">
                    <Plus :size="10" :stroke-width="2.6" />
                    Ajouter
                  </button>
                </div>

                <div v-if="eventsLoading" class="events-loading ultra">Chargement…</div>

                <ul v-else-if="selectedDayEvents.length" class="selected-day-events ultra">
                  <li v-for="ev in selectedDayEvents" :key="ev.id" class="mini-event-row ultra">
                    <span class="mini-event-dot ultra" :style="{ background: typeColor(ev.type) }"></span>
                    <div class="mini-event-info ultra">
                      <span class="mini-event-title ultra">{{ ev.titre }}</span>
                      <span class="mini-event-meta ultra">
                        <span v-if="ev.heure">{{ ev.heure }}</span>
                        <span v-if="ev.chantier_nom"> · {{ ev.chantier_nom }}</span>
                      </span>
                    </div>
                    <button type="button" class="mini-event-delete ultra" title="Supprimer" @click="deleteEvent(ev)">
                      <X :size="10" :stroke-width="2.4" />
                    </button>
                  </li>
                </ul>

                <p v-else class="no-events-text ultra">Aucun événement.</p>

                <div class="calendar-legend ultra">
                  <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('reunion') }"></span>Réu</span>
                  <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('livraison') }"></span>Liv</span>
                  <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('inspection') }"></span>Insp</span>
                  <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('autre') }"></span>Autre</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Activités récentes - ultra compact -->
          <div v-if="stats.scope !== 'responsable'" class="v-card ultra">
            <div class="card-header ultra">
              <span class="card-header-icon tone-amber ultra"><Activity :size="13" :stroke-width="2.2" /></span>
              <h3 class="ultra">Activités</h3>
            </div>
            <div class="card-body padding-none">
              <div class="activity-feed ultra">
                <div v-for="act in stats.activites_recentes" :key="act.description" class="activity-feed-item ultra">
                  <div class="activity-icon-avatar ultra" :class="act.type || 'default'">
                    <component :is="activityIcon(act.type)" :size="12" :stroke-width="2.2" />
                  </div>
                  <div class="activity-node-body ultra">
                    <p class="activity-desc-text ultra">
                      <strong>{{ act.description }}</strong>
                    </p>
                    <span class="activity-time-stamp ultra">{{ act.time_ago || 'Récent' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications - ultra compact -->
          <div class="v-card notif-card ultra">
            <div class="card-header flex-between ultra">
              <div class="card-header-left ultra">
                <span class="card-header-icon tone-blue ultra"><Bell :size="13" :stroke-width="2.2" /></span>
                <h3 class="ultra">Notif.</h3>
              </div>
              <button
                type="button"
                class="see-all-link as-button ultra"
                @click="markAllNotifsRead"
                v-if="unreadNotifCount > 0"
              >
                Tout lire
              </button>
            </div>
            <div class="card-body padding-none notif-card-body ultra">
              <div v-if="notifLoading" class="notif-widget-state ultra">Chargement…</div>
              <div v-else-if="!recentNotifications.length" class="notif-widget-state ultra">
                Aucune notif.
              </div>
              <div v-else class="notif-widget-list ultra">
                <div
                  v-for="n in recentNotifications"
                  :key="n.id"
                  class="notif-widget-item ultra"
                  :class="{ unread: !n.read_at }"
                  @click="goToNotification(n)"
                >
                  <span
                    class="notif-widget-icon ultra"
                    :style="{ background: getNotifToneStyle(n.type).bg, color: getNotifToneStyle(n.type).fg }"
                  >
                    <component :is="getNotifIcon(n.type).icon" :size="11" :stroke-width="2.2" />
                  </span>
                  <div class="notif-widget-info ultra">
                    <h4 class="ultra">{{ n.title }}</h4>
                    <span class="notif-widget-meta ultra">{{ n.message }}</span>
                  </div>
                  <span v-if="!n.read_at" class="notif-widget-dot ultra"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

    </template>

    <!-- ─── DASHBOARD MAGASINIER (stock uniquement) ─── -->
    <template v-else>
      <section class="kpi-grid">
        <div class="kpi-card tone-blue">
          <div class="kpi-header-row">
            <div class="kpi-badge"><Package :size="20" :stroke-width="2.2" /></div>
            <div class="kpi-body">
              <span class="kpi-label">Produits</span>
              <h2 class="kpi-value">{{ stats.kpis.produits.total }}</h2>
            </div>
          </div>
        </div>
        <div class="kpi-card tone-indigo">
          <div class="kpi-header-row">
            <div class="kpi-badge"><Warehouse :size="20" :stroke-width="2.2" /></div>
            <div class="kpi-body">
              <span class="kpi-label">Dépôts</span>
              <h2 class="kpi-value">{{ stats.kpis.depots.total }}</h2>
            </div>
          </div>
        </div>
        <div class="kpi-card tone-emerald">
          <div class="kpi-header-row">
            <div class="kpi-badge"><Truck :size="20" :stroke-width="2.2" /></div>
            <div class="kpi-body">
              <span class="kpi-label">Fournisseurs</span>
              <h2 class="kpi-value">{{ stats.kpis.fournisseurs.total }}</h2>
            </div>
          </div>
        </div>
        <div class="kpi-card tone-amber">
          <div class="kpi-header-row">
            <div class="kpi-badge"><Coins :size="20" :stroke-width="2.2" /></div>
            <div class="kpi-body">
              <span class="kpi-label">Valeur du stock</span>
              <h2 class="kpi-value">{{ formatMAD(stats.kpis.valeur_stock.total) }}</h2>
            </div>
          </div>
        </div>
        <div class="kpi-card tone-violet">
          <div class="kpi-header-row">
            <div class="kpi-badge"><AlertTriangle :size="20" :stroke-width="2.2" /></div>
            <div class="kpi-body">
              <span class="kpi-label">Alertes stock</span>
              <h2 class="kpi-value">{{ stats.kpis.alertes.total }}</h2>
            </div>
          </div>
        </div>
      </section>

      <section class="bottom-grid-compact">
        <div class="v-card">
          <div class="card-header flex-between">
            <div class="card-header-left">
              <span class="card-header-icon tone-blue"><Truck :size="14" :stroke-width="2.2" /></span>
              <h3>Mouvements récents</h3>
            </div>
            <router-link to="/admin/mouvements" class="see-all-link">Voir tout</router-link>
          </div>
          <div class="card-body padding-none">
            <div v-if="!stats.mouvements_recents?.length" style="padding:1.5rem; text-align:center; color:#94a3b8; font-size:.85rem;">Aucun mouvement récent.</div>
            <div class="chantiers-list">
              <div v-for="(m, i) in stats.mouvements_recents" :key="i" class="chantier-item-row">
                <div class="mock-image-container"><Package :size="16" :stroke-width="2" /></div>
                <div class="item-details">
                  <h4>{{ m.type_label }} — {{ m.produit }}</h4>
                  <div class="meta-subtext">
                    <span>{{ m.depot }}</span>
                    <span class="separator">•</span>
                    <span>{{ m.date }}</span>
                  </div>
                </div>
                <div class="item-status-progression">
                  <span class="percentage-txt" :style="{ color: m.type === 'sortie' ? '#dc2626' : '#059669' }">{{ m.quantite }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="v-card">
          <div class="card-header flex-between">
            <div class="card-header-left">
              <span class="card-header-icon tone-amber"><AlertTriangle :size="14" :stroke-width="2.2" /></span>
              <h3>Produits en alerte</h3>
            </div>
            <router-link to="/admin/produits" class="see-all-link">Voir tout</router-link>
          </div>
          <div class="card-body padding-none">
            <div v-if="!stats.produits_en_alerte?.length" style="padding:1.5rem; text-align:center; color:#94a3b8; font-size:.85rem;">Aucune alerte de stock 🎉</div>
            <div class="tasks-list">
              <div v-for="(p, i) in stats.produits_en_alerte" :key="i" class="task-item-row">
                <div class="task-checkbox-circle" style="border-color:#dc2626;"></div>
                <div class="task-main-info">
                  <span>{{ p.nom }}</span>
                </div>
                <strong style="color:#dc2626; font-size:.85rem;">{{ p.stock_total }} {{ p.unite }}</strong>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Calendrier personnel + Valeur du stock par dépôt, même ligne -->
      <section class="mag-calendar-row">
        <div class="v-card calendar-compact">
          <div class="card-header flex-between ultra-compact-header">
            <h3 class="calendar-title-bold ultra">{{ calendarMonthName }} {{ calendarYear }}</h3>
            <div class="calendar-nav-arrows ultra">
              <button type="button" class="arrow-btn ultra" @click="changeMonth(-1)"><ChevronLeft :size="12" :stroke-width="2.4" /></button>
              <button type="button" class="arrow-btn today-btn ultra" @click="goToday">Auj</button>
              <button type="button" class="arrow-btn ultra" @click="changeMonth(1)"><ChevronRight :size="12" :stroke-width="2.4" /></button>
            </div>
          </div>
          <div class="card-body calendar-compact-body ultra mag-calendar-body">
            <div class="calendar-wrapper compact ultra">
              <div class="calendar-weekdays-row ultra">
                <div v-for="(day, i) in ['L','M','M','J','V','S','D']" :key="i">{{ day }}</div>
              </div>
              <div class="calendar-days-matrix ultra">
                <button
                  v-for="day in calendarDays"
                  :key="day.date.toISOString()"
                  type="button"
                  class="calendar-day-node ultra"
                  :class="{
                    'is-inactive-month': !day.currentMonth,
                    'is-today': day.isToday,
                    'is-picked': isSameDay(day.date, selectedDate)
                  }"
                  @click="selectDay(day)"
                >
                  <span class="day-number ultra">{{ day.day }}</span>
                  <span class="day-dot-row ultra" v-if="eventsByDate[dateKey(day.date)]?.length">
                    <span
                      v-for="(ev, i) in eventsByDate[dateKey(day.date)].slice(0, 2)"
                      :key="i"
                      class="day-dot ultra"
                      :style="{ background: typeColor(ev.type) }"
                    ></span>
                  </span>
                </button>
              </div>
            </div>

            <!-- Événements personnels du jour sélectionné -->
            <div class="selected-day-panel ultra">
              <div class="selected-day-header ultra">
                <span class="selected-day-label ultra">{{ formatSelectedDate(selectedDate) }}</span>
                <button type="button" class="add-event-btn ultra" @click="openAddEvent">
                  <Plus :size="10" :stroke-width="2.6" />
                  Ajouter
                </button>
              </div>

              <div v-if="eventsLoading" class="events-loading ultra">Chargement…</div>

              <ul v-else-if="selectedDayEvents.length" class="selected-day-events ultra">
                <li v-for="ev in selectedDayEvents" :key="ev.id" class="mini-event-row ultra">
                  <span class="mini-event-dot ultra" :style="{ background: typeColor(ev.type) }"></span>
                  <div class="mini-event-info ultra">
                    <span class="mini-event-title ultra">{{ ev.titre }}</span>
                    <span class="mini-event-meta ultra">
                      <span v-if="ev.heure">{{ ev.heure }}</span>
                    </span>
                  </div>
                  <button type="button" class="mini-event-delete ultra" title="Supprimer" @click="deleteEvent(ev)">
                    <X :size="10" :stroke-width="2.4" />
                  </button>
                </li>
              </ul>

              <p v-else class="no-events-text ultra">Aucun événement personnel.</p>

              <div class="calendar-legend ultra">
                <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('reunion') }"></span>Réu</span>
                <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('livraison') }"></span>Liv</span>
                <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('inspection') }"></span>Insp</span>
                <span class="legend-chip ultra"><span class="chip-dot ultra" :style="{ background: typeColor('autre') }"></span>Autre</span>
              </div>
            </div>
          </div>
        </div>

        <div class="v-card">
          <div class="card-header flex-between">
            <div class="card-header-left">
              <span class="card-header-icon tone-indigo"><Warehouse :size="14" :stroke-width="2.2" /></span>
              <h3>Valeur du stock par dépôt</h3>
            </div>
          </div>
          <div class="card-body padding-none">
            <div v-if="!stats.stock_par_depot?.length" style="padding:1.5rem; text-align:center; color:#94a3b8; font-size:.85rem;">Aucun dépôt.</div>
            <div class="chantiers-list">
              <div v-for="(d, i) in stats.stock_par_depot" :key="i" class="chantier-item-row">
                <div class="mock-image-container"><Warehouse :size="16" :stroke-width="2" /></div>
                <div class="item-details"><h4>{{ d.depot }}</h4></div>
                <div class="item-status-progression">
                  <span class="percentage-txt">{{ formatMAD(d.valeur) }}</span>
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
          <button type="button" class="modal-close" @click="closeAddEvent">
            <X :size="16" :stroke-width="2.2" />
          </button>
        </div>
        <form class="modal-form" @submit.prevent="submitEvent">
          <div class="mode-toggle" v-if="stats.scope !== 'magasinier'">
            <button
              type="button"
              class="mode-btn"
              :class="{ active: eventMode === 'chantier' }"
              @click="eventMode = 'chantier'"
            >
              <HardHat :size="14" :stroke-width="2.2" />
              Lié à un chantier
            </button>
            <button
              type="button"
              class="mode-btn"
              :class="{ active: eventMode === 'personnel' }"
              @click="eventMode = 'personnel'"
            >
              <UserRound :size="14" :stroke-width="2.2" />
              Personnel
            </button>
          </div>

          <label class="field" v-if="eventMode === 'chantier' && stats.scope !== 'magasinier'">
            <span>Chantier</span>
            <select v-model="eventForm.chantier_id" required>
              <option value="" disabled>Sélectionner un chantier</option>
              <option v-for="c in chantiersList" :key="c.id" :value="c.id">{{ c.nom }}</option>
            </select>
          </label>
         

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
              {{ submitting ? 'Enregistrement…' : "Ajouter l'événement" }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useNotifications } from '@/composables/useNotifications'
import { getNotifIcon, getNotifToneStyle } from '@/utils/notificationIcons'
import {
  Sparkles, CalendarDays, ChevronDown, ChevronLeft, ChevronRight,
  Building2, FolderKanban, Wallet, Receipt, Users,
  TrendingUp, TrendingDown, ChartPie, Target, ChartColumn,
  HardHat, ListChecks, MapPin, Plus, X, UserRound,
  Activity, CircleCheckBig, FileText, Banknote, Bell,
  Package, Warehouse, Truck, Coins, AlertTriangle,
} from 'lucide-vue-next'

const router = useRouter()

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

// Icônes professionnelles pour le flux "Activités récentes"
const activityIconMap = {
  'new-chantier': Building2,
  'task-done': CircleCheckBig,
  'doc': FileText,
  'payment': Banknote,
}
function activityIcon(type) {
  return activityIconMap[type] || Activity
}

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
  return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' })
}

function dateKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

// ─── Événements ──────────────────────────────────────────
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
const eventMode = ref('chantier')
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
  eventMode.value = stats.value.scope === 'magasinier' ? 'personnel' : 'chantier'
  eventForm.chantier_id = ''
  eventForm.titre = ''
  eventForm.description = ''
  eventForm.date = dateKey(selectedDate.value)
  eventForm.heure = ''
  eventForm.type = 'reunion'
  if (stats.value.scope !== 'magasinier' && !chantiersList.value.length) fetchChantiersList()
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

function slug(str) {
  return String(str).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-')
}

// Camembert
const statutLabels = {
  planifie: 'Planifié',
  en_cours: 'En cours',
  suspendu: 'Suspendu',
  termine: 'Terminé',
  annule: 'Annulé'
}

const statutGradients = {
  planifie: ['#60a5fa', '#2563eb'],
  en_cours: ['#34d399', '#059669'],
  suspendu: ['#fbbf24', '#d97706'],
  termine:  ['#a78bfa', '#6d28d9'],
  annule:   ['#fb7185', '#e11d48'],
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
    const [colorStart, colorEnd] = statutGradients[key] || ['#94a3b8', '#64748b']
    return {
      label: statutLabels[key] || key,
      value,
      percent: Math.round(percent),
      colorStart,
      colorEnd,
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
    const params = {}
    if (selectedStart.value && selectedEnd.value) {
      params.start = selectedStart.value
      params.end = selectedEnd.value
    }
    const { data } = await api.get('/admin/dashboard/stats', { params })
    stats.value = data
  } catch (e) {
    console.error('Erreur chargement dashboard', e)
    alert('Impossible de charger les données du tableau de bord.')
  } finally {
    loading.value = false
  }
}

// ─── Date range (picker fonctionnel) ──────────────────────
function toISODate(d) {
  return d.toISOString().slice(0, 10)
}
function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}
function endOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0)
}

const datePresets = [
  { key: 'today', label: "Aujourd'hui" },
  { key: '7d', label: '7 derniers jours' },
  { key: '30d', label: '30 derniers jours' },
  { key: 'this_month', label: 'Ce mois-ci' },
  { key: 'last_month', label: 'Mois dernier' },
  { key: 'year', label: 'Cette année' },
]

const showDatePicker = ref(false)
const datePickerRef = ref(null)
const activePreset = ref('last_month')
const customStart = ref('')
const customEnd = ref('')
// Plage réellement appliquée (déclenche le fetch)
const selectedStart = ref('')
const selectedEnd = ref('')

function computePresetRange(key) {
  const now = new Date()
  let start, end
  switch (key) {
    case 'today':
      start = new Date(now.getFullYear(), now.getMonth(), now.getDate())
      end = now
      break
    case '7d':
      start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6)
      end = now
      break
    case '30d':
      start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29)
      end = now
      break
    case 'this_month':
      start = startOfMonth(now)
      end = now
      break
    case 'last_month': {
      const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1)
      start = startOfMonth(lastMonth)
      end = endOfMonth(lastMonth)
      break
    }
    case 'year':
      start = new Date(now.getFullYear(), 0, 1)
      end = now
      break
    default:
      start = startOfMonth(new Date(now.getFullYear(), now.getMonth() - 1, 1))
      end = now
  }
  return { start, end }
}

function applyPreset(key) {
  activePreset.value = key
  const { start, end } = computePresetRange(key)
  customStart.value = toISODate(start)
  customEnd.value = toISODate(end)
  confirmDateRange()
}

function confirmDateRange() {
  if (!customStart.value || !customEnd.value) return
  selectedStart.value = customStart.value
  selectedEnd.value = customEnd.value
  showDatePicker.value = false
  fetchDashboard()
}

function resetDateRange() {
  activePreset.value = 'last_month'
  const { start, end } = computePresetRange('last_month')
  customStart.value = toISODate(start)
  customEnd.value = toISODate(end)
  selectedStart.value = customStart.value
  selectedEnd.value = customEnd.value
  showDatePicker.value = false
  fetchDashboard()
}

const dateRangeLabel = computed(() => {
  if (!selectedStart.value || !selectedEnd.value) return 'Sélectionner une période'
  const start = new Date(selectedStart.value + 'T00:00:00')
  const end = new Date(selectedEnd.value + 'T00:00:00')
  return `${start.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })} - ${end.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })}`
})

function handleClickOutside(event) {
  if (datePickerRef.value && !datePickerRef.value.contains(event.target)) {
    showDatePicker.value = false
  }
}

// ─── Notifications ────────────────────────────────────────
const {
  notifications: notifList,
  unreadCount: unreadNotifCount,
  loading: notifLoading,
  markAsRead: markNotifAsRead,
  markAllAsRead: markAllNotifsRead,
  ensureInit: ensureNotifInit,
} = useNotifications()

const recentNotifications = computed(() => notifList.value.slice(0, 4))

async function goToNotification(n) {
  if (!n.read_at) await markNotifAsRead(n.id)
  const link = n.data?.link
  if (link) router.push(link)
}

// ─── Montage ──────────────────────────────────────────────
onMounted(() => {
  // Initialise la plage par défaut (mois dernier) sans fermer un popover inexistant
  const { start, end } = computePresetRange('last_month')
  customStart.value = toISODate(start)
  customEnd.value = toISODate(end)
  selectedStart.value = customStart.value
  selectedEnd.value = customEnd.value

  fetchDashboard()
  fetchEvents()
  ensureNotifInit()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* ════════════════════════════════════════════════════════
   DESIGN SYSTEM — HEADER CLAIR + CALENDRIER ULTRA COMPACT
   ════════════════════════════════════════════════════════ */
.dashboard-page {
  --blue-500: #3b82f6;
  --blue-600: #2563eb;
  --blue-700: #1d4ed8;
  --indigo-600: #4f46e5;
  --emerald-600: #059669;
  --amber-600: #d97706;
  --violet-600: #7c3aed;
  --rose-600: #e11d48;
  --ink: #0f172a;
  --muted: #64748b;
  --border: #e7ecf3;

  padding: 1.75rem 2.5rem 2.5rem;
  background: #f4f7fc;
  color: var(--ink);
  font-family: 'Inter', system-ui, sans-serif;
  min-height: 100vh;
  margin: -40px;
}

/* ─── HERO HEADER CLAIR ─── */
.page-header.light-header {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.75rem;
  padding: 1.6rem 2rem;
  border-radius: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
  overflow: hidden;
}
.header-glow-light {
  position: absolute;
  top: -60%; right: -8%;
  width: 320px; height: 320px;
  background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%);
  pointer-events: none;
}
.header-titles { position: relative; z-index: 1; }
.header-eyebrow.light {
  display: inline-flex; align-items: center; gap: 0.35rem;
  font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
  color: var(--blue-600);
  background: #eff6ff;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  margin-bottom: 0.65rem;
}
.header-titles h1.light-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: var(--ink);
  margin: 0;
  letter-spacing: -0.01em;
}
.header-titles p.light-subtitle {
  color: var(--muted);
  font-size: 0.88rem;
  margin: 0.35rem 0 0 0;
}
.header-actions { position: relative; z-index: 1; }
.date-picker-mock.light {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #ffffff;
  border: 1px solid var(--border);
  padding: 0.6rem 1.1rem;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--ink);
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.date-picker-mock.light:hover,
.date-picker-mock.light.active {
  border-color: var(--blue-500);
  box-shadow: 0 4px 12px rgba(59,130,246,0.12);
}
.icon-calendar-light { color: var(--blue-600); }
.arrow-down-light { color: var(--muted); transition: transform 0.15s; }
.arrow-down-light.rotated { transform: rotate(180deg); }

/* ─── POPOVER DATE PICKER ─── */
.date-picker-popover {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  z-index: 20;
  width: 300px;
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 14px;
  box-shadow: 0 12px 32px rgba(15,23,42,0.12);
  padding: 0.9rem;
  animation: pickerFadeIn 0.15s ease-out;
}
@keyframes pickerFadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
.date-picker-presets {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.4rem;
}
.preset-btn {
  border: 1px solid var(--border);
  background: #f8fafc;
  color: var(--ink);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.5rem 0.6rem;
  border-radius: 9px;
  cursor: pointer;
  text-align: left;
  transition: all 0.15s;
}
.preset-btn:hover {
  border-color: var(--blue-500);
  background: #eff6ff;
}
.preset-btn.selected {
  background: var(--blue-600);
  border-color: var(--blue-600);
  color: #fff;
}
.date-picker-divider {
  height: 1px;
  background: var(--border);
  margin: 0.85rem 0;
}
.date-picker-custom {
  display: flex;
  gap: 0.6rem;
}
.date-field {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.date-field input[type="date"] {
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0.4rem 0.5rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--ink);
  font-family: inherit;
}
.date-field input[type="date"]:focus {
  outline: none;
  border-color: var(--blue-500);
}
.date-picker-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.6rem;
  margin-top: 0.9rem;
}
.btn-reset {
  background: none;
  border: none;
  color: var(--muted);
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.4rem 0.2rem;
}
.btn-reset:hover { color: var(--ink); text-decoration: underline; }
.btn-apply {
  background: var(--blue-600);
  border: none;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 0.55rem 1.1rem;
  border-radius: 9px;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-apply:hover:not(:disabled) { background: var(--blue-700); }
.btn-apply:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── LOADING ─── */
.loading-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 1rem; padding: 6rem 0; color: var(--muted);
}
.spinner {
  width: 34px; height: 34px; border-radius: 50%;
  border: 3px solid #dbeafe; border-top-color: var(--blue-600);
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── KPI CARDS ─── */
/* ─── CALENDRIER PERSONNEL MAGASINIER + STOCK PAR DÉPÔT (même ligne) ─── */
.mag-calendar-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
  margin-top: 1.25rem;
}
.mag-calendar-body {
  display: flex;
  flex-direction: column;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}
.kpi-card {
  position: relative;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid var(--border);
  padding: 1.25rem 1.3rem 1.1rem;
  overflow: hidden;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.kpi-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 30px -14px rgba(15, 23, 42, 0.18);
}
.kpi-card.tone-blue::before    { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.kpi-card.tone-indigo::before  { background: linear-gradient(90deg, #818cf8, #4f46e5); }
.kpi-card.tone-emerald::before { background: linear-gradient(90deg, #34d399, #059669); }
.kpi-card.tone-amber::before   { background: linear-gradient(90deg, #fbbf24, #d97706); }
.kpi-card.tone-violet::before  { background: linear-gradient(90deg, #c084fc, #7c3aed); }

.kpi-header-row { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 0.85rem; }
.kpi-badge {
  width: 44px; height: 44px; border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.tone-blue .kpi-badge    { background: linear-gradient(135deg, #60a5fa, #1d4ed8); box-shadow: 0 8px 16px -6px rgba(37,99,235,0.55); }
.tone-indigo .kpi-badge  { background: linear-gradient(135deg, #a5b4fc, #4f46e5); box-shadow: 0 8px 16px -6px rgba(79,70,229,0.5); }
.tone-emerald .kpi-badge { background: linear-gradient(135deg, #6ee7b7, #059669); box-shadow: 0 8px 16px -6px rgba(5,150,105,0.5); }
.tone-amber .kpi-badge   { background: linear-gradient(135deg, #fcd34d, #d97706); box-shadow: 0 8px 16px -6px rgba(217,119,6,0.5); }
.tone-violet .kpi-badge  { background: linear-gradient(135deg, #d8b4fe, #7c3aed); box-shadow: 0 8px 16px -6px rgba(124,58,237,0.5); }

.kpi-body { display: flex; flex-direction: column; }
.kpi-label { font-size: 0.78rem; color: var(--muted); font-weight: 600; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: var(--ink); margin: 0.1rem 0 0; letter-spacing: -0.02em; }

.kpi-trend {
  display: inline-flex; align-items: center; gap: 0.3rem;
  font-size: 0.76rem; font-weight: 700; padding: 0.25rem 0.55rem;
  border-radius: 999px;
}
.kpi-trend span { font-weight: 500; color: inherit; opacity: 0.85; }
.kpi-trend.positive { color: #059669; background: #ecfdf5; }
.kpi-trend.negative { color: #dc2626; background: #fef2f2; }

/* ─── CARTES GÉNÉRIQUES ─── */
.v-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: box-shadow 0.18s ease;
}
.v-card:hover { box-shadow: 0 14px 28px -18px rgba(15, 23, 42, 0.16); }

.card-header {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 1.1rem 1.3rem;
  border-bottom: 1px solid #f1f4f9;
}
.card-header.flex-between { justify-content: space-between; }
.card-header-left { display: flex; align-items: center; gap: 0.6rem; }
.card-header h3 { margin: 0; font-size: 0.94rem; font-weight: 700; color: var(--ink); letter-spacing: -0.01em; }

.card-header-icon {
  width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.card-header-icon.tone-blue    { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
.card-header-icon.tone-indigo  { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4338ca; }
.card-header-icon.tone-emerald { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857; }
.card-header-icon.tone-amber   { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; }
.card-header-icon.tone-violet  { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; }

.card-body { padding: 1.3rem; }
.card-body.padding-none { padding: 0; }
.card-body.display-center { display: flex; align-items: center; justify-content: center; }

.see-all-link {
  font-size: 0.75rem; font-weight: 700; color: var(--blue-600); text-decoration: none;
  background: none; border: none; cursor: pointer; padding: 0;
}
.see-all-link:hover { color: var(--blue-700); }

/* ─── GRAPHIQUES DU HAUT ─── */
.charts-upper-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.3fr;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

/* Camembert */
.pie-wrapper { display: flex; align-items: center; gap: 1.5rem; }
.pie-svg-container { position: relative; width: 130px; height: 130px; flex-shrink: 0; }
.pie-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.pie-segment { transition: stroke-dasharray 0.4s ease; }
.pie-center-text {
  position: absolute; inset: 0; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
}
.center-qty { font-size: 1.5rem; font-weight: 800; color: var(--ink); }
.center-lbl { font-size: 0.7rem; color: var(--muted); font-weight: 600; }

.pie-legend { display: flex; flex-direction: column; gap: 0.65rem; flex: 1; }
.legend-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; }
.legend-indicator { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.legend-name { flex: 1; color: var(--ink); font-weight: 600; }
.legend-count { font-weight: 700; color: var(--ink); }
.legend-percentage { color: var(--muted); font-size: 0.74rem; }

/* Avancement global */
.progress-wrapper { display: flex; align-items: center; gap: 1.75rem; }
.progress-ring-container { position: relative; width: 130px; height: 130px; flex-shrink: 0; }
.ring-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-progress-bar { transition: stroke-dasharray 0.5s ease; }
.ring-content {
  position: absolute; inset: 0; display: flex; flex-direction: column;
  align-items: center; justify-content: center; text-align: center;
}
.ring-number { font-size: 1.5rem; font-weight: 800; color: var(--blue-700); }
.ring-text { font-size: 0.68rem; color: var(--muted); font-weight: 600; max-width: 80px; }

.progress-metrics-list { display: flex; flex-direction: column; gap: 0.7rem; }
.metric-row-item { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; font-size: 0.82rem; }
.metric-row-item .label { color: var(--muted); font-weight: 500; }
.metric-row-item .val { color: var(--ink); font-weight: 700; }
.metric-row-item.danger-text .val { color: var(--rose-600); }

/* Budget vs Dépenses */
.chart-labels-guide { display: flex; gap: 1rem; }
.guide-item { display: flex; align-items: center; gap: 0.35rem; font-size: 0.76rem; color: var(--muted); font-weight: 600; }
.guide-dot { width: 8px; height: 8px; border-radius: 50%; }
.guide-dot.budget { background: linear-gradient(135deg, #60a5fa, #1d4ed8); }
.guide-dot.depenses { background: linear-gradient(135deg, #6ee7b7, #059669); }

.bar-chart-body { display: flex; gap: 1rem; height: 230px; }
.y-axis-graduation {
  display: flex; flex-direction: column; justify-content: space-between;
  font-size: 0.68rem; color: var(--muted); text-align: right; padding-bottom: 1.6rem;
  min-width: 52px;
}
.bar-chart-container { flex: 1; display: flex; align-items: flex-end; gap: 0.9rem; }
.bar-group-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; height: 100%; }
.bar-columns { flex: 1; display: flex; align-items: flex-end; gap: 0.3rem; width: 100%; }
.bar-track { flex: 1; height: 100%; display: flex; align-items: flex-end; background: #f5f7fb; border-radius: 6px 6px 0 0; overflow: hidden; }
.bar-fill { width: 100%; border-radius: 5px 5px 0 0; transition: height 0.4s ease; }
.bar-fill.budget { background: linear-gradient(180deg, #60a5fa, #1d4ed8); }
.bar-fill.depenses { background: linear-gradient(180deg, #6ee7b7, #059669); }
.bar-axis-label { font-size: 0.74rem; color: var(--muted); font-weight: 600; }

/* ─── GRILLE DU BAS ─── */
.bottom-grid-compact {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 1.25rem;
}

/* Chantiers récents - compact */
.chantiers-list { display: flex; flex-direction: column; }
.chantier-item-row {
  display: flex; align-items: center; padding: 0.6rem 1rem; border-bottom: 1px solid #f1f4f9; gap: 0.7rem;
}
.chantier-item-row:last-child { border-bottom: none; }
.mock-image-container {
  width: 32px; height: 32px; border-radius: 9px;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.item-details { flex: 1; min-width: 0; }
.item-details h4 { margin: 0; font-size: 0.78rem; font-weight: 700; color: var(--ink); }
.meta-subtext { font-size: 0.65rem; color: var(--muted); margin-top: 0.1rem; display: flex; align-items: center; gap: 0.3rem; flex-wrap: wrap; }
.meta-subtext span { display: inline-flex; align-items: center; gap: 0.15rem; }
.item-status-progression { text-align: right; display: flex; flex-direction: column; gap: 0.2rem; flex-shrink: 0; }
.percentage-txt { font-size: 0.75rem; font-weight: 800; color: var(--blue-600); }
.badge-status { font-size: 0.6rem; padding: 0.1rem 0.45rem; border-radius: 999px; font-weight: 700; display: inline-block; }
.badge-status.en-cours { background: #dcfce7; color: #15803d; }
.badge-status.planifie { background: #dbeafe; color: #1e40af; }
.badge-status.termine { background: #ede9fe; color: #5b21b6; }
.badge-status.suspendu { background: #fef3c7; color: #92400e; }
.badge-status.annule { background: #fee2e2; color: #991b1b; }

/* Tâches - compact */
.tasks-list { display: flex; flex-direction: column; }
.task-item-row {
  display: flex; align-items: center; padding: 0.6rem 1rem; border-bottom: 1px solid #f1f4f9; gap: 0.7rem;
}
.task-item-row:last-child { border-bottom: none; }
.task-checkbox-circle { width: 12px; height: 12px; border-radius: 50%; border: 2px solid #94a3b8; flex-shrink: 0; }
.task-main-info { flex: 1; min-width: 0; }
.task-main-info h4 { margin: 0; font-size: 0.75rem; font-weight: 600; color: var(--ink); }
.task-relation { font-size: 0.65rem; color: var(--muted); display: block; margin-top: 0.1rem; }
.task-deadline { font-size: 0.7rem; font-weight: 700; white-space: nowrap; }

/* ─── TROIS COLONNES ULTRA COMPACT ─── */
.triple-col-grid {
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: 1.15fr 1fr 1fr;
  gap: 1rem;
  margin-top: 0.25rem;
}
.triple-col-grid.two-col {
  grid-template-columns: 1.2fr 1fr;
}

.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(3px);
  display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;
}
.modal-box {
  background: #fff; border-radius: 18px; width: 100%; max-width: 440px;
  max-height: 90vh; overflow-y: auto; box-shadow: 0 30px 70px -20px rgba(15,23,42,0.4);
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f4f9;
}
.modal-header h3 { margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--ink); }
.modal-close {
  background: #f5f7fb; border: none; width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center; color: var(--muted); cursor: pointer;
}
.modal-close:hover { background: #fee2e2; color: #e11d48; }

.modal-form { padding: 1.4rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }

.mode-toggle { display: flex; gap: 0.5rem; background: #f5f7fb; padding: 0.3rem; border-radius: 11px; }
.mode-btn {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
  padding: 0.55rem; border-radius: 8px; border: none; background: none; cursor: pointer;
  font-size: 0.78rem; font-weight: 700; color: var(--muted); transition: all 0.15s;
}
.mode-btn.active { background: #fff; color: var(--blue-600); box-shadow: 0 2px 8px rgba(15,23,42,0.08); }
.mode-hint { font-size: 0.78rem; color: var(--muted); background: #f8fafc; padding: 0.65rem 0.8rem; border-radius: 10px; margin: 0; }

.field { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8rem; font-weight: 700; color: var(--ink); }
.field input, .field select, .field textarea {
  border: 1px solid var(--border); border-radius: 10px; padding: 0.6rem 0.75rem;
  font-size: 0.85rem; font-family: inherit; color: var(--ink); background: #fff;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.field input:focus, .field select:focus, .field textarea:focus {
  outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.form-error { color: #dc2626; font-size: 0.78rem; background: #fef2f2; padding: 0.6rem 0.75rem; border-radius: 8px; margin: 0; }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.65rem; margin-top: 0.3rem; }
.btn-cancel {
  padding: 0.6rem 1.1rem; border-radius: 10px; border: 1px solid var(--border);
  background: #fff; color: var(--muted); font-weight: 700; font-size: 0.82rem; cursor: pointer;
}
.btn-cancel:hover { background: #f5f7fb; }
.btn-submit {
  padding: 0.6rem 1.2rem; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff;
  font-weight: 700; font-size: 0.82rem; cursor: pointer;
  box-shadow: 0 8px 18px -6px rgba(37,99,235,0.5);
  transition: filter 0.15s;
}
.btn-submit:hover:not(:disabled) { filter: brightness(1.06); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

/* ═══════════ RESPONSIVE ═══════════ */
@media (max-width: 1200px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
  .charts-upper-grid { grid-template-columns: 1fr; }
  .bottom-grid-compact { grid-template-columns: 1fr; }
  .mag-calendar-row { grid-template-columns: 1fr; }
  .triple-col-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .triple-col-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .dashboard-page { padding: 1.25rem 1.25rem 2rem; }
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .page-header.light-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .field-row { grid-template-columns: 1fr; }
  .mag-calendar-row { grid-template-columns: 1fr; }
}
/* ─── CALENDRIER COMPACT ─── */
.calendar-compact .card-header.ultra-compact-header {
  padding: 0.4rem 0.7rem !important;
  background: #fafcff;
  min-height: 38px;
}

.calendar-title-bold.ultra {
  font-size: 0.94rem !important;
  font-weight: 700;
}

.calendar-nav-arrows.ultra {
  gap: 0.25rem;
  display: flex;
  align-items: center;
}

.arrow-btn.ultra {
  width: 28px !important;
  height: 28px !important;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

.arrow-btn.ultra svg {
  width: 14px !important;
  height: 14px !important;
}

.arrow-btn.ultra:hover {
  background: #f1f5f9;
}

.today-btn.ultra {
  font-size: 0.72rem !important;
  padding: 0 0.6rem !important;
  height: 28px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
}

.today-btn.ultra:hover {
  background: #dbeafe;
}

.calendar-compact-body.ultra {
  padding: 0.35rem 0.7rem 0.5rem !important;
}

.calendar-weekdays-row.ultra {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 2px;
}

.calendar-weekdays-row.ultra div {
  font-size: 0.7rem !important;
  color: #94a3b8;
  font-weight: 700;
  padding: 2px 0;
  text-transform: uppercase;
}

.calendar-days-matrix.ultra {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px !important;
}

.calendar-day-node.ultra {
  min-height: 36px !important;
  border-radius: 6px !important;
  border: 1px solid transparent;
  background: transparent;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 1px 0;
  transition: all 0.1s;
}

.calendar-day-node.ultra:hover {
  background: #f1f5f9;
}

.calendar-day-node.ultra .day-number {
  font-size: 0.84rem !important;
  font-weight: 600;
  color: #0f172a;
  line-height: 1.2;
}

.calendar-day-node.ultra.is-inactive-month .day-number {
  color: #cbd5e1;
}

.calendar-day-node.ultra.is-today {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.calendar-day-node.ultra.is-today .day-number {
  color: #2563eb;
  font-weight: 800;
}

.calendar-day-node.ultra.is-picked {
  background: #2563eb;
  border-color: #2563eb;
}

.calendar-day-node.ultra.is-picked .day-number {
  color: white;
}

.day-dot-row.ultra {
  display: flex;
  gap: 1px !important;
  margin-top: 1px;
  justify-content: center;
}

.day-dot.ultra {
  width: 4px !important;
  height: 4px !important;
  border-radius: 50%;
}

/* Événements du jour - compact */
.selected-day-panel.ultra {
  margin-top: 0.35rem !important;
  padding-top: 0.35rem !important;
  border-top: 1px solid #f1f4f9;
}

.selected-day-header.ultra {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem !important;
}

.selected-day-label.ultra {
  font-size: 0.8rem !important;
  font-weight: 700;
  color: #64748b;
  text-transform: capitalize;
}

.add-event-btn.ultra {
  font-size: 0.72rem !important;
  padding: 0.25rem 0.65rem !important;
  gap: 0.2rem !important;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  font-weight: 600;
  cursor: pointer;
  height: 28px;
}

.add-event-btn.ultra:hover {
  background: #1d4ed8;
}

.add-event-btn.ultra svg {
  width: 13px !important;
  height: 13px !important;
}

.selected-day-events.ultra {
  display: flex;
  flex-direction: column;
  gap: 0.15rem !important;
  padding: 0;
  margin: 0;
  list-style: none;
}

.mini-event-row.ultra {
  display: flex;
  align-items: flex-start;
  gap: 0.3rem !important;
  padding: 2px 0;
}

.mini-event-row.ultra .mini-event-dot {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50%;
  margin-top: 0.3rem !important;
  flex-shrink: 0;
}

.mini-event-info.ultra {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.mini-event-row.ultra .mini-event-title {
  font-size: 0.8rem !important;
  font-weight: 600;
  color: #0f172a;
  line-height: 1.3;
}

.mini-event-row.ultra .mini-event-meta {
  font-size: 0.7rem !important;
  color: #94a3b8;
  line-height: 1.3;
}

.mini-event-row.ultra .mini-event-delete {
  width: 20px !important;
  height: 20px !important;
  border: none;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  flex-shrink: 0;
}

.mini-event-row.ultra .mini-event-delete:hover {
  background: #fee2e2;
  color: #ef4444;
}

.mini-event-row.ultra .mini-event-delete svg {
  width: 12px !important;
  height: 12px !important;
}

.no-events-text.ultra {
  font-size: 0.78rem !important;
  color: #94a3b8;
  padding: 0.15rem 0 !important;
  margin: 0;
}

.calendar-legend.ultra {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem 0.4rem !important;
  margin-top: 0.35rem !important;
  padding-top: 0.35rem;
  border-top: 1px solid #f1f4f9;
}

.legend-chip.ultra {
  font-size: 0.7rem !important;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.15rem !important;
  font-weight: 500;
}

.chip-dot.ultra {
  width: 6px !important;
  height: 6px !important;
  border-radius: 50%;
}

/* ─── ACTIVITÉS COMPACT ─── */
.activity-feed.ultra {
  display: flex;
  flex-direction: column;
  padding: 0.1rem 0 !important;
}

.activity-feed-item.ultra {
  display: flex;
  align-items: center;
  padding: 0.35rem 0.7rem !important;
  gap: 0.6rem !important;
  border-bottom: 1px solid #f8fafc;
}

.activity-feed-item.ultra:last-child {
  border-bottom: none;
}

.activity-feed-item.ultra .activity-icon-avatar {
  width: 32px !important;
  height: 32px !important;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-feed-item.ultra .activity-icon-avatar.default {
  background: #f1f5f9;
  color: #64748b;
}

.activity-feed-item.ultra .activity-icon-avatar.new-chantier {
  background: #dbeafe;
  color: #2563eb;
}

.activity-feed-item.ultra .activity-icon-avatar.task-done {
  background: #dcfce7;
  color: #16a34a;
}

.activity-feed-item.ultra .activity-icon-avatar.doc {
  background: #fef3c7;
  color: #d97706;
}

.activity-feed-item.ultra .activity-icon-avatar.payment {
  background: #ede9fe;
  color: #7c3aed;
}

.activity-feed-item.ultra .activity-icon-avatar svg {
  width: 16px !important;
  height: 16px !important;
}

.activity-node-body.ultra {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.activity-feed-item.ultra .activity-desc-text {
  font-size: 0.82rem !important;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}

.activity-feed-item.ultra .activity-desc-text strong {
  font-weight: 600;
}

.activity-feed-item.ultra .activity-time-stamp {
  font-size: 0.7rem !important;
  color: #94a3b8;
  line-height: 1.3;
}

/* ─── NOTIFICATIONS COMPACT ─── */
.notif-card.ultra .card-header {
  padding: 0.35rem 0.7rem !important;
  min-height: 32px;
}

.notif-card.ultra .card-header-left {
  gap: 0.4rem !important;
}

.notif-widget-item.ultra {
  display: flex;
  align-items: center;
  padding: 0.35rem 0.7rem !important;
  gap: 0.6rem !important;
  border-bottom: 1px solid #f8fafc;
  cursor: pointer;
  transition: background 0.1s;
  position: relative;
}

.notif-widget-item.ultra:last-child {
  border-bottom: none;
}

.notif-widget-item.ultra:hover {
  background: #f8fafc;
}

.notif-widget-item.ultra.unread {
  background: #eff6ff;
}

.notif-widget-item.ultra.unread:hover {
  background: #dbeafe;
}

.notif-widget-item.ultra .notif-widget-icon {
  width: 32px !important;
  height: 32px !important;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notif-widget-item.ultra .notif-widget-icon svg {
  width: 16px !important;
  height: 16px !important;
}

.notif-widget-info.ultra {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.notif-widget-item.ultra .notif-widget-info h4 {
  font-size: 0.82rem !important;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}

.notif-widget-item.ultra .notif-widget-meta {
  font-size: 0.72rem !important;
  color: #64748b;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notif-widget-item.ultra .notif-widget-dot {
  width: 7px !important;
  height: 7px !important;
  border-radius: 50%;
  background: #2563eb;
  flex-shrink: 0;
  margin-top: 0.15rem !important;
}

.notif-widget-state.ultra {
  padding: 0.9rem 0.7rem !important;
  font-size: 0.8rem !important;
  color: #94a3b8;
  text-align: center;
  margin: 0;
}

.see-all-link.as-button.ultra {
  font-size: 0.75rem !important;
  background: none;
  border: none;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.see-all-link.as-button.ultra:hover {
  text-decoration: underline;
}

/* ─── RESPONSIVE COMPACT ─── */
@media (max-width: 640px) {
  .calendar-compact-body.ultra {
    padding: 0.25rem 0.5rem 0.4rem !important;
  }
  
  .calendar-day-node.ultra {
    min-height: 22px !important;
  }
  
  .calendar-day-node.ultra .day-number {
    font-size: 0.6rem !important;
  }
}
</style>