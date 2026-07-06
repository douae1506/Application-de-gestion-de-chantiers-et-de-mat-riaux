<template>
  <div class="app-layout-clean">
    <main class="main-content-full">
      <!-- États de chargement / erreur -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Chargement du chantier...</span>
      </div>

      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchChantier" class="btn btn-primary">Réessayer</button>
      </div>

      <template v-else-if="chantier">
        <!-- Barre supérieure -->
        <div class="top-bar">
          <div class="breadcrumb">
            Chantiers &gt; <span @click="$router.push({ name: 'chantiers' })" style="cursor:pointer;">Liste des chantiers</span> &gt; <span class="active">Détail du chantier</span>
          </div>
          <div class="action-buttons">
            <button class="btn btn-secondary" @click="$router.push({ name: 'chantiers' })">Retour à la liste</button>
            <button class="btn btn-secondary" @click="editChantier">Modifier</button>
            <button class="btn btn-danger" @click="deleteChantier">Supprimer</button>
          </div>
        </div>

        <!-- En-tête du chantier -->
        <div class="detail-header-card">
          <div class="header-image-container">
            <img :src="getImageForType(chantier.type)" :alt="'Image ' + chantier.type" class="header-img" />
          </div>

          <div class="header-main-info">
            <div class="header-title-row">
              <span class="eyebrow">CHANTIER</span>
              <div class="title-with-badge">
                <h1>{{ chantier.nom }}</h1>
                <span class="status-badge" :class="statutClass(chantier.statut)">{{ chantier.statut_label }}</span>
              </div>
              <div class="location-date">
                <span class="meta-item">{{ chantier.ville }}, {{ chantier.pays }}</span>
                <span class="meta-item">{{ chantier.date_debut }} → {{ chantier.date_fin_prevue || 'Non définie' }}</span>
              </div>
            </div>

            <div class="kpi-grid">
              <div class="kpi-box">
                <span class="kpi-title">Progression</span>
                <span class="kpi-number">{{ chantierProgression }}%</span>
                <div class="progress-bar-mini">
                  <div class="progress-fill" :style="{ width: chantierProgression + '%' }"></div>
                </div>
              </div>

              <div class="kpi-box">
                <span class="kpi-title">Budget dépensé</span>
                <span class="kpi-number text-sm">{{ formatMAD(totalCoutReel) }}</span>
                <span class="kpi-sub">sur {{ formatMAD(chantier.budget_total) }}</span>
              </div>

              <div class="kpi-box">
                <span class="kpi-title">Projets</span>
                <span class="kpi-number">{{ chantier.projets?.length || 0 }}</span>
                <span class="kpi-sub">en cours</span>
              </div>

              <div class="kpi-box text-green">
                <span class="kpi-title">Jours restants</span>
                <span class="kpi-number">{{ chantier.jours_restants ?? '—' }} jours</span>
              </div>
            </div>
          </div>

          <div class="main-project-sidebar">
            <div class="sidebar-box-title">CLIENT</div>
            <h3>{{ chantier.client?.nom_complet || chantier.client?.nom || 'Non renseigné' }}</h3>
            <div class="meta-list">
              <div class="meta-row"><span>Entreprise</span><strong>{{ chantier.client?.entreprise || '—' }}</strong></div>
              <div class="meta-row"><span>Architecte</span><strong>{{ chantier.architecte || 'Non défini' }}</strong></div>
              <div class="meta-row"><span>Début</span><strong>{{ chantier.date_debut }}</strong></div>
              <div class="meta-row border-none"><span>Budget total</span><strong class="text-blue">{{ formatMAD(chantier.budget_total) }}</strong></div>
            </div>
          </div>
        </div>

        <!-- Onglets -->
        <div class="tabs-container">
          <button 
            v-for="tab in tabs" :key="tab.key"
            class="tab-link" :class="{ active: activeTab === tab.key }"
            @click="activeTab = tab.key"
          >
            <span v-html="tab.icon" class="tab-icon-wrapper"></span>
            {{ tab.label }}
          </button>
        </div>

        <!-- ====== ONGLET APERÇU ====== -->
        <div v-if="activeTab === 'apercu'" class="dashboard-grid">
          <!-- Informations du chantier -->
          <div class="grid-card">
            <h3 class="card-title">Informations du chantier</h3>
            <div class="data-table">
              <div class="data-row"><span>Référence</span><strong>{{ chantier.reference }}</strong></div>
              <div class="data-row"><span>Type de chantier</span><strong>{{ chantier.type_label }}</strong></div>
              <div class="data-row"><span>Surface</span><strong>{{ chantier.surface ? chantier.surface + ' m²' : '—' }}</strong></div>
              <div class="data-row"><span>Adresse</span><strong>{{ chantier.adresse || '—' }}</strong></div>
              <div class="data-row"><span>Latitude</span><strong>{{ chantier.latitude || '—' }}</strong></div>
              <div class="data-row"><span>Longitude</span><strong>{{ chantier.longitude || '—' }}</strong></div>
              <div class="data-row"><span>Statut actuel</span><span class="status-badge" :class="statutClass(chantier.statut)">{{ chantier.statut_label }}</span></div>
              <div class="data-row"><span>Date de début</span><strong>{{ chantier.date_debut }}</strong></div>
              <div class="data-row"><span>Date de fin prévue</span><strong>{{ chantier.date_fin_prevue || 'Non définie' }}</strong></div>
              <div class="data-row"><span>Dernière mise à jour</span><span class="text-muted">{{ chantier.updated_at || '—' }}</span></div>
            </div>
          </div>

          <!-- Projets (extrait) -->
          <div class="grid-card">
            <div class="card-header-flex">
              <h3 class="card-title">Projets du chantier ({{ chantier.projets?.length || 0 }})</h3>
            </div>
            <div class="projects-table-wrapper">
              <table class="projects-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Statut</th>
                    <th>Progression</th>
                    <th>Budget</th>
                    <th>Dates</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(p, i) in chantier.projets?.slice(0, 5)" :key="p.id">
                    <td>{{ i + 1 }}</td>
                    <td><strong>{{ p.nom }}</strong></td>
                    <td><span class="status-badge sm" :class="statutClass(p.statut)">{{ p.statut_label || p.statut }}</span></td>
                    <td>
                      <div class="progress-bar-line"><div class="fill" :style="{ width: p.progression + '%' }"></div></div>
                      <span class="pct">{{ p.progression }}%</span>
                    </td>
                    <td>{{ formatMAD(p.budget) }}</td>
                    <td>{{ p.date_debut }} → {{ p.date_fin_prevue }}</td>
                  </tr>
                </tbody>
              </table>
              <p v-if="chantier.projets?.length > 5" class="more-indicator">... et {{ chantier.projets.length - 5 }} autres</p>
            </div>
            <a href="#" @click.prevent="activeTab = 'projets'" class="view-all-link">Voir tous les projets</a>
          </div>

          <!-- Résumé financier -->
          <div class="grid-card financial-card">
            <h3 class="card-title"><span class="title-icon"></span> Résumé financier</h3>
            <div class="financial-summary-box">
              <div class="donut-chart-container">
                <svg width="180" height="180" viewBox="0 0 120 120" class="donut-svg">
                  <circle cx="60" cy="60" r="48" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                  <circle cx="60" cy="60" r="48" fill="none" stroke="#2563eb" stroke-width="10"
                    :stroke-dasharray="`${depensePercent * 3.015} 301.5`" 
                    stroke-linecap="round" class="donut-fill"/>
                  <text x="60" y="55" text-anchor="middle" class="donut-percentage">{{ depensePercent }}%</text>
                  <text x="60" y="74" text-anchor="middle" class="donut-label">Dépensé</text>
                </svg>
              </div>
              <div class="financial-details">
                <div class="fin-item budget-total">
                  <div class="fin-meta">
                    <span class="dot text-slate"></span>
                    <span class="label">Budget total</span>
                  </div>
                  <strong class="value">{{ formatMAD(chantier.budget_total) }}</strong>
                </div>
                <div class="fin-item budget-depense">
                  <div class="fin-meta">
                    <span class="dot bg-blue"></span>
                    <span class="label">Dépensé</span>
                  </div>
                  <strong class="value text-blue">
                    {{ formatMAD(totalCoutReel) }} 
                    <span class="badge-pct">{{ depensePercent }}%</span>
                  </strong>
                </div>
                <div class="fin-item budget-restant">
                  <div class="fin-meta">
                    <span class="dot bg-green"></span>
                    <span class="label">Disponible</span>
                  </div>
                  <strong class="value text-green">{{ formatMAD(chantier.budget_total - totalCoutReel) }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Planning Gantt (mini) -->
          <div class="grid-card col-span-2">
            <h3 class="card-title">Planning Gantt</h3>
            <div class="gantt-container">
              <div class="gantt-months-row">
                <div class="gantt-empty-cell"></div>
                <div class="gantt-months-grid">
                  <span v-for="m in ganttMonthsMini" :key="m.key">{{ m.label }}</span>
                </div>
              </div>
              <div class="gantt-rows-list">
                <div v-for="p in chantier.projets" :key="p.id" class="gantt-bar-row">
                  <div class="gantt-project-label">{{ p.nom }}</div>
                  <div class="gantt-track-field">
                    <div v-if="ganttBarComputed(p).show" class="gantt-bar-fill" :style="ganttBarComputed(p).style">
                      <span class="gantt-bar-label">{{ p.progression }}%</span>
                    </div>
                    <div v-else class="gantt-no-dates">— Dates non définies —</div>
                  </div>
                </div>
                <p v-if="!chantier.projets?.length" class="text-muted" style="padding:0.5rem 0;">Aucun projet pour afficher le planning.</p>
              </div>
            </div>
          </div>

          <!-- Événements (prochains) -->
          <div class="grid-card">
            <div class="card-header-flex">
              <h3 class="card-title">Événements à venir</h3>
              <button class="btn btn-sm btn-outline" @click="activeTab = 'evenements'">Voir tout</button>
            </div>
            <div v-if="upcomingEvents.length > 0" class="events-vertical-list">
              <div v-for="evt in upcomingEvents" :key="evt.id" class="event-card-item">
                <div class="event-date-badge blue-badge">
                  <span>{{ new Date(evt.date).getDate() }}</span>
                  {{ new Date(evt.date).toLocaleDateString('fr-FR', { month: 'short' }) }}
                </div>
                <div class="event-details">
                  <h4>
                    {{ evt.titre }}
                    <span v-if="eventBadgeLabel(evt)" class="mini-badge" :class="eventBadgeLabel(evt).class">{{ eventBadgeLabel(evt).text }}</span>
                  </h4>
                  <p v-if="evt.heure">{{ evt.heure_formatee || evt.heure }}</p>
                  <span class="event-type-badge">{{ evt.type_label }}</span>
                </div>
              </div>
            </div>
            <p v-else class="text-muted" style="font-size:0.9rem;">Aucun événement planifié.</p>
            <a href="#" @click.prevent="activeTab = 'evenements'" class="view-all-link">Gérer les événements</a>
          </div>

          <!-- Documents (extrait) -->
          <div class="grid-card">
            <div class="card-header-flex">
              <h3 class="card-title">Documents ({{ chantier.documents?.length || 0 }})</h3>
            </div>
            <div v-if="chantier.documents?.length" class="documents-list-compact">
              <div v-for="doc in chantier.documents" :key="doc.id" class="document-item-compact">
                <span class="doc-icon">📄</span>
                <div class="doc-info">
                  <strong>{{ doc.nom }}</strong>
                  <span>{{ doc.type }}</span>
                </div>
                <a :href="getDocumentUrl(doc.fichier)" target="_blank" class="doc-link">Voir</a>
              </div>
            </div>
            <p v-else class="text-muted">Aucun document disponible.</p>
            <a href="#" @click.prevent="activeTab = 'documents'" class="view-all-link">Gérer les documents</a>
          </div>

          <!-- Matériaux utilisés (extrait) -->
          <div class="grid-card col-span-2">
            <div class="card-header-flex">
              <h3 class="card-title">Matériaux utilisés ({{ chantier.materiaux?.length || 0 }})</h3>
            </div>
            <div v-if="chantier.materiaux?.length" class="materiaux-compact">
              <div v-for="m in chantier.materiaux.slice(0, 3)" :key="m.produit" class="materiau-item">
                <span class="mat-nom">{{ m.produit }}</span>
                <span class="mat-qte">{{ m.quantite }} {{ m.unite }}</span>
                <span class="mat-cout">{{ formatMAD(m.cout_total) }}</span>
                <div class="action-group">
                  <button 
                    v-if="!m.projet_id" 
                    class="btn btn-sm btn-outline" 
                    @click="returnToStock(m.sortie_id)"
                  >
                    Retourner
                  </button>
                  <button 
                    v-if="!m.projet_id" 
                    class="btn btn-sm btn-primary" 
                    @click="openAffecterModal(m.sortie_id)"
                  >
                    Affecter
                  </button>
                  <span v-else class="text-muted" style="font-size:0.7rem;">Affecté</span>
                </div>
              </div>
              <p v-if="chantier.materiaux.length > 3" class="more-indicator">... et {{ chantier.materiaux.length - 3 }} autres</p>
            </div>
            <p v-else class="text-muted">Aucun matériau enregistré.</p>
            <a href="#" @click.prevent="activeTab = 'materiaux'" class="view-all-link">Voir tous les matériaux</a>
          </div>
        </div>

        <!-- ====== ONGLET PROJETS ====== -->
        <div v-else-if="activeTab === 'projets'" class="tab-content-alternative">
          <div class="projets-full-list">
            <div class="projets-header-actions">
              <h2>Projets du chantier</h2>
              <button class="btn btn-primary" @click="ajouterProjet">+ Ajouter un projet</button>
            </div>
            <div v-if="chantier.projets?.length" class="table-responsive">
              <table class="projets-table-full">
                <thead>
                  <tr>
                    <th>Réf.</th>
                    <th>Nom</th>
                    <th>Statut</th>
                    <th>Progression</th>
                    <th>Budget</th>
                    <th>Coût réel</th>
                    <th>Dates</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in chantier.projets" :key="p.id" @click="voirProjet(p.id)" class="clickable-row">
                    <td>{{ p.reference }}</td>
                    <td><strong>{{ p.nom }}</strong></td>
                    <td><span class="status-badge sm" :class="statutClass(p.statut)">{{ p.statut_label || p.statut }}</span></td>
                    <td>
                      <div class="progress-bar-line"><div class="fill" :style="{ width: p.progression + '%' }"></div></div>
                      <span class="pct">{{ p.progression }}%</span>
                    </td>
                    <td>{{ formatMAD(p.budget) }}</td>
                    <td><strong class="text-blue">{{ formatMAD(p.cout_reel) }}</strong></td>
                    <td>{{ p.date_debut }} → {{ p.date_fin_prevue || '…' }}</td>
                    <td>
                      <button class="btn btn-sm btn-outline" @click.stop="voirProjet(p.id)">Voir</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else>Aucun projet associé à ce chantier.</p>
          </div>
        </div>

        <!-- ====== ONGLET DOCUMENTS ====== -->
        <div v-else-if="activeTab === 'documents'" class="tab-content-alternative">
          <div class="documents-full">
            <div class="section-header-row">
              <h2>Documents du chantier</h2>
              <button class="btn btn-primary" @click="showDocModal = true">+ Ajouter un document</button>
            </div>
            <div v-if="chantier.documents?.length" class="documents-grid">
              <div v-for="doc in chantier.documents" :key="doc.id" class="document-card">
                <div class="doc-card-icon">📄</div>
                <div class="doc-card-info">
                  <strong>{{ doc.nom }}</strong>
                  <span class="doc-type-badge">{{ doc.type }}</span>
                  <span class="doc-meta">{{ doc.taille_format || '' }} • {{ doc.created_at }}</span>
                </div>
                <div class="doc-card-actions">
                  <a :href="getDocumentUrl(doc.fichier)" target="_blank" class="btn btn-sm btn-outline">Voir</a>
                  <button class="btn btn-sm btn-danger" @click="supprimerDocument(doc.id)">Supprimer</button>
                </div>
              </div>
            </div>
            <div v-else class="empty-state-box">
              <span>📂</span>
              <p>Aucun document disponible.</p>
              <button class="btn btn-primary" @click="showDocModal = true">+ Ajouter un document</button>
            </div>
          </div>
        </div>

        <!-- ====== ONGLET GANTT ====== -->
        <div v-else-if="activeTab === 'gantt'" class="tab-content-alternative">
          <div class="gantt-full-section">
            <div class="section-header-row">
              <h2>Planning Gantt</h2>
              <div class="gantt-legend">
                <span v-for="p in chantier.projets" :key="p.id" class="legend-item">
                  <span class="legend-dot" :style="{ background: ganttColor(p.id) }"></span>{{ p.nom }}
                </span>
              </div>
            </div>
            <div class="gantt-full-container">
              <div class="gantt-header">
                <div class="gantt-label-col">Projet</div>
                <div class="gantt-timeline-header">
                  <div
                    v-for="m in ganttMonthsFull" :key="m.key"
                    class="gantt-month-cell"
                    :style="{ width: (100 / ganttMonthsFull.length) + '%' }"
                  >{{ m.label }}</div>
                </div>
              </div>
              <div class="gantt-body">
                <div v-if="!chantier.projets?.length" class="empty-state-box">
                  <p>Aucun projet pour afficher le Gantt.</p>
                </div>
                <div v-for="p in chantier.projets" :key="p.id" class="gantt-row">
                  <div class="gantt-label-col">
                    <div class="gantt-row-title">{{ p.nom }}</div>
                    <div class="gantt-row-sub">{{ p.statut_label || p.statut }}</div>
                  </div>
                  <div class="gantt-track">
                    <div class="gantt-grid-lines">
                      <div
                        v-for="m in ganttMonthsFull" :key="m.key"
                        class="gantt-grid-col"
                        :style="{ width: (100 / ganttMonthsFull.length) + '%' }"
                      ></div>
                    </div>
                    <div v-if="ganttBarComputed(p).show" class="gantt-bar" :style="ganttBarComputed(p).style">
                      <div class="gantt-bar-progress" :style="{ width: p.progression + '%' }"></div>
                      <span class="gantt-bar-text">{{ p.nom }}</span>
                    </div>
                    <div v-else class="gantt-no-dates">— Dates non définies —</div>
                  </div>
                </div>
              </div>
              <div class="gantt-today-line" :style="{ left: todayOffset + 'px' }" title="Aujourd'hui"></div>
            </div>
          </div>
        </div>

        <!-- ====== ONGLET ÉVÉNEMENTS ====== -->
        <div v-else-if="activeTab === 'evenements'" class="tab-content-alternative">
          <div class="events-full-section">
            <div class="section-header-row">
              <h2>Événements du chantier</h2>
              <button class="btn btn-primary" @click="openAddEvent">+ Ajouter un événement</button>
            </div>

            <!-- Filtres -->
            <div class="events-filters">
              <select v-model="eventFilterStatut" class="form-input filter-select">
                <option value="">Tous les statuts</option>
                <option value="a_venir">À venir</option>
                <option value="termine">Terminé</option>
                <option value="annule">Annulé</option>
              </select>
              <select v-model="eventFilterType" class="form-input filter-select">
                <option value="">Tous les types</option>
                <option value="reunion">Réunion</option>
                <option value="livraison">Livraison</option>
                <option value="inspection">Inspection</option>
                <option value="autre">Autre</option>
              </select>
              <span v-if="eventFilterStatut || eventFilterType" class="filter-reset" @click="eventFilterStatut = ''; eventFilterType = ''">
                ✕ Réinitialiser
              </span>
            </div>

            <template v-if="filteredEvents.length > 0">
              <!-- Groupe : Aujourd'hui -->
              <div v-if="groupedFilteredEvents.aujourdhui.length" class="events-group">
                <h3 class="events-group-title">🔴 Aujourd'hui</h3>
                <div class="events-list">
                  <div v-for="evt in groupedFilteredEvents.aujourdhui" :key="evt.id" class="event-card-item">
                    <div class="event-date-badge blue-badge">
                      <span>{{ new Date(evt.date).getDate() }}</span>
                      {{ new Date(evt.date).toLocaleDateString('fr-FR', { month: 'short' }) }}
                    </div>
                    <div class="event-details">
                      <h4>
                        {{ evt.titre }}
                        <span v-if="eventBadgeLabel(evt)" class="mini-badge" :class="eventBadgeLabel(evt).class">{{ eventBadgeLabel(evt).text }}</span>
                      </h4>
                      <p v-if="evt.description">{{ evt.description }}</p>
                      <span class="event-meta">
                        <span class="event-type-badge">{{ evt.type_label }}</span>
                        <span v-if="evt.heure"> • {{ evt.heure_formatee || evt.heure }}</span>
                      </span>
                    </div>
                    <div class="event-actions">
                      <button class="btn btn-sm btn-outline" @click="exportEventToIcs(evt)" title="Exporter au calendrier">📅</button>
                      <button class="btn btn-sm btn-outline" @click="openEditEvent(evt)">Modifier</button>
                      <button class="btn btn-sm btn-danger" @click="deleteEvent(evt.id)">Supprimer</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Groupe : Cette semaine -->
              <div v-if="groupedFilteredEvents.cette_semaine.length" class="events-group">
                <h3 class="events-group-title">🟠 Cette semaine</h3>
                <div class="events-list">
                  <div v-for="evt in groupedFilteredEvents.cette_semaine" :key="evt.id" class="event-card-item">
                    <div class="event-date-badge blue-badge">
                      <span>{{ new Date(evt.date).getDate() }}</span>
                      {{ new Date(evt.date).toLocaleDateString('fr-FR', { month: 'short' }) }}
                    </div>
                    <div class="event-details">
                      <h4>
                        {{ evt.titre }}
                        <span v-if="eventBadgeLabel(evt)" class="mini-badge" :class="eventBadgeLabel(evt).class">{{ eventBadgeLabel(evt).text }}</span>
                      </h4>
                      <p v-if="evt.description">{{ evt.description }}</p>
                      <span class="event-meta">
                        <span class="event-type-badge">{{ evt.type_label }}</span>
                        <span v-if="evt.heure"> • {{ evt.heure_formatee || evt.heure }}</span>
                      </span>
                    </div>
                    <div class="event-actions">
                      <button class="btn btn-sm btn-outline" @click="exportEventToIcs(evt)" title="Exporter au calendrier">📅</button>
                      <button class="btn btn-sm btn-outline" @click="openEditEvent(evt)">Modifier</button>
                      <button class="btn btn-sm btn-danger" @click="deleteEvent(evt.id)">Supprimer</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Groupe : Plus tard -->
              <div v-if="groupedFilteredEvents.plus_tard.length" class="events-group">
                <h3 class="events-group-title">🔵 Plus tard</h3>
                <div class="events-list">
                  <div v-for="evt in groupedFilteredEvents.plus_tard" :key="evt.id" class="event-card-item">
                    <div class="event-date-badge blue-badge">
                      <span>{{ new Date(evt.date).getDate() }}</span>
                      {{ new Date(evt.date).toLocaleDateString('fr-FR', { month: 'short' }) }}
                    </div>
                    <div class="event-details">
                      <h4>{{ evt.titre }}</h4>
                      <p v-if="evt.description">{{ evt.description }}</p>
                      <span class="event-meta">
                        <span class="event-type-badge">{{ evt.type_label }}</span>
                        <span v-if="evt.heure"> • {{ evt.heure_formatee || evt.heure }}</span>
                      </span>
                    </div>
                    <div class="event-actions">
                      <button class="btn btn-sm btn-outline" @click="exportEventToIcs(evt)" title="Exporter au calendrier">📅</button>
                      <button class="btn btn-sm btn-outline" @click="openEditEvent(evt)">Modifier</button>
                      <button class="btn btn-sm btn-danger" @click="deleteEvent(evt.id)">Supprimer</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Groupe : Historique (passés / terminés / annulés) -->
              <div v-if="groupedFilteredEvents.passe.length" class="events-group">
                <h3 class="events-group-title">⚪ Historique</h3>
                <div class="events-list">
                  <div v-for="evt in groupedFilteredEvents.passe" :key="evt.id" class="event-card-item">
                    <div class="event-date-badge" :class="{
                      'green-badge': evt.statut === 'termine',
                      'gray-badge': evt.statut !== 'termine'
                    }">
                      <span>{{ new Date(evt.date).getDate() }}</span>
                      {{ new Date(evt.date).toLocaleDateString('fr-FR', { month: 'short' }) }}
                    </div>
                    <div class="event-details">
                      <h4>{{ evt.titre }}</h4>
                      <p v-if="evt.description">{{ evt.description }}</p>
                      <span class="event-meta">
                        <span class="event-type-badge">{{ evt.type_label }}</span>
                        <span v-if="evt.heure"> • {{ evt.heure_formatee || evt.heure }}</span>
                        <span class="event-status-badge" :class="{
                          'status-avenir': evt.statut === 'a_venir',
                          'status-termine': evt.statut === 'termine',
                          'status-annule': evt.statut === 'annule'
                        }">{{ evt.statut_label }}</span>
                      </span>
                    </div>
                    <div class="event-actions">
                      <button class="btn btn-sm btn-outline" @click="openEditEvent(evt)">Modifier</button>
                      <button class="btn btn-sm btn-danger" @click="deleteEvent(evt.id)">Supprimer</button>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <div v-else class="empty-state-box">
              <span>📅</span>
              <p v-if="eventFilterStatut || eventFilterType">Aucun événement ne correspond aux filtres sélectionnés.</p>
              <p v-else>Aucun événement planifié pour ce chantier.</p>
              <button class="btn btn-primary" @click="openAddEvent">+ Ajouter un événement</button>
            </div>
          </div>
        </div>

        <!-- ====== ONGLET PRODUITS UTILISÉS (MATÉRIAUX) ====== -->
        <div v-else-if="activeTab === 'materiaux'" class="tab-content-alternative">
          <div class="materiaux-list">
            <h2>Produits utilisés</h2>
            <div v-if="chantier.materiaux?.length" class="materiaux-table">
              <table>
                <thead>
                  <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Quantité</th>
                    <th>Unité</th>
                    <th>Coût unitaire</th>
                    <th>Coût total</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in chantier.materiaux" :key="m.produit">
                    <td>{{ m.produit }}</td>
                    <td>{{ m.categorie }}</td>
                    <td>{{ m.quantite }}</td>
                    <td>{{ m.unite }}</td>
                    <td>{{ formatMAD(m.cout_unitaire) }}</td>
                    <td>{{ formatMAD(m.cout_total) }}</td>
                    <td>
                      <div class="action-group">
                        <button 
                          v-if="!m.projet_id" 
                          class="btn btn-sm btn-outline" 
                          @click="returnToStock(m.sortie_id)"
                        >
                          Retourner
                        </button>
                        <button 
                          v-if="!m.projet_id" 
                          class="btn btn-sm btn-primary" 
                          @click="openAffecterModal(m.sortie_id)"
                        >
                          Affecter
                        </button>
                        <span v-else class="text-muted">Affecté</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else>Aucun matériau enregistré.</p>
          </div>
        </div>
      </template>
    </main>

    <!-- Modal Ajouter Document -->
    <div v-if="showDocModal" class="modal-overlay" @click.self="showDocModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>Ajouter un document</h3>
          <button class="modal-close" @click="showDocModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nom du document *</label>
            <input v-model="docForm.nom" class="form-input" placeholder="Ex: Plan de masse, Devis..." />
          </div>
          <div class="form-group">
            <label>Type de document *</label>
            <select v-model="docForm.type" class="form-input">
              <option value="">-- Choisir --</option>
              <option value="Plan">Plan</option>
              <option value="Devis">Devis</option>
              <option value="Contrat">Contrat</option>
              <option value="Facture">Facture</option>
              <option value="Rapport">Rapport</option>
              <option value="Photo">Photo</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label>Fichier *</label>
            <input type="file" @change="onFileChange" class="form-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.zip" />
          </div>
          <p v-if="docError" class="form-error">{{ docError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showDocModal = false">Annuler</button>
          <button class="btn btn-primary" @click="uploadDocument" :disabled="docLoading">
            {{ docLoading ? 'Envoi...' : 'Ajouter' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Affecter à un projet -->
    <div v-if="showAffecterModal" class="modal-overlay" @click.self="showAffecterModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h3>Affecter le produit à un projet</h3>
          <button class="modal-close" @click="showAffecterModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Choisir un projet *</label>
            <select v-model="affecterProjetId" class="form-input">
              <option value="">-- Sélectionner un projet --</option>
              <option v-for="p in chantier.projets" :key="p.id" :value="p.id">
                {{ p.nom }} ({{ p.reference }})
              </option>
            </select>
          </div>
          <p v-if="affecterError" class="form-error">{{ affecterError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showAffecterModal = false">Annuler</button>
          <button class="btn btn-primary" @click="confirmAffecter" :disabled="affecterLoading">
            {{ affecterLoading ? 'Affectation...' : 'Affecter' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Événement -->
    <div v-if="showEventModal" class="modal-overlay" @click.self="showEventModal = false">
      <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
          <h3>{{ isEditingEvent ? 'Modifier' : 'Ajouter' }} un événement</h3>
          <button class="modal-close" @click="showEventModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Titre *</label>
            <input v-model="eventForm.titre" class="form-input" placeholder="Réunion chantier..." />
            <span v-if="eventErrors.titre" class="form-error">{{ eventErrors.titre[0] }}</span>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="eventForm.description" class="form-input" rows="2" placeholder="Détails..."></textarea>
          </div>
          <div class="form-group">
            <label>Date *</label>
            <input v-model="eventForm.date" type="date" class="form-input" />
            <span v-if="eventErrors.date" class="form-error">{{ eventErrors.date[0] }}</span>
          </div>
          <div class="form-group">
            <label>Heure (optionnel)</label>
            <input v-model="eventForm.heure" type="time" class="form-input" />
          </div>
          <div class="form-group">
            <label>Type</label>
            <select v-model="eventForm.type" class="form-input">
              <option value="reunion">Réunion</option>
              <option value="livraison">Livraison</option>
              <option value="inspection">Inspection</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-group" v-if="isEditingEvent">
            <label>Statut</label>
            <select v-model="eventForm.statut" class="form-input">
              <option value="a_venir">À venir</option>
              <option value="termine">Terminé</option>
              <option value="annule">Annulé</option>
            </select>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" v-model="eventForm.rappel" />
              Activer un rappel
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showEventModal = false">Annuler</button>
          <button class="btn btn-primary" @click="saveEvent" :disabled="eventSaving">
            {{ eventSaving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import chantierService from '@/services/chantierService'
import api from '@/services/api'

// ─── Router ──────────────────────────────────────────────
const route = useRoute()
const router = useRouter()
const chantierId = route.params.id

// ─── États généraux ──────────────────────────────────────
const chantier = ref(null)
const loading = ref(true)
const error = ref(null)
const activeTab = ref('apercu')

// ─── Onglets ─────────────────────────────────────────────
const tabs = [
  { key: 'apercu', label: 'Aperçu', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>` },
  { key: 'projets', label: 'Projets', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>` },
  { key: 'documents', label: 'Documents', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>` },
  { key: 'gantt', label: 'Planning (Gantt)', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>` },
  { key: 'evenements', label: 'Événements', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>` },
  { key: 'materiaux', label: 'Produits utilisés', icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>` }
]

// ─── Image selon type ────────────────────────────────────
function getImageForType(type) {
  const images = {
    residentiel: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&auto=format&fit=crop&q=60',
    commercial:  'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&auto=format&fit=crop&q=60',
    industriel:  'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600&auto=format&fit=crop&q=60',
    public:      'https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=600&auto=format&fit=crop&q=60',
  }
  return images[type] || 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&auto=format&fit=crop&q=80'
}

// ─── Documents ────────────────────────────────────────────
const showDocModal = ref(false)
const docForm = reactive({ nom: '', type: '' })
const docFile = ref(null)
const docError = ref('')
const docLoading = ref(false)

function getDocumentUrl(path) {
  if (!path) return '#'
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `http://127.0.0.1:8000/storage/${path}`
}

function onFileChange(e) {
  docFile.value = e.target.files[0] || null
}

async function uploadDocument() {
  docError.value = ''
  if (!docForm.nom) { docError.value = 'Le nom est requis.'; return }
  if (!docForm.type) { docError.value = 'Le type est requis.'; return }
  if (!docFile.value) { docError.value = 'Veuillez sélectionner un fichier.'; return }

  docLoading.value = true
  try {
    const fd = new FormData()
    fd.append('nom', docForm.nom)
    fd.append('type', docForm.type)
    fd.append('fichier', docFile.value)

    const { data } = await api.post(`/admin/chantiers/${chantierId}/documents`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    chantier.value.documents.push(data.data)
    showDocModal.value = false
    docForm.nom = ''
    docForm.type = ''
    docFile.value = null
  } catch (e) {
    docError.value = e.response?.data?.message || 'Erreur lors de l\'envoi.'
  } finally {
    docLoading.value = false
  }
}

async function supprimerDocument(docId) {
  if (!confirm('Supprimer ce document ?')) return
  try {
    await api.delete(`/admin/documents/${docId}`)
    chantier.value.documents = chantier.value.documents.filter(d => d.id !== docId)
  } catch (e) {
    alert('Erreur lors de la suppression du document.')
  }
}

// ─── Affectation matériaux ──────────────────────────────
const showAffecterModal = ref(false)
const affecterSortieId = ref(null)
const affecterProjetId = ref('')
const affecterLoading = ref(false)
const affecterError = ref('')

async function returnToStock(sortieId) {
  if (!confirm('Voulez-vous vraiment retourner ce produit au stock ? Cette action est irréversible.')) return
  try {
    await api.post(`/admin/mouvements/sortie/${sortieId}/retour-stock`)
    await fetchChantier()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors du retour au stock.')
  }
}

function openAffecterModal(sortieId) {
  affecterSortieId.value = sortieId
  affecterProjetId.value = ''
  affecterError.value = ''
  showAffecterModal.value = true
}

async function confirmAffecter() {
  if (!affecterProjetId.value) {
    affecterError.value = 'Veuillez sélectionner un projet.'
    return
  }
  affecterLoading.value = true
  affecterError.value = ''
  try {
    await api.put(`/admin/mouvements/sortie/${affecterSortieId.value}/affecter-projet`, {
      projet_id: affecterProjetId.value
    })
    showAffecterModal.value = false
    await fetchChantier()
  } catch (e) {
    affecterError.value = e.response?.data?.message || 'Erreur lors de l\'affectation.'
  } finally {
    affecterLoading.value = false
  }
}

// ─── Navigation projets ──────────────────────────────────
function voirProjet(projetId) {
  router.push({ name: 'projet-detail', params: { id: projetId } })
}
function ajouterProjet() {
  router.push({ name: 'projet-create', params: { chantierId: chantierId } })
}

// ─── Gestion chantier ────────────────────────────────────
async function fetchChantier() {
  loading.value = true
  error.value = null
  try {
    const { data } = await chantierService.getChantier(chantierId)
    chantier.value = data.data
    // Mise à jour du label "Projets"
    const projetsTab = tabs.find(t => t.key === 'projets')
    if (projetsTab && chantier.value) {
      projetsTab.label = `Projets (${chantier.value.projets?.length || 0})`
    }
  } catch (e) {
    error.value = "Impossible de charger les détails du chantier. Veuillez réessayer."
    console.error(e)
  } finally {
    loading.value = false
  }
}

function editChantier() {
  router.push({ name: 'chantier-edit', params: { id: chantierId } })
}

async function deleteChantier() {
  if (!confirm('Voulez-vous vraiment supprimer ce chantier ?')) return
  try {
    await chantierService.deleteChantier(chantierId)
    router.push({ name: 'chantiers' })
  } catch (e) {
    alert('Erreur lors de la suppression')
  }
}

// ─── Événements ──────────────────────────────────────────
const events = ref([])
const showEventModal = ref(false)
const eventForm = reactive({
  id: null,
  titre: '',
  description: '',
  date: '',
  heure: '',
  type: 'autre',
  statut: 'a_venir',
  rappel: false,
})
const eventErrors = ref({})
const eventSaving = ref(false)
const isEditingEvent = ref(false)

// Filtres de l'onglet Événements
const eventFilterStatut = ref('') // '', 'a_venir', 'termine', 'annule'
const eventFilterType = ref('')   // '', 'reunion', 'livraison', 'inspection', 'autre'

async function fetchEvents() {
  if (!chantierId) return
  try {
    const { data } = await api.get(`/admin/chantiers/${chantierId}/events`)
    events.value = data.data
  } catch (e) {
    console.error('Erreur chargement événements', e)
  }
}

function openAddEvent() {
  isEditingEvent.value = false
  eventForm.id = null
  eventForm.titre = ''
  eventForm.description = ''
  eventForm.date = ''
  eventForm.heure = ''
  eventForm.type = 'autre'
  eventForm.statut = 'a_venir'
  eventForm.rappel = false
  eventErrors.value = {}
  showEventModal.value = true
}

function openEditEvent(event) {
  isEditingEvent.value = true
  Object.assign(eventForm, {
    id: event.id,
    titre: event.titre,
    description: event.description || '',
    date: event.date ? event.date.split('T')[0] : '',
    heure: event.heure || '',
    type: event.type,
    statut: event.statut,
    rappel: event.rappel || false,
  })
  eventErrors.value = {}
  showEventModal.value = true
}

async function saveEvent() {
  eventSaving.value = true
  eventErrors.value = {}
  try {
    const payload = { ...eventForm }
    delete payload.id

    let response
    if (isEditingEvent.value) {
      // ✅ route imbriquée + sécurisée (le backend vérifie que l'événement appartient au chantier)
      response = await api.put(`/admin/chantiers/${chantierId}/events/${eventForm.id}`, payload)
    } else {
      response = await api.post(`/admin/chantiers/${chantierId}/events`, payload)
    }

    if (response.data.success) {
      showEventModal.value = false
      await fetchEvents()
      await fetchChantier() // pour rafraîchir les compteurs si besoin
    }
  } catch (e) {
    if (e.response && e.response.status === 422) {
      eventErrors.value = e.response.data.errors
    } else {
      alert('Erreur lors de la sauvegarde.')
    }
  } finally {
    eventSaving.value = false
  }
}

async function deleteEvent(eventId) {
  if (!confirm('Voulez-vous vraiment supprimer cet événement ?')) return
  try {
    await api.delete(`/admin/chantiers/${chantierId}/events/${eventId}`)
    await fetchEvents()
    await fetchChantier()
  } catch (e) {
    alert('Erreur lors de la suppression.')
  }
}

// Événements filtrés (statut + type), triés par date/heure
const filteredEvents = computed(() => {
  return events.value
    .filter(e => !eventFilterStatut.value || e.statut === eventFilterStatut.value)
    .filter(e => !eventFilterType.value || e.type === eventFilterType.value)
    .sort((a, b) => new Date(a.date + 'T' + (a.heure || '00:00')) - new Date(b.date + 'T' + (b.heure || '00:00')))
})

// Regroupement par période pour un affichage en sections claires
const groupedFilteredEvents = computed(() => {
  const groups = { aujourdhui: [], cette_semaine: [], plus_tard: [], passe: [] }
  const today = new Date(); today.setHours(0, 0, 0, 0)
  const in7days = new Date(today); in7days.setDate(in7days.getDate() + 7)

  filteredEvents.value.forEach(e => {
    const d = new Date(e.date); d.setHours(0, 0, 0, 0)
    if (e.statut !== 'a_venir') {
      groups.passe.push(e)
    } else if (d.getTime() === today.getTime()) {
      groups.aujourdhui.push(e)
    } else if (d > today && d <= in7days) {
      groups.cette_semaine.push(e)
    } else if (d < today) {
      groups.passe.push(e)
    } else {
      groups.plus_tard.push(e)
    }
  })
  return groups
})

// Badge "Aujourd'hui / Demain / Dans X j / En retard" pour un événement à venir
function eventBadgeLabel(evt) {
  if (evt.statut !== 'a_venir') return null
  const d = new Date(evt.date); d.setHours(0, 0, 0, 0)
  const today = new Date(); today.setHours(0, 0, 0, 0)
  const diffDays = Math.round((d - today) / 86400000)

  if (diffDays === 0) return { text: "Aujourd'hui", class: 'badge-today' }
  if (diffDays === 1) return { text: 'Demain', class: 'badge-soon' }
  if (diffDays > 1 && diffDays <= 7) return { text: `Dans ${diffDays} j`, class: 'badge-soon' }
  if (diffDays < 0) return { text: 'En retard', class: 'badge-late' }
  return null
}

// Export d'un événement au format .ics (compatible Google Calendar / Outlook)
function exportEventToIcs(evt) {
  const dateStr = evt.date.replace(/-/g, '')
  const timeStr = (evt.heure_formatee || evt.heure || '09:00').replace(':', '') + '00'
  const dtStart = `${dateStr}T${timeStr}`
  const ics = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'BEGIN:VEVENT',
    `UID:${evt.id}@chantier`,
    `DTSTART:${dtStart}`,
    `SUMMARY:${evt.titre}`,
    `DESCRIPTION:${evt.description || ''}`,
    'END:VEVENT',
    'END:VCALENDAR',
  ].join('\r\n')
  const blob = new Blob([ics], { type: 'text/calendar' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `${evt.titre.replace(/\s+/g, '_')}.ics`
  link.click()
  URL.revokeObjectURL(link.href)
}

// Prochains événements (pour l'aperçu)
const upcomingEvents = computed(() => {
  return events.value
    .filter(e => e.statut === 'a_venir')
    .sort((a, b) => new Date(a.date + 'T' + (a.heure || '00:00')) - new Date(b.date + 'T' + (b.heure || '00:00')))
    .slice(0, 3)
})

// ─── Computed ────────────────────────────────────────────
const totalCoutReel = computed(() => {
  if (!chantier.value?.projets?.length) return 0
  return chantier.value.projets.reduce((sum, p) => sum + (parseFloat(p.cout_reel) || 0), 0)
})

const depensePercent = computed(() => {
  if (!chantier.value?.budget_total || chantier.value.budget_total == 0) return 0
  return Math.min(100, Math.round((totalCoutReel.value / chantier.value.budget_total) * 100))
})

const chantierProgression = computed(() => {
  if (!chantier.value?.projets?.length) return 0
  const total = chantier.value.projets.reduce((sum, p) => sum + (p.progression || 0), 0)
  return Math.round(total / chantier.value.projets.length)
})

// ─── Gantt ────────────────────────────────────────────────
const GANTT_COLORS = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#14b8a6','#ec4899','#f97316']

function ganttColor(id) {
  return GANTT_COLORS[(id || 0) % GANTT_COLORS.length]
}

const ganttRange = computed(() => {
  if (!chantier.value?.projets?.length) {
    const y = new Date().getFullYear()
    return { start: new Date(y, 0, 1), end: new Date(y, 11, 31) }
  }
  const dates = chantier.value.projets.flatMap(p => [
    p.date_debut_raw ? new Date(p.date_debut_raw) : null,
    p.date_fin_raw ? new Date(p.date_fin_raw) : null,
  ]).filter(Boolean)
  if (!dates.length) {
    const y = new Date().getFullYear()
    return { start: new Date(y, 0, 1), end: new Date(y, 11, 31) }
  }
  let start = new Date(Math.min(...dates))
  let end = new Date(Math.max(...dates))
  start = new Date(start.getFullYear(), start.getMonth() - 1, 1)
  end = new Date(end.getFullYear(), end.getMonth() + 2, 0)
  return { start, end }
})

const FR_MONTHS = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc']

const ganttMonthsFull = computed(() => {
  const months = []
  let cur = new Date(ganttRange.value.start.getFullYear(), ganttRange.value.start.getMonth(), 1)
  const end = ganttRange.value.end
  while (cur <= end) {
    months.push({ key: `${cur.getFullYear()}-${cur.getMonth()}`, label: `${FR_MONTHS[cur.getMonth()]} ${cur.getFullYear()}` })
    cur = new Date(cur.getFullYear(), cur.getMonth() + 1, 1)
  }
  return months
})

const ganttMonthsMini = computed(() => {
  const full = ganttMonthsFull.value
  if (full.length <= 12) return full
  const step = Math.ceil(full.length / 12)
  return full.filter((_, i) => i % step === 0)
})

function ganttBarComputed(projet) {
  if (!projet.date_debut_raw) return { show: false }
  const start = new Date(projet.date_debut_raw)
  const end = projet.date_fin_raw ? new Date(projet.date_fin_raw) : new Date(start.getTime() + 30*86400000)
  const rangeStart = ganttRange.value.start
  const rangeEnd = ganttRange.value.end
  const totalMs = rangeEnd - rangeStart
  if (totalMs <= 0) return { show: false }
  const leftPct = Math.max(0, (start - rangeStart) / totalMs * 100)
  const widthPct = Math.min(100 - leftPct, Math.max(1, (end - start) / totalMs * 100))
  const color = ganttColor(projet.id)
  return {
    show: true,
    style: {
      left: leftPct + '%',
      width: widthPct + '%',
      backgroundColor: color,
    }
  }
}

const todayOffset = computed(() => 0)

// ─── Helpers ──────────────────────────────────────────────
function formatMAD(n) {
  if (n === null || n === undefined) return '—'
  return new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(n)
}

function statutClass(statut) {
  const map = {
    'en_cours': 'en-cours',
    'planifie': 'planifie',
    'termine': 'termine',
    'suspendu': 'suspendu',
    'annule': 'annule',
    'non_commence': 'planifie',
    'bloque': 'suspendu',
  }
  return map[statut] || ''
}

// ─── Montage ──────────────────────────────────────────────
onMounted(() => {
  if (chantierId) {
    fetchChantier()
    fetchEvents()
  } else {
    error.value = "Aucun identifiant de chantier fourni."
    loading.value = false
  }
})

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchChantier()
    fetchEvents()
  }
})
</script>

<style scoped>
/* ── DESIGN SYSTEM ET FOND GLOBAL DE L'APPLICATION ──────── */
:focus { outline: none; }

.app-layout-clean {
  min-height: 100vh;
  background-color: #f4f7fc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  margin: -30px !important;
  box-sizing: border-box;
}

.main-content-full {
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  padding: 0 1.5rem;
  box-sizing: border-box;
}

/* ── Top bar ──────────────────────────────────────────────── */
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0;
  padding-top: 0.5rem;
}
.breadcrumb { font-size: 0.85rem; color: #64748b; font-weight: 500; }
.breadcrumb .active { color: #0f172a; font-weight: 600; }
.action-buttons { display: flex; gap: 0.5rem; }

.btn {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  transition: background-color 0.2s;
}
.btn-secondary { background-color: #ffffff; color: #334155; }
.btn-secondary:hover { background-color: #f8fafc; }
.btn-danger { background-color: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
.btn-danger:hover { background-color: #ffe4e6; }
.btn-primary {
  background-color: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}
.btn-primary:hover { background-color: #1d4ed8; }
.btn-sm {
  padding: 0.2rem 0.6rem;
  font-size: 0.75rem;
}
.btn-outline {
  background: transparent;
  border: 1px solid #2563eb;
  color: #2563eb;
}
.btn-outline:hover { background: #2563eb; color: #fff; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── CARTE EN-TÊTE PRINCIPAL ──────────────────────────────── */
.detail-header-card {
  display: grid;
  grid-template-columns: 240px 1fr 320px;
  gap: 1.5rem;
  background-color: #ffffff;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  margin-bottom: 0;
  margin-top: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

@media (max-width: 1024px) {
  .detail-header-card {
    grid-template-columns: 1fr;
  }
  .header-image-container {
    height: 200px !important;
  }
}

.header-image-container {
  width: 100%;
  height: 165px;
  border-radius: 12px;
  overflow: hidden;
}
.header-img { width: 100%; height: 100%; object-fit: cover; }

.header-main-info { display: flex; flex-direction: column; justify-content: space-between; }
.eyebrow { font-size: 0.7rem; font-weight: 700; color: #2563eb; letter-spacing: 0.05em; }
.title-with-badge { display: flex; align-items: center; gap: 1rem; margin: 0.15rem 0; }
.title-with-badge h1 { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }

.status-badge {
  background-color: #ecfdf5; color: #10b981;
  padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;
}
.status-badge.en-cours-sm { background-color: #e0f2fe; color: #0369a1; }

.location-date { display: flex; gap: 1.5rem; font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem; }

/* Mini Grille KPIs Internes */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-top: 0.5rem; }
.kpi-box { background-color: #f8fafc; border-radius: 10px; padding: 0.75rem; border: 1px solid #f1f5f9; }
.kpi-title { font-size: 0.75rem; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px; }
.kpi-number { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
.kpi-number.text-sm { font-size: 0.95rem; }
.kpi-sub { font-size: 0.7rem; color: #94a3b8; display: block; }
.progress-bar-mini { height: 6px; background-color: #e2e8f0; border-radius: 4px; margin-top: 6px; overflow: hidden; }
.progress-fill { height: 100%; background-color: #2563eb; }

/* Volet Latéral Droit (Client) */
.main-project-sidebar {
  border-left: 1px solid #f1f5f9;
  padding-left: 1.5rem;
}
.sidebar-box-title { font-size: 0.7rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em; }
.main-project-sidebar h3 { margin: 0.25rem 0 0.75rem 0; font-size: 1.15rem; color: #0f172a; font-weight: 700; }
.meta-list { display: flex; flex-direction: column; gap: 0.5rem; }
.meta-row { display: flex; justify-content: space-between; font-size: 0.8rem; border-bottom: 1px dashed #f1f5f9; padding-bottom: 4px; }
.meta-row span { color: #64748b; }
.meta-row strong { color: #1e293b; }
.text-blue { color: #2563eb !important; }

/* ── NAV ONGLET ─────────────────────────────────────────────── */
.tabs-container { display: flex; gap: 0.5rem; border-bottom: 1px solid #e2e8f0; margin: 1.5rem 0 1.5rem 0; }
.tab-link {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.75rem 1.2rem; font-size: 0.9rem; font-weight: 600; color: #64748b;
  border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent;
  transition: all 0.2s;
}
.tab-link.active { color: #2563eb; border-bottom-color: #2563eb; }
.tab-link:hover:not(.active) { color: #1e293b; border-bottom-color: #cbd5e1; }

/* ── GRILLE MOSAÏQUE DU DASHBOARD ─────────────────────────── */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

@media (max-width: 900px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  .col-span-2 { grid-column: span 1 !important; }
}

.col-span-2 { grid-column: span 2; }

.grid-card {
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.01);
  width: 100%;
  box-sizing: border-box;
}
.card-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 1rem; }

/* ── Données ────────────────────────────────────────────────── */
.data-table { display: flex; flex-direction: column; gap: 0.75rem; }
.data-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
.data-row span { color: #64748b; }
.data-row strong { color: #1e293b; font-weight: 600; }

.view-all-link {
  text-align: center; font-size: 0.8rem; font-weight: 700; color: #2563eb;
  text-decoration: none; margin-top: 1rem; display: block;
}
.view-all-link:hover { text-decoration: underline; }

/* ── Finances ───────────────────────────────────────────────── */
.financial-summary-box { display: flex; align-items: center; gap: 1.5rem; flex: 1; justify-content: center; }
.financial-details { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; }
.fin-item { display: flex; flex-direction: column; font-size: 0.8rem; }
.fin-item span { color: #64748b; }

/* ── Gantt (mini et full) ──────────────────────────────────── */
.gantt-container {
  width: 100%;
  overflow-x: auto;
}
.gantt-months-row {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 0.5rem;
}
.gantt-empty-cell {
  width: 180px;
  flex-shrink: 0;
}
.gantt-months-grid {
  display: flex;
  flex: 1;
}
.gantt-months-grid span {
  flex: 1;
  text-align: center;
  font-size: 0.7rem;
  color: #94a3b8;
  font-weight: 600;
  padding: 0.3rem 0;
  border-left: 1px solid #f1f5f9;
}
.gantt-rows-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.gantt-bar-row {
  display: flex;
  align-items: center;
  height: 36px;
}
.gantt-project-label {
  width: 180px;
  flex-shrink: 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 0.5rem;
}
.gantt-track-field {
  flex: 1;
  height: 24px;
  background: #f8fafc;
  border-radius: 4px;
  position: relative;
  overflow: hidden;
}
.gantt-bar-fill {
  position: absolute;
  top: 0;
  height: 100%;
  border-radius: 4px;
  display: flex;
  align-items: center;
  overflow: hidden;
}
.gantt-bar-label {
  font-size: 0.65rem;
  font-weight: 700;
  color: #fff;
  padding: 0 6px;
  white-space: nowrap;
}
.gantt-no-dates {
  font-size: 0.75rem;
  color: #cbd5e1;
  padding: 0 0.5rem;
  line-height: 24px;
}

/* ── Gantt Full ── */
.gantt-full-section {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}
.section-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}
.section-header-row h2 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
}
.gantt-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: #475569;
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 3px;
  display: inline-block;
}
.gantt-full-container {
  position: relative;
  width: 100%;
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}
.gantt-header {
  display: flex;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}
.gantt-label-col {
  width: 200px;
  min-width: 200px;
  padding: 0.6rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
  border-right: 1px solid #e2e8f0;
}
.gantt-timeline-header {
  display: flex;
  flex: 1;
}
.gantt-month-cell {
  text-align: center;
  font-size: 0.72rem;
  color: #64748b;
  font-weight: 600;
  padding: 0.4rem 0;
  border-left: 1px solid #e2e8f0;
  white-space: nowrap;
  overflow: hidden;
}
.gantt-body {
  display: flex;
  flex-direction: column;
}
.gantt-row {
  display: flex;
  border-bottom: 1px solid #f1f5f9;
  min-height: 52px;
  align-items: center;
}
.gantt-row:last-child {
  border-bottom: none;
}
.gantt-label-col {
  padding: 0.5rem 0.75rem;
  border-right: 1px solid #e2e8f0;
}
.gantt-row-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #0f172a;
}
.gantt-row-sub {
  font-size: 0.72rem;
  color: #94a3b8;
}
.gantt-track {
  flex: 1;
  position: relative;
  height: 52px;
  display: flex;
  align-items: center;
}
.gantt-grid-lines {
  position: absolute;
  inset: 0;
  display: flex;
  pointer-events: none;
}
.gantt-grid-col {
  height: 100%;
  border-left: 1px solid #f1f5f9;
}
.gantt-bar {
  position: absolute;
  height: 28px;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  align-items: center;
  min-width: 8px;
  z-index: 1;
  box-shadow: 0 1px 4px rgba(0,0,0,.15);
}
.gantt-bar-progress {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background: rgba(0,0,0,.2);
}
.gantt-bar-text {
  position: relative;
  z-index: 2;
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  padding: 0 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.gantt-today-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #ef4444;
  z-index: 5;
  pointer-events: none;
  left: 30%; /* approximatif, à améliorer */
}

/* ── Événements ─────────────────────────────────────────────── */
.events-full-section {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}
.events-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.event-card-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #f8fafc;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  transition: background 0.2s;
}
.event-card-item:hover {
  background: #f1f5f9;
}
.event-date-badge {
  width: 60px;
  text-align: center;
  padding: 6px 8px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.7rem;
  line-height: 1.2;
  flex-shrink: 0;
}
.event-date-badge span {
  display: block;
  font-size: 1.3rem;
  font-weight: 800;
}
.blue-badge   { background: #dbeafe; color: #1e40af; }
.green-badge  { background: #d1fae5; color: #065f46; }
.gray-badge   { background: #f1f5f9; color: #475569; }

.event-details {
  flex: 1;
}
.event-details h4 {
  margin: 0 0 2px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
}
.event-details p {
  margin: 0;
  font-size: 0.8rem;
  color: #475569;
}
.event-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-top: 4px;
  font-size: 0.75rem;
  color: #64748b;
}
.event-type-badge {
  background: #e2e8f0;
  color: #334155;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.7rem;
  text-transform: uppercase;
}
.event-status-badge {
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.7rem;
}
.status-avenir  { background: #dbeafe; color: #1d4ed8; }
.status-termine { background: #d1fae5; color: #065f46; }
.status-annule  { background: #fee2e2; color: #991b1b; }

.event-actions {
  display: flex;
  gap: 0.4rem;
  flex-shrink: 0;
}
.events-vertical-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* ── Filtres événements ─────────────────────────────────── */
.events-filters {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}
.filter-select {
  width: auto;
  min-width: 160px;
}
.filter-reset {
  font-size: 0.8rem;
  font-weight: 600;
  color: #ef4444;
  cursor: pointer;
}
.filter-reset:hover {
  text-decoration: underline;
}

/* ── Groupes d'événements ───────────────────────────────── */
.events-group {
  margin-bottom: 1.5rem;
}
.events-group:last-child {
  margin-bottom: 0;
}
.events-group-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #475569;
  margin: 0 0 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

/* ── Mini badges (aujourd'hui / bientôt / en retard) ────── */
.mini-badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 10px;
  margin-left: 0.5rem;
  vertical-align: middle;
}
.badge-today { background: #fee2e2; color: #b91c1c; }
.badge-soon  { background: #fef3c7; color: #b45309; }
.badge-late  { background: #f1f5f9; color: #475569; }

/* ── Loading ────────────────────────────────────────────────── */
.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px; color: #64748b; font-size: 0.9rem; gap: 1rem; }
.spinner { width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Documents (compact) ───────────────────────────────────── */
.documents-list-compact {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.document-item-compact {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.4rem 0.6rem;
  background: #f8fafc;
  border-radius: 8px;
}
.document-item-compact .doc-info {
  flex: 1;
}
.document-item-compact .doc-info strong {
  display: block;
  font-size: 0.85rem;
}
.document-item-compact .doc-info span {
  font-size: 0.7rem;
  color: #64748b;
}
.document-item-compact .doc-link {
  font-size: 0.75rem;
  font-weight: 600;
  color: #2563eb;
  text-decoration: none;
}

/* ── Matériaux compact ────────────────────────────────────── */
.materiaux-compact {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.materiau-item {
  display: flex;
  justify-content: space-between;
  padding: 0.3rem 0.4rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.85rem;
  align-items: center;
}
.mat-nom { font-weight: 600; color: #0f172a; }
.mat-qte { color: #475569; }
.mat-cout { font-weight: 600; color: #2563eb; }

/* ── Groupes d'actions ─────────────────────────────────────── */
.action-group {
  display: flex;
  gap: 0.3rem;
  align-items: center;
}

/* ── Onglets complets ──────────────────────────────────────── */
.projets-full-list, .documents-full, .materiaux-list, .gantt-full, .evenements-placeholder { background: #fff;border-radius: 16px;padding: 1.5rem;border: 1px solid #e2e8f0;
}

.projets-full-list .projets-header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.projets-full-list .table-responsive {
  overflow-x: auto;
}
.projets-full-list .projets-table-full {width: 100%;border-collapse: collapse;font-size: 0.9rem;
}
.projets-full-list .projets-table-full th {text-align: left;padding: 0.6rem 0.5rem;border-bottom: 2px solid #e2e8f0;
  font-weight: 600;
  color: #475569;
}
.projets-full-list .projets-table-full td { padding: 0.6rem 0.5rem; border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.projets-full-list .clickable-row { cursor: pointer; transition: background 0.1s;
}
.projets-full-list .clickable-row:hover {background-color: #f1f5f9;
}

/* ── Documents full ───────────────────────────────────────── */
.documents-full {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}
.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}
.document-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.doc-card-icon {
  font-size: 2rem;
  flex-shrink: 0;
}
.doc-card-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.doc-card-info strong {
  font-size: 0.9rem;
  color: #0f172a;
}
.doc-type-badge {
  display: inline-block;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  width: fit-content;
}
.doc-meta {
  font-size: 0.75rem;
  color: #94a3b8;
}
.doc-card-actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.empty-state-box {
  text-align: center;
  padding: 3rem;
  color: #64748b;
  background: #f8fafc;
  border-radius: 12px;
  border: 2px dashed #e2e8f0;
}
.empty-state-box span {
  font-size: 3rem;
  display: block;
  margin-bottom: 0.75rem;
}
.empty-state-box h3 {
  margin: 0 0 0.5rem;
  color: #0f172a;
}
.empty-state-box p {
  margin: 0 0 1rem;
}

/* ── Modal ──────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.modal-box {
  background: #fff;
  border-radius: 16px;
  width: 480px;
  max-width: 95vw;
  box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #64748b;
}
.modal-body {
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
}
.form-input {
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #0f172a;
  background: #f8fafc;
}
.form-input:focus {
  outline: 2px solid #2563eb;
  border-color: #2563eb;
}
.form-error {
  color: #e11d48;
  font-size: 0.85rem;
  margin: 0;
}

/* ── Badges statut ──────────────────────────────────────────── */
.status-badge.en-cours { background: #dbeafe; color: #1d4ed8; }
.status-badge.planifie { background: #fef3c7; color: #b45309; }
.status-badge.termine { background: #d1fae5; color: #065f46; }
.status-badge.suspendu { background: #fce4ec; color: #c62828; }
.status-badge.annule { background: #f1f5f9; color: #64748b; }
.status-badge.sm { font-size: 0.65rem; padding: 2px 8px; }
.border-none { border-bottom: none !important; }
.text-muted { color: #94a3b8; }

.financial-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  transition: all 0.3s ease;
}
.financial-card:hover {
  transform: translateY(-2px);
  border-color: #cbd5e1;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
}
.donut-chart-container {
  flex: 0 0 180px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.donut-svg {
  transform: rotate(-90deg);
  transform-origin: center;
  width: 100%;
  height: auto;
}
.donut-fill {
  transition: stroke-dasharray 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.donut-percentage, .donut-label {
  transform: rotate(90deg);
  transform-origin: 60px 60px;
}
.donut-percentage {
  font-size: 22px;
  font-weight: 800;
  fill: #0f172a;
  letter-spacing: -0.04em;
}
.donut-label {
  font-size: 8.5px;
  fill: #94a3b8;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.fin-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.4rem 0.6rem;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}
.fin-item:hover {
  background-color: #f8fafc;
}
.fin-meta {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot.text-slate { background-color: #94a3b8; }
.dot.bg-blue { background-color: #2563eb; }
.dot.bg-green { background-color: #10b981; }
.fin-item .label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
}
.fin-item .value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  padding-left: 0.75rem;
  letter-spacing: -0.01em;
}
.text-green { color: #16a34a !important; }
.badge-pct {
  font-size: 0.7rem;
  font-weight: 700;
  background-color: #eff6ff;
  color: #2563eb;
  padding: 1px 4px;
  border-radius: 4px;
  margin-left: 0.25rem;
}

/* Gestion Responsive */
@media (max-width: 1150px) and (min-width: 900px), (max-width: 480px) {
  .financial-summary-box {
    flex-direction: column;
    text-align: center;
    gap: 1.25rem;
  }
  .donut-chart-container {
    flex: 0 0 160px;
  }
  .fin-item .value {
    padding-left: 0;
  }
  .fin-meta {
    justify-content: center;
  }
}

/* ── Tableau des projets ────────────────────────────────────── */
.projects-table-wrapper {
  overflow-x: auto;
  margin: 0 -0.25rem;
}
.projects-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.projects-table th {
  text-align: left;
  padding: 0.5rem 0.5rem;
  border-bottom: 1px solid #e2e8f0;
  color: #64748b;
  font-weight: 600;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.projects-table td {
  padding: 0.5rem 0.5rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.projects-table tbody tr:hover {
  background-color: #f8fafc;
  transition: background 0.1s;
}
.projects-table .progress-bar-line {
  width: 80px;
  display: inline-block;
  margin-right: 0.4rem;
  vertical-align: middle;
}
.projects-table .pct {
  font-weight: 600;
  font-size: 0.75rem;
  color: #0f172a;
}
.projects-table .status-badge.sm {
  font-size: 0.65rem;
  padding: 2px 8px;
}
.projects-table td:first-child {
  font-weight: 700;
  color: #94a3b8;
  width: 1%;
  white-space: nowrap;
}
.projects-table td:nth-child(2) {
  min-width: 80px;
  padding-right: 0.5rem;
  padding-left: 0.5rem;
}
.projects-table td:nth-child(3) {
  white-space: nowrap;
  padding-left: 0.25rem;
  padding-right: 0.25rem;
}
.projects-table td:nth-child(4) {
  min-width: 100px;
}
.projects-table td:nth-child(5) {
  white-space: nowrap;
  font-weight: 600;
}
.projects-table td:nth-child(6) {
  white-space: nowrap;
  font-size: 0.75rem;
  color: #475569;
}
.more-indicator {
  font-size: 0.8rem;
  color: #94a3b8;
  text-align: center;
  margin: 0.5rem 0 0;
}

/* ── Matériaux full ──────────────────────────────────────────── */
.materiaux-list {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}
.materiaux-list h2 {
  margin: 0 0 1rem;
  font-size: 1.2rem;
}
.materiaux-table table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.materiaux-table th, .materiaux-table td {
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}
.materiaux-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}
</style>