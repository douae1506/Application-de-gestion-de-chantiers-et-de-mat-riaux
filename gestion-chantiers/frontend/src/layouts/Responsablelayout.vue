<template>
  <div class="admin-shell" :class="{ 'sidebar-collapsed': sidebarCollapsed }">

    <!-- SIDEBAR -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">
      <div class="sidebar-brand">
        <div class="brand-logo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/>
          </svg>
        </div>
        <span class="brand-name">GesChantier</span>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed" title="Réduire le menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 19l-7-7 7-7M20 19l-7-7 7-7"/></svg>
        </button>
      </div>

      <div class="sidebar-user">
        <div class="user-avatar">{{ initials }}</div>
        <div class="user-info">
          <p class="user-name">{{ auth.user?.nom_complet || 'Utilisateur' }}</p>
          <span class="user-badge">Responsable</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <p class="nav-section-label">PRINCIPAL</p>

        <RouterLink to="/responsable/dashboard" class="nav-item" active-class="active">
          <div class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></div>
          <span>Tableau de bord</span>
        </RouterLink>

        <p class="nav-section-label">CHANTIERS</p>

        <RouterLink to="/responsable/chantiers" class="nav-item" active-class="active">
          <div class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/></svg></div>
          <span>Liste des chantiers</span>
        </RouterLink>

        <RouterLink to="/responsable/projets" class="nav-item" active-class="active">
          <div class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
          <span>Mes projets</span>
        </RouterLink>

        <p class="nav-section-label">SUIVI</p>

        <RouterLink to="/responsable/planning" class="nav-item" active-class="active">
          <div class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
          <span>Planning</span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <button class="logout-btn" @click="handleLogout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <div v-if="mobileOpen" class="sidebar-overlay" @click="mobileOpen = false" />

    <div class="main-area">
      <header class="topbar">
        <div class="topbar-left">
          <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="page-breadcrumb">
            <span class="breadcrumb-root">Espace Responsable</span>
            <svg class="breadcrumb-separator" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
            <span class="breadcrumb-current">{{ pageTitle }}</span>
          </div>
        </div>
        <div class="topbar-right">
          <NotificationBell />
          <div class="topbar-avatar" title="Mon profil">{{ initials }}</div>
        </div>
      </header>

      <main class="page-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const sidebarCollapsed = ref(false)
const mobileOpen       = ref(false)

const initials = computed(() => {
  const u = auth.user
  if (!u) return 'RS'
  return `${u.prenom?.[0] ?? ''}${u.nom?.[0] ?? ''}`.toUpperCase()
})

const pageTitles = {
  '/responsable/dashboard': 'Tableau de bord',
  '/responsable/chantiers': 'Chantiers',
  '/responsable/projets':   'Mes projets',
  '/responsable/planning':  'Planning',
}
const pageTitle = computed(() => {
  if (pageTitles[route.path]) return pageTitles[route.path]
  if (route.path.startsWith('/responsable/chantiers/')) return 'Détail du chantier'
  if (route.path.includes('/consommation')) return 'Consommation du projet'
  if (route.path.includes('/projets/')) return 'Détail du projet'
  return 'Espace Responsable'
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-shell {
  --sidebar-w: 260px;
  --sidebar-collapsed-w: 74px;
  --topbar-h: 64px;

  --bg-main: rgb(255, 255, 255);
  --bg-card: #ffffff;
  --bg-sidebar: #ffffff;

  --text-main: #0a2540;
  --text-muted: #639fab;
  --text-sidebar: #0a2540;

  --primary-cyan: #e0f2fe;
  --primary-blue: #0284c7;
  --border-color: #e0f2fe;

  display: flex;
  min-height: 100vh;
  background: var(--bg-main);
  color: var(--text-main);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
}

.sidebar {
  width: var(--sidebar-w);
  min-height: 100vh;
  background: var(--bg-sidebar);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  z-index: 100;
  transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  border-right: 1px solid var(--border-color);
}
.sidebar.collapsed { width: var(--sidebar-collapsed-w); }
.sidebar.collapsed .brand-name,
.sidebar.collapsed .user-info,
.sidebar.collapsed .nav-section-label,
.sidebar.collapsed .nav-item span,
.sidebar.collapsed .sidebar-footer span { opacity: 0; max-width: 0; visibility: hidden; padding: 0; margin: 0; overflow: hidden; }
.sidebar.collapsed .sidebar-brand { padding: 0; justify-content: center; }
.sidebar.collapsed .brand-logo { display: none; }
.sidebar.collapsed .sidebar-user { padding: 1.25rem 0; justify-content: center; }
.sidebar.collapsed .nav-item { justify-content: center; padding: 0.7rem 0; }
.sidebar.collapsed .collapse-btn svg { transform: rotate(180deg); }
.sidebar.collapsed .logout-btn { justify-content: center; padding: 0.7rem 0; }

.sidebar-brand {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0 1.25rem; min-height: var(--topbar-h);
  border-bottom: 1px solid var(--border-color); background: #f8fafc;
  transition: all 0.22s ease;
}
.brand-logo {
  width: 34px; height: 34px; background: var(--primary-blue); border-radius: 8px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff;
}
.brand-logo svg { width: 18px; height: 18px; }
.brand-name { font-size: 1.1rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.02em; white-space: nowrap; transition: opacity 0.15s ease, max-width 0.15s ease; max-width: 200px; }

.collapse-btn {
  background: transparent; border: none; border-radius: 6px;
  width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--text-muted); flex-shrink: 0; transition: all 0.15s; margin-left: auto;
}
.collapse-btn:hover { background: var(--primary-cyan); color: var(--primary-blue); }
.collapse-btn svg { width: 16px; height: 16px; transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1); }

