<template>
<div class="crm-viewport">
    <!-- CHARGEMENT -->
    <div v-if="loading" class="state-wrapper">
        <div class="loader-circle"></div>
        <p>Traitement des données...</p>
    </div>

    <!-- ERREUR -->
    <div v-else-if="error" class="state-wrapper state-error">
        <p>{{ error }}</p>
        <button class="btn btn-primary" @click="loadClient">Actualiser</button>
    </div>

    <!-- UI PRINCIPALE -->
    <div v-else-if="client" class="crm-container">
        
        <!-- HEADER LUMINEUX -->
        <header class="main-header">
            <div class="user-profile-section">
                <div class="light-avatar">
                    {{ client.prenom?.[0] || '' }}{{ client.nom?.[0] || '' }}
                </div>
                <div class="user-meta">
                    <div class="name-badge-row">
                        <h1>{{ client.prenom }} {{ client.nom }}</h1>
                        <span class="status-pill" :class="getBadgeClass(client.type_client)">
                            {{ client.type_client || 'Standard' }}
                        </span>
                    </div>
                    <p class="company-sub">{{ client.entreprise || 'Compte Personnel' }}</p>
                </div>
            </div>
        </header>

        <!-- GRID INFOS SUPERIEURES (2 COLONNES) -->
        <div class="workspace-grid">
            
            <!-- ZONE PRINCIPALE (INFOS ET ADRESSE) -->
            <div class="content-stream">
                <!-- BLOC : FICHE D'IDENTITÉ -->
                <section class="card-glass">
                    <div class="card-title">
                        <span class="icon-indicator blue-dot"></span>
                        <h2>Coordonnées & Profil</h2>
                    </div>
                    <div class="card-content">
                        <div class="info-grid">
                            <div class="grid-item">
                                <span class="caption">Identité</span>
                                <span class="data text-dark font-semibold">{{ client.nom }} {{ client.prenom }}</span>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Raison Sociale</span>
                                <span class="data">{{ client.entreprise || '—' }}</span>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Courriel</span>
                                <a :href="`mailto:${client.email}`" class="data link-style">{{ client.email || '—' }}</a>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Ligne Directe</span>
                                <span class="data font-medium">{{ client.telephone || '—' }}</span>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Ligne Secondaire</span>
                                <span class="data text-light">{{ client.telephone_secondaire || '—' }}</span>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Dernière Interaction</span>
                                <span class="data">{{ formatDate(client.dernier_contact) }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- BLOC : ADRESSE -->
                <section class="card-glass">
                    <div class="card-title">
                        <span class="icon-indicator purple-dot"></span>
                        <h2>Localisation & Facturation</h2>
                    </div>
                    <div class="card-content">
                        <div class="info-grid grid-split">
                            <div class="grid-item">
                                <span class="caption">Adresse Postale</span>
                                <span class="data">{{ client.adresse || 'Non renseignée' }}</span>
                            </div>
                            <div class="grid-item">
                                <span class="caption">Ville / Code Région</span>
                                <span class="data font-medium">{{ client.ville || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ZONE LATÉRALE (ACTIVITÉ ET NOTES) -->
            <aside class="sidebar-stream">
                <!-- ACTIVITÉ -->
                <section class="card-glass accent-bg">
                    <div class="card-title">
                        <h2>Activité Système</h2>
                    </div>
                    <div class="card-content">
                        <div class="light-timeline">
                            <div class="step-node">
                                <div class="node-dot blue-node"></div>
                                <div class="node-text">
                                    <span class="caption">Création Initialisée</span>
                                    <span class="data font-small">{{ formatDate(client.created_at) }}</span>
                                </div>
                            </div>
                            <div class="step-node">
                                <div class="node-dot sky-node"></div>
                                <div class="node-text">
                                    <span class="caption">Dernière Synchronisation</span>
                                    <span class="data font-small">{{ formatDate(client.updated_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- NOTES -->
                <section class="card-glass">
                    <div class="card-title">
                        <h2>Notes Collaboratives</h2>
                    </div>
                    <div class="card-content">
                        <div class="clean-notebook" :class="{ 'notebook-void': !client.notes }">
                            {{ client.notes || 'Aucun commentaire édité pour ce compte.' }}
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- SECTION CHANTIERS : SORTIE DE LA GRID POUR PRENDRE 100% DE LA PAGE -->
        <section class="card-glass full-width-section">
            <div class="card-title">
                <span class="icon-indicator green-dot"></span>
                <h2>Chantiers de Client</h2>
            </div>
            <div class="card-content no-padding">
                <div v-if="client.chantiers?.length" class="scrollable-table">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width:15%">Référence</th>
                                <th style="width:25%">Intitulé du Projet</th>
                                <th style="width:12%">Ville</th>
                                <th style="width:5%">État</th>
                                <th style="width:18%" class="align-right">Budget</th>
                                <th style="width:15%" class="align-right">Avancement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="chantier in client.chantiers" :key="chantier.id">
                                <td class="ref-code">{{ chantier.reference }}</td>
                                <td class="font-semibold">{{ chantier.nom }}</td>
                                <td>{{ chantier.ville }}</td>
                                <td>
                                    <span class="status-pill" :class="getBadgeClass(chantier.statut)">
                                        {{ chantier.statut }}
                                    </span>
                                </td>
                                <td class="align-right font-semibold text-nowrap">
                                    {{ Number(chantier.budget_total).toLocaleString('fr-MA') }} DH
                                </td>
                                <td class="align-right">
                                    <div class="progress-wrapper">
                                        <span class="progress-val">{{ chantier.progression }}%</span>
                                        <div class="track-bar">
                                            <div class="fill-bar" :style="{ width: chantier.progression + '%' }"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="empty-state-fallback">
                    <p>Aucun dossier de chantier en cours.</p>
                </div>
            </div>
        </section>

    </div>
</div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import clientService from '@/services/clientService'

// 1. Créer les variables
const loading = ref(true)     // pour le chargement
const error = ref(null)       // pour les erreurs
const client = ref(null)      // pour les données du client

const route = useRoute()

// 2. Fonction pour charger le client
const loadClient = async () => {
  loading.value = true
  try {
    const response = await clientService.getClient(route.params.id)
    client.value = response.data.data || response.data
  } catch (err) {
    error.value = "Erreur de chargement"
  } finally {
    loading.value = false
  }
}

// 3. Charger au démarrage
onMounted(() => {
  loadClient()
})

// 4. Vos fonctions existantes (formatDate, getBadgeClass, etc.)
const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR')
}

const getBadgeClass = (type) => {
  // Vos classes existantes
  const classes = {
    'vip': 'badge-vip',
    'pro': 'badge-pro',
    // ... etc
  }
  return classes[type] || 'badge-default'
}
</script>
<style scoped>
/* --- CONFIGURATION DES NUANCES CLAIRES --- */
.crm-viewport {
    background-color: #f4f7fc;
    color: #334155;
    min-height: 100vh;
    padding: 30px max(3%, 16px);
    box-sizing: border-box;
    margin: -30px;
}

.crm-container {
    max-width: 1440px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px; /* Crée l'espace uniforme entre tous les blocs principaux */
}

/* --- ETATS --- */
.state-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    gap: 16px;
    color: #94a3b8;
}
.loader-circle {
    width: 35px;
    height: 35px;
    border: 3px solid #e2e8f0;
    border-top-color: #38bdf8;
    border-radius: 50%;
    animation: turn 0.7s linear infinite;
}
@keyframes turn { to { transform: rotate(360deg); } }

/* --- EN-TÊTE ÉPURÉ --- */
.main-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 20px 28px;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(148, 163, 184, 0.05);
}

.user-profile-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.light-avatar {
    width: 56px;
    height: 56px;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 20px;
    font-weight: 700;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.name-badge-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.main-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.company-sub {
    font-size: 13px;
    color: #64748b;
    margin: 2px 0 0 0;
}

/* --- BOUTONS --- */
.btn {
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-outline {
    background: #ffffff;
    color: #0284c7;
    border: 1px solid #bae6fd;
}
.btn-outline:hover {
    background: #f0f9ff;
    border-color: #0284c7;
}

/* --- GRID SYSTEM --- */
.workspace-grid {
    display: grid;
    grid-template-columns: 2.3fr 1fr;
    gap: 24px;
}

.content-stream, .sidebar-stream {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* --- PANEL & CARTES BLANCHES --- */
.card-glass {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04);
    overflow: hidden;
}

.full-width-section {
    width: 100%; /* Prend toute l'envergure horizontale disponible */
}

.card-glass.accent-bg {
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
}

.card-title {
    padding: 18px 24px 0 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title h2 {
    font-size: 14px;
    font-weight: 700;
    color: #475569;
    margin: 0;
    letter-spacing: -0.01em;
}

.icon-indicator { width: 6px; height: 6px; border-radius: 50%; }
.blue-dot { background: #38bdf8; }
.purple-dot { background: #c084fc; }
.green-dot { background: #34d399; }

.card-content {
    padding: 20px 24px 24px 24px;
}
.card-content.no-padding { padding: 16px 0 0 0; }

/* --- INFORMATION DISPLAY --- */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.info-grid.grid-split { grid-template-columns: repeat(2, 1fr); }

.grid-item { display: flex; flex-direction: column; gap: 4px; }
.caption {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.data { font-size: 14px; color: #475569; }
.text-dark { color: #0f172a; }
.text-light { color: #94a3b8; }
.font-semibold { font-weight: 600; }
.font-medium { font-weight: 500; }

.link-style {
    color: #0284c7;
    text-decoration: none;
    font-weight: 500;
}
.link-style:hover { text-decoration: underline; }

/* --- TABLEAU DES CHANTIERS REVISITÉ (PLEINE LARGEUR) --- */
.scrollable-table {
     overflow-x: auto;
     padding: 0 4px;
 }
.modern-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    min-width: 700px;
}
.modern-table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 14px 16px;
    border-bottom: 2px solid #e9edf4;
    text-align: left;
    white-space: nowrap;
}
.modern-table th.align-right {
  text-align: right;
}

.modern-table td {
    padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  color: #1e293b;
  vertical-align: middle;
  line-height: 1.4;
}
.modern-table td.align-right {
  text-align: right;
}

.modern-table tr:last-child td {  border-bottom: none; }
.modern-table tr:hover { background-color: #f8fafc; }

.ref-code { 
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 12px;
    color: #475569;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-block;
    font-weight: 500;    }
.align-right { text-align: right; }
.text-nowrap {
  white-space: nowrap;
}
/* BARRE DE PROGRESSION CIEL */
.progress-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
}
.progress-val { font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  min-width: 38px;
  text-align: right;}
.track-bar { width: 100px;
  height: 6px;
  background: #e9edf4;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0; }
.fill-bar {  height: 100%;
  background: linear-gradient(90deg, #38bdf8, #0ea5e9);
  border-radius: 8px;
  transition: width 0.4s ease;}

/* --- BLOC NOTES --- */
.clean-notebook {
    background: #fafafa;
    border-left: 3px solid #bae6fd;
    padding: 14px;
    border-radius: 4px 8px 8px 4px;
    font-size: 13.5px;
    line-height: 1.5;
    color: #475569;
}
.clean-notebook.notebook-void {
    border-left-color: #cbd5e1;
    color: #94a3b8;
    font-style: italic;
}

/* --- TIMELINE --- */
.light-timeline { display: flex; flex-direction: column; gap: 16px; }
.step-node { display: flex; gap: 12px; align-items: flex-start; }
.node-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; }
.blue-node { background: #0284c7; box-shadow: 0 0 0 4px #e0f2fe; }
.sky-node { background: #38bdf8; box-shadow: 0 0 0 4px #f0f9ff; }
.node-text { display: flex; flex-direction: column; }
.font-small { font-size: 13px; color: #334155; font-weight: 500; }

/* --- BADGES --- */
.status-pill {
    display: inline-block;
  padding: 4px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
  letter-spacing: 0.02em;
}
.badge-default { background: #f1f5f9; color: #475569; }
.badge-vip { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
.badge-pro { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
.badge-lead { background: #f0f9ff; color: #0369a1; border: 1px solid #e0f2fe; }
.badge-en_cours { background: #f0f9ff; color: #0369a1; }
.badge-termine { background: #f0fdf4; color: #15803d; }

.empty-state-fallback { 
    padding: 40px 24px;
    text-align: center;
    color: #94a3b8;
    font-style: italic;
    font-size: 14px; 
}

/* --- RESPONSIVE DESIGN --- */
@media (max-width: 1150px) {
    .workspace-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .main-header { flex-direction: column; align-items: flex-start; gap: 14px; }
    .action-block { width: 100%; }
    .btn { width: 100%; text-align: center; }
    .info-grid.grid-split { grid-template-columns: 1fr; }
}
</style>
