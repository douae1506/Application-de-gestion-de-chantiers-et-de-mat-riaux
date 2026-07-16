<template>
  <div class="crm-viewport" v-if="user">

    <!-- TOP ACTION BAR / HEADER -->
    <header class="crm-header">
      <div class="header-identity">
      
        <div class="avatar-genius">
          <img v-if="user.photo_profil" :src="getPhotoUrl(user.photo_profil)" alt="Profil" />
          <span v-else>{{ initials(user) }}</span>
        </div>
        <div>
          <div class="identity-row">
            <h1>{{ user.prenom }} {{ user.nom }}</h1>
            <span class="status-pill" :class="user.est_actif ? 'badge-active' : 'badge-inactive'">
              {{ user.est_actif ? 'Actif' : 'Inactif' }}
            </span>
          </div>
          <p class="subtitle">{{ roleLabel(user.role) }}</p>
        </div>
      </div>

    </header>

    <!-- MAIN CONTENT GRID -->
    <div class="crm-layout">

      <!-- COLONNE PRINCIPALE -->
      <main class="crm-main-content">

        <!-- SECTION : COORDONNÉES & RÔLE -->
        <section class="panel">
          <div class="panel-header">
            <h2>Informations du compte</h2>
          </div>
          <div class="panel-body">
            <div class="data-grid">
              <div class="data-cell">
                <span class="label">Adresse e-mail</span>
                <span class="value email-link">{{ user.email }}</span>
              </div>
              <div class="data-cell">
                <span class="label">Rôle système</span>
                <span class="value font-medium">{{ roleLabel(user.role) }}</span>
              </div>
              <div class="data-cell">
                <span class="label">Téléphone Pro</span>
                <span class="value">{{ user.telephone_pro || '—' }}</span>
              </div>
              <div class="data-cell">
                <span class="label">Téléphone Mobile</span>
                <span class="value">{{ user.telephone_mobile || '—' }}</span>
              </div>
            </div>
          </div>
        </section>

      </main>

      <!-- COLONNE LATÉRALE : ACTIVITÉ -->
      <aside class="crm-sidebar">
        <section class="panel">
          <div class="panel-header">
            <h2>Sécurité & Activité</h2>
          </div>
          <div class="panel-body timeline">
            <div class="timeline-item">
              <div class="timeline-marker" :class="{ 'update-marker': user.est_actif }"></div>
              <div class="timeline-content">
                <span class="label">Dernière connexion</span>
                <span class="value font-medium">{{ formatDate(user.derniere_connexion_at) }}</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <span class="label">Statut d'accès</span>
                <span class="value text-muted">{{ user.est_actif ? 'Autorisé à se connecter' : 'Accès révoqué' }}</span>
              </div>
            </div>
          </div>
        </section>
      </aside>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import userService from '@/services/userService'

const route = useRoute()
const user = ref(null)

onMounted(async () => {
  const res = await userService.getUser(route.params.id)
  user.value = res.data.data
})

function getPhotoUrl(path){
  return `http://127.0.0.1:8000/storage/${path}`
}

function initials(u){
  return (u.prenom?.[0] || '') + (u.nom?.[0] || '')
}

function formatDate(dateString) {
  if (!dateString) return 'Jamais'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function roleLabel(role){
  return {
    admin: 'Administrateur',
    responsable: 'Responsable',
    chef_projet: 'Chef de Projet',
    magasinier: 'Magasinier'
  }[role] || role
}
</script>

<style scoped>
.crm-viewport {
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: #f8fafc; /* Gris ardoise ultra léger */
  color: #0f172a; /* Slate 900 */
  min-height: 100vh;
  padding: 40px 8%;
  box-sizing: border-box;
}

/* --- HEADER --- */
.crm-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 28px;
  margin-bottom: 32px;
}

.header-identity {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-genius {
  width: 64px;
  height: 64px;
  background:linear-gradient(135deg,#0f172a,#1e293b);
  color: #ffffff;
  font-size: 20px;
  font-weight: 600;
  border-radius: 50%; /* Arrondi parfait pour les utilisateurs */
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: -0.02em;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.avatar-genius img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.identity-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.crm-header h1 {
  font-size: 24px;
  font-weight: 600;
  letter-spacing: -0.03em;
  margin: 0;
}

.subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 4px 0 0 0;
}

/* Boutons */
.header-actions {
  display: flex;
  gap: 12px;
}

.btn {
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary {
  background: white;
  color: #334155;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* --- LAYOUT GRID --- */
.crm-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  align-items: start;
}

.crm-main-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* --- PANELS --- */
.panel {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
}

.panel-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  background: #fcfcfd;
}

.panel-header h2 {
  font-size: 14px;
  font-weight: 600;
  color: #334155;
  margin: 0;
  letter-spacing: -0.01em;
}

.panel-body {
  padding: 24px;
}

/* --- GRILLE DE DONNÉES --- */
.data-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
}

.data-cell {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.value {
  font-size: 14px;
  color: #334155;
}

.text-muted {
  color: #94a3b8;
}

.font-medium {
  font-weight: 500;
  color: #0f172a;
}

.email-link {
  color: #059669; /* Accent Émeraude */
  font-weight: 500;
}

/* --- BADGES (Status Pill) --- */
.status-pill {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.badge-active {
  background: #ecfdf5;
  color: #065f46;
}

.badge-inactive {
  background: #fef2f2;
  color: #991b1b;
}

/* --- TIMELINE ACTIVITÉ --- */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.timeline-item {
  display: flex;
  gap: 16px;
}

.timeline-marker {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
  margin-top: 6px;
}

.update-marker {
  background: #059669; /* Émeraude quand l'utilisateur est actif */
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

/* --- RESPONSIVE DESIGN --- */
@media (max-width: 1024px) {
  .crm-viewport { padding: 24px; }
  .crm-layout { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .crm-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .header-actions { width: 100%; }
  .btn { flex: 1; text-align: center; }
}
</style>