.sidebar-user { display: flex; align-items: center; gap: 0.75rem; padding: 1.25rem; background: #f4f9fd; border-bottom: 1px solid var(--border-color); }
.user-avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--primary-blue); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.user-info { display: flex; flex-direction: column; gap: 0.1rem; transition: opacity 0.15s ease, max-width 0.15s ease; max-width: 200px; }
.user-name { font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin: 0; white-space: nowrap; }
.user-badge { font-size: 0.72rem; color: var(--primary-blue); font-weight: 500; white-space: nowrap; }

.sidebar-nav { flex: 1; padding: 1rem 0.75rem; overflow-y: auto; }
.nav-section-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); letter-spacing: 0.05em; padding: 1.25rem 0.5rem 0.5rem; white-space: nowrap; }
.nav-item { display: flex; align-items: center; gap: 0.85rem; padding: 0.7rem 0.75rem; border-radius: 8px; text-decoration: none; color: var(--text-sidebar); font-size: 0.92rem; font-weight: 500; margin-bottom: 4px; transition: all 0.15s ease; white-space: nowrap; }
.nav-item:hover { background: #f4f9fd; color: var(--primary-blue); }
.nav-item.active { background: var(--primary-cyan); color: var(--primary-blue); font-weight: 600; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--text-muted); }
.nav-icon svg { width: 18px; height: 18px; }
.nav-item:hover .nav-icon, .nav-item.active .nav-icon { color: var(--primary-blue); }
.nav-item span { transition: opacity 0.15s ease, max-width 0.15s ease; max-width: 200px; }

.sidebar-footer { padding: 0.75rem; border-top: 1px solid var(--border-color); }
.logout-btn { display: flex; align-items: center; gap: 0.85rem; width: 100%; padding: 0.7rem 0.75rem; border-radius: 8px; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444; font-size: 0.92rem; font-weight: 500; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
.logout-btn:hover { background: #fee2e2; }
.logout-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
.logout-btn span { transition: opacity 0.15s ease, max-width 0.15s ease; max-width: 200px; }

.main-area { flex: 1; margin-left: var(--sidebar-w); transition: margin-left 0.22s cubic-bezier(0.4, 0, 0.2, 1); min-width: 0; }
.admin-shell.sidebar-collapsed .main-area { margin-left: var(--sidebar-collapsed-w); }

.topbar { height: var(--topbar-h); background: var(--bg-card); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; position: sticky; top: 0; z-index: 50; }
.topbar-left { display: flex; align-items: center; gap: 1rem; }
.mobile-menu-btn { display: none; background: none; border: none; cursor: pointer; color: var(--text-main); padding: 0; }
.mobile-menu-btn svg { width: 22px; height: 22px; }
.page-breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
.breadcrumb-root { color: var(--text-muted); font-weight: 400; }
.breadcrumb-separator { width: 12px; height: 12px; color: var(--text-muted); opacity: 0.8; }
.breadcrumb-current { font-weight: 600; color: var(--text-main); }
.topbar-right { display: flex; align-items: center; gap: 1rem; }
.topbar-icon-btn {
  width: 38px; height: 38px; border-radius: 8px; border: 1px solid var(--border-color);
  background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: var(--text-muted); position: relative; transition: all 0.15s;
}
.topbar-icon-btn:hover { background: var(--primary-cyan); color: var(--primary-blue); border-color: var(--primary-blue); }
.topbar-icon-btn svg { width: 18px; height: 18px; }

.topbar-avatar { width: 36px; height: 36px; border-radius: 8px; background: var(--primary-cyan); color: var(--primary-blue); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.15s; }
.topbar-avatar:hover { background: var(--primary-blue); color: #fff; }

.page-content { padding: 2rem; max-width: 1600px; margin: 0 auto; width: 100%; box-sizing: border-box; }
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(10, 37, 64, 0.2); backdrop-filter: blur(4px); z-index: 99; }

@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; }
  .sidebar.mobile-open { transform: translateX(0); }
  .sidebar-overlay { display: block; }
  .main-area, .admin-shell.sidebar-collapsed .main-area { margin-left: 0; }
  .mobile-menu-btn { display: flex; }
  .collapse-btn { display: none; }
}
</style>