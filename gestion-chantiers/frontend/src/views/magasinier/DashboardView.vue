<template>
  <div class="dashboard">
    <header class="dash-header mag">
      <div class="dash-brand">
        <div class="dash-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </div>
        <span>GesChantier — <strong>Magasinier</strong></span>
      </div>
      <div class="dash-user">
        <span>{{ auth.user?.nom_complet }}</span>
        <button @click="handleLogout" class="btn-logout">Déconnexion</button>
      </div>
    </header>
    <main class="dash-main">
      <h1>Bienvenue, {{ auth.user?.prenom }} 📦</h1>
      <p class="subtitle">Le catalogue de produits, les niveaux de stock et les transferts apparaîtront ici.</p>
      <div class="placeholder-card">
        <p>🚧 Fonctionnalités en cours de développement...</p>
        <p>Étape 3 & 4 : Catalogue produits, stock et transferts.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
const auth = useAuthStore()
const router = useRouter()
async function handleLogout() { await auth.logout(); router.push('/login') }
</script>

<style scoped>
.dashboard { min-height: 100vh; background: #f1f5f9; display: flex; flex-direction: column; }
.dash-header { color: #fff; padding: 0.9rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
.mag { background: #7c2d12; }
.dash-brand { display: flex; align-items: center; gap: 0.6rem; font-size: 0.95rem; }
.dash-icon { width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fdba74; }
.dash-icon svg { width: 18px; height: 18px; }
.dash-user { display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; }
.btn-logout { padding: 0.4rem 0.9rem; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
.btn-logout:hover { background: rgba(255,255,255,0.22); }
.dash-main { padding: 2rem 1.5rem; flex: 1; max-width: 960px; margin: 0 auto; width: 100%; }
h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 0.4rem; }
.subtitle { color: #64748b; font-size: 0.9rem; margin: 0 0 2rem; }
.placeholder-card { background: #fff; border-radius: 12px; padding: 2rem; text-align: center; color: #64748b; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.placeholder-card p { margin: 0.25rem 0; }
</style>
