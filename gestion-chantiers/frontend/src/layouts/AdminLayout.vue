<template>
  <div class="admin-shell" :class="{ 'sidebar-collapsed': sidebarCollapsed }">

    <!-- ══════════════════════════════════════════
         SIDEBAR (Sticky + défilement interne)
    ══════════════════════════════════════════ -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">

      <!-- Brand -->
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

      <!-- User card -->
      <div class="sidebar-user">
        <div class="user-avatar">{{ initials }}</div>
        <div class="user-info">
          <p class="user-name">{{ auth.user?.nom_complet || 'Utilisateur' }}</p>
          <span class="user-badge">{{ roleLabel }}</span>
        </div>
      </div>

      <!-- Navigation (défilement interne) -->
      <nav class="sidebar-nav">
        <p class="nav-section-label">PRINCIPAL</p>

        <RouterLink v-if="auth.hasPermission('view_dashboard')" to="/admin/dashboard" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </div>
          <span>Tableau de bord</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_users')" to="/admin/utilisateurs" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <span>Utilisateurs</span>
        </RouterLink>

        <p class="nav-section-label">CHANTIERS</p>

        <RouterLink v-if="auth.hasPermission('view_clients')" to="/admin/clients" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
          </div>
          <span>Clients</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_chantiers')" to="/admin/chantiers" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/></svg>
          </div>
          <span>Chantiers</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_projets')" to="/admin/projets" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <span>Projets</span>
        </RouterLink>

        <p class="nav-section-label">STOCK</p>
        <RouterLink v-if="auth.hasPermission('view_fournisseurs')" to="/admin/fournisseurs" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <span>Fournisseurs</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_produits')" to="/admin/produits" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
          </div>
          <span>Produits</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_stocks')" to="/admin/stocks" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
          </div>
          <span>Stocks</span>
        </RouterLink>

        <RouterLink v-if="auth.hasPermission('view_mouvements')" to="/admin/mouvements" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17H16M4 17l4 4M4 17l4-4"/></svg>
          </div>
          <span>Entrées / Sorties</span>
        </RouterLink>

        <p class="nav-section-label">RAPPORTS</p>

        <RouterLink v-if="auth.isAdmin" to="/admin/historique" class="nav-item" active-class="active">
          <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <span>Historique</span>
        </RouterLink>

      </nav>

      <!-- Logout -->
      <div class="sidebar-footer">
        <button class="logout-btn" @click="handleLogout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div v-if="mobileOpen" class="sidebar-overlay" @click="mobileOpen = false" />

    <!-- ══════════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════════ -->
    <div class="main-area">

      <!-- Top bar -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="page-breadcrumb">
            <span class="breadcrumb-root">Gestion</span>
            <svg class="breadcrumb-separator" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
            <span class="breadcrumb-current">{{ pageTitle }}</span>
          </div>
        </div>
        <div class="topbar-right">
          <button class="topbar-icon-btn notif-btn" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
            <span class="notif-dot"></span>
          </button>
          <div class="topbar-avatar" title="Mon profil">{{ initials }}</div>
        </div>
      </header>

      <!-- Page content -->
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

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const sidebarCollapsed = ref(false)
const mobileOpen       = ref(false)

const initials = computed(() => {
  const u = auth.user
  if (!u) return 'GC'
  return `${u.prenom?.[0] ?? ''}${u.nom?.[0] ?? ''}`.toUpperCase()
})

const pageTitles = {
  '/admin/dashboard':      'Tableau de bord',
  '/admin/utilisateurs':   'Utilisateurs',
  '/admin/clients':        'Clients',
  '/admin/chantiers':      'Chantiers',
  '/admin/projets':        'Projets',
  '/admin/produits':       'Catalogue Produits',
  '/admin/stocks':         'Gestion des Stocks',
  '/admin/mouvements':     'Entrées / Sorties / Transferts',
  '/admin/rapports':       'Rapports',
  '/admin/historique':     'Historique des actions',
}
const pageTitle = computed(() => pageTitles[route.path] ?? 'Administration')

const roleLabels = {
  admin: 'Administrateur',
  responsable: 'Responsable',
  chef_projet: 'Chef de projet',
  magasinier: 'Magasinier',
}
const roleLabel = computed(() => roleLabels[auth.role] ?? 'Utilisateur')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
/* ══════════════════════════════════════════
   DESIGN SYSTEM - CLAIR & BLEU PUR
══════════════════════════════════════════ */
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

/* ══════════════════════════════════════════
   SIDEBAR - sticky en haut, hauteur pleine, défilement interne
══════════════════════════════════════════ */
.sidebar {
  position: sticky;
  top: 0;
  align-self: flex-start;       /* pour que le sticky fonctionne dans flex */
  width: var(--sidebar-w);
  height: 100vh;                /* occupe toute la hauteur de la fenêtre */
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  background: var(--bg-sidebar);
  border-right: 1px solid var(--border-color);
  transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;             /* défilement interne si le contenu dépasse */
  overflow-x: hidden;
}

/* Personnalisation de la scrollbar (optionnel) */
.sidebar::-webkit-scrollbar {
  width: 4px;
}
.sidebar::-webkit-scrollbar-thumb {
  background: var(--border-color);
  border-radius: 2px;
}

/* État Réduit (Collapsed) */
.sidebar.collapsed { width: var(--sidebar-collapsed-w); }
.sidebar.collapsed .brand-name,
.sidebar.collapsed .user-info,
.sidebar.collapsed .nav-section-label,
.sidebar.collapsed .nav-badge,
.sidebar.collapsed .sidebar-footer span { opacity: 0; max-width: 0; visibility: hidden; padding: 0; overflow: hidden; }
.sidebar.collapsed .sidebar-user { padding: 1.25rem 0; justify-content: center; }
.sidebar.collapsed .nav-item span { opacity: 0; max-width: 0; visibility: hidden; }
.sidebar.collapsed .collapse-btn svg { transform: rotate(180deg); }

/* Logo de marque */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1.25rem;
  min-height: var(--topbar-h);
  border-bottom: 1px solid var(--border-color);
  background: #f8fafc;
  flex-shrink: 0;
}
.brand-logo {
  width: 34px; height: 34px;
  background: var(--primary-blue);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.brand-logo svg { width: 18px; height: 18px; }
.brand-name { font-size: 1.1rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.02em; white-space: nowrap; }

.collapse-btn {
  background: transparent; border: none; border-radius: 6px;
  width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--text-muted); flex-shrink: 0; transition: all 0.15s;
  margin-left: auto;
}
.collapse-btn:hover { background: var(--primary-cyan); color: var(--primary-blue); }
.collapse-btn svg { width: 16px; height: 16px; transition: transform 0.2s; }

/* Profil Utilisateur */
.sidebar-user {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 1.25rem;
  background: #f4f9fd;
  border-bottom: 1px solid var(--border-color);
  flex-shrink: 0;
}
.user-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--primary-blue);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.user-info { display: flex; flex-direction: column; gap: 0.1rem; }
.user-name { font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin: 0; white-space: nowrap; }
.user-badge {
  font-size: 0.72rem; color: var(--primary-blue); font-weight: 500; white-space: nowrap;
}

/* Navigation - s'étire pour occuper l'espace entre user et footer */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
  /* pas d'overflow ici, c'est le parent qui gère */
}

.nav-section-label {
  font-size: 0.7rem; font-weight: 700; color: var(--text-muted);
  letter-spacing: 0.05em; padding: 1.25rem 0.5rem 0.5rem;
  white-space: nowrap;
}

.nav-item {
  display: flex; align-items: center; gap: 0.85rem;
  padding: 0.7rem 0.75rem; border-radius: 8px;
  text-decoration: none; color: var(--text-sidebar);
  font-size: 0.92rem; font-weight: 500;
  margin-bottom: 4px; transition: all 0.15s ease;
  white-space: nowrap;
}
.nav-item:hover { background: #f4f9fd; color: var(--primary-blue); }
.nav-item.active { background: var(--primary-cyan); color: var(--primary-blue); font-weight: 600; }

.nav-icon {
  width: 20px; height: 20px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: var(--text-muted);
}
.nav-icon svg { width: 18px; height: 18px; }
.nav-item:hover .nav-icon, .nav-item.active .nav-icon { color: var(--primary-blue); }

.nav-badge {
  margin-left: auto; background: #e0f2fe;
  color: var(--primary-blue); font-size: 0.75rem; font-weight: 700;
  border-radius: 6px; padding: 2px 6px; flex-shrink: 0;
}
.nav-item.active .nav-badge { background: var(--primary-blue); color: #fff; }

/* Pied de page (Déconnexion) - toujours en bas du sidebar */
.sidebar-footer {
  padding: 0.75rem;
  border-top: 1px solid var(--border-color);
  flex-shrink: 0;
  background: var(--bg-sidebar); /* pour éviter la transparence lors du scroll */
}
.logout-btn {
  display: flex; align-items: center; gap: 0.85rem;
  width: 100%; padding: 0.7rem 0.75rem; border-radius: 8px;
  border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444;
  font-size: 0.92rem; font-weight: 500; cursor: pointer;
  transition: all 0.15s; white-space: nowrap;
}
.logout-btn:hover { background: #fee2e2; }
.logout-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

/* ══════════════════════════════════════════
   MAIN CONTENT
══════════════════════════════════════════ */
.main-area {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: var(--topbar-h);
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-color);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 1.5rem;
  position: sticky; top: 0; z-index: 50;
}

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

.notif-dot {
  position: absolute; top: 9px; right: 9px;
  width: 7px; height: 7px; border-radius: 50%;
  background: #ef4444; border: 1.5px solid #fff;
}

.topbar-avatar {
  width: 36px; height: 36px; border-radius: 8px;
  background: var(--primary-cyan); color: var(--primary-blue);
  border: 1px solid var(--border-color);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.82rem; font-weight: 700; cursor: pointer;
  transition: all 0.15s;
}
.topbar-avatar:hover { background: var(--primary-blue); color: #fff; }

.page-content {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
  flex: 1;
}

/* Overlay Mobile */
.sidebar-overlay {
  display: none; position: fixed; inset: 0; background: rgba(10, 37, 64, 0.2); backdrop-filter: blur(4px); z-index: 99;
}

/* ══════════════════════════════════════════
   RESPONSIVE (mobile : fixed et overlay)
══════════════════════════════════════════ */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;            /* on repasse en fixed pour l'overlay */
    top: 0;
    left: 0;
    height: 100vh;
    transform: translateX(-100%);
    width: var(--sidebar-w) !important;
    z-index: 100;
    transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .sidebar.mobile-open {
    transform: translateX(0);
  }
  .sidebar-overlay {
    display: block;
  }
  .mobile-menu-btn {
    display: flex;
  }
  .collapse-btn {
    display: none;
  }
}

/* ==========================================================================
   ÉTAT RÉDUIT (COLLAPSED) – adaptations
   ========================================================================== */
.sidebar.collapsed { 
  width: var(--sidebar-collapsed-w); 
}

.sidebar.collapsed .brand-name,
.sidebar.collapsed .user-info,
.sidebar.collapsed .nav-section-label,
.sidebar.collapsed .nav-badge,
.sidebar.collapsed .nav-item span,
.sidebar.collapsed .sidebar-footer span { 
  opacity: 0; 
  max-width: 0; 
  visibility: hidden; 
  padding: 0; 
  margin: 0;
  overflow: hidden; 
}

.sidebar.collapsed .sidebar-brand {
  padding: 0;
  justify-content: center;
}
.sidebar.collapsed .brand-logo {
  display: none;
}
.sidebar.collapsed .sidebar-user { 
  padding: 1.25rem 0; 
  justify-content: center; 
}
.sidebar.collapsed .sidebar-nav {
  padding: 1rem 0.5rem;
}
.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 0.7rem 0;
}
.sidebar.collapsed .sidebar-footer {
  padding: 0.75rem 0.5rem;
}
.sidebar.collapsed .logout-btn {
  justify-content: center;
  padding: 0.7rem 0;
}

.sidebar.collapsed .collapse-btn {
  margin: 0;
}
.sidebar.collapsed .collapse-btn svg { 
  transform: rotate(180deg); 
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1.25rem;
  min-height: var(--topbar-h);
  border-bottom: 1px solid var(--border-color);
  background: #f8fafc;
  transition: all 0.22s ease;
}

.brand-name, .user-info, .nav-item span, .sidebar-footer span {
  transition: opacity 0.15s ease, max-width 0.15s ease;
  opacity: 1;
  max-width: 200px;
}

.collapse-btn {
  background: transparent; 
  border: none; 
  border-radius: 6px;
  width: 28px; 
  height: 28px; 
  display: flex; 
  align-items: center; 
  justify-content: center;
  cursor: pointer; 
  color: var(--text-muted); 
  flex-shrink: 0; 
  transition: all 0.15s;
  margin-left: auto;
}
.collapse-btn:hover { 
  background: var(--primary-cyan); 
  color: var(--primary-blue); 
}
.collapse-btn svg { 
  width: 16px; 
  height: 16px; 
  transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1); 
}
</style>