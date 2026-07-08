// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import des composants (ils doivent être dans des fichiers séparés)
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Pages admin
import DashboardView from '@/views/admin/DashboardView.vue'
import UsersView from '@/views/admin/UsersView.vue'
import ClientsView from '@/views/admin/ClientsView.vue'
import ChantiersView from '@/views/admin/ChantiersView.vue'
import ChantierDetailView from '@/views/admin/ChantierDetailView.vue'
import CreateChantier from '@/views/admin/CreateChantier.vue'
import ChantierEdit from '@/views/admin/ChantierEdit.vue'
import ProjetsView from '@/views/admin/ProjetsView.vue'
import ProjetDetail from '@/views/admin/ProjetDetail.vue'
import ProjetEditView from '@/views/admin/ProjetEditView.vue'
import ProjetCreateView from '@/views/admin/ProjetCreateView.vue'
import ProduitsView from '@/views/admin/ProduitsView.vue'
import StocksView from '@/views/admin/StocksView.vue'
import MouvementsView from '@/views/admin/MouvementsView.vue'
import RapportsView from '@/views/admin/RapportsView.vue'
import FournisseursView from '@/views/admin/FournisseursView.vue'
import HistoriqueView from '@/views/admin/HistoriqueView.vue'

const routes = [
  // Routes publiques (guest)
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterView,
    meta: { guest: true }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPasswordView,
    meta: { guest: true }
  },

  // Espace applicatif partagé (admin, responsable, chef_projet, magasinier).
  // Chaque enfant déclare la permission requise via meta.permission ;
  // le rôle n'est plus utilisé pour bloquer l'accès, seule la permission
  // compte (un admin a toujours accès à tout).
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', name: 'AdminDashboard', component: DashboardView, meta: { permission: 'view_dashboard' } },
      { path: 'utilisateurs', name: 'AdminUsers', component: UsersView, meta: { permission: 'view_users' } },
      { path: 'clients', name: 'AdminClients', component: ClientsView, meta: { permission: 'view_clients' } },
      { path: 'chantiers', name: 'AdminChantiers', component: ChantiersView, meta: { permission: 'view_chantiers' } },
      { path: 'chantiers/:id', name: 'chantier-detail', component: ChantierDetailView, props: true, meta: { permission: 'view_chantiers' } },
      { path: 'chantiers/create', name: 'chantier-create', component: CreateChantier, meta: { permission: 'create_chantiers' } },
      { path: 'chantiers/:id/edit', name: 'chantier-edit', component: ChantierEdit, meta: { requiresAuth: true, permission: 'edit_chantiers' } },
      { path: 'projets', name: 'AdminProjets', component: ProjetsView, meta: { permission: 'view_projets' } },
      { path: 'projets/:id', name: 'projet-detail', component: ProjetDetail, meta: { permission: 'view_projets' } },
      { path: 'projets/edit/:id', name: 'projet-edit', component: ProjetEditView, meta: { permission: 'edit_projets' } },
      { path: 'projets/create', name: 'projet-create', component: ProjetCreateView, meta: { permission: 'create_projets' } },
      // Stock
      { path: 'produits', name: 'AdminProduits', component: ProduitsView, meta: { permission: 'view_produits' } },
      { path: 'stocks', name: 'AdminStocks', component: StocksView, meta: { permission: 'view_stocks' } },
      { path: 'mouvements', name: 'AdminMouvements', component: MouvementsView, meta: { permission: 'view_mouvements' } },
      { path: 'rapports', name: 'AdminRapports', component: RapportsView, meta: { role: 'admin' } },
      // Profiles (si vous les avez)
      { path: 'users/:id', name: 'user-profile', component: () => import('@/views/admin/UserProfile.vue'), meta: { permission: 'view_users' } },
      { path: 'clients/:id', name: 'client-profile', component: () => import('@/views/admin/ClientProfile.vue'), meta: { permission: 'view_clients' } },
      { path: 'fournisseurs', name: 'AdminFournisseurs', component: FournisseursView, meta: { permission: 'view_fournisseurs' } },
      { path: 'historique', name: 'historique', component: HistoriqueView, meta: { requiresAuth: true, role: 'admin' } },
    ]
  },

  // Anciennes URLs de dashboard par rôle : conservées pour compatibilité
  // (favoris, liens externes...) mais redirigent vers le tableau de bord
  // partagé désormais.
  { path: '/chef-projet/dashboard', redirect: '/admin/dashboard' },
  { path: '/responsable/dashboard', redirect: '/admin/dashboard' },
  { path: '/magasinier/dashboard', redirect: '/admin/dashboard' },

  // Redirections
  { path: '/', redirect: '/login' },
  { path: '/:pathMatch(.*)*', redirect: '/login' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to) => {
  const auth = useAuthStore()
  // Si la route est réservée aux invités et que l'utilisateur est connecté
  if (to.meta.guest && auth.isAuthenticated) {
    return auth.dashboardRoute() // méthode qui redirige vers le dashboard selon le rôle
  }
  // Si la route nécessite une authentification
  if (to.meta.requiresAuth || to.matched.some((r) => r.meta?.requiresAuth)) {
    if (!auth.isAuthenticated) {
      return '/login'
    }

    // Ancien contrôle par rôle exact (ex: pages réservées explicitement à l'admin)
    const requiredRole = to.meta.role
    if (requiredRole && auth.role !== requiredRole && auth.role !== 'admin') {
      return auth.dashboardRoute()
    }

    // Nouveau contrôle par permission (partagé entre les 4 rôles)
    const requiredPermission = to.meta.permission
    if (requiredPermission && !auth.hasPermission(requiredPermission)) {
      return auth.dashboardRoute()
    }
  }
  return true
})

// ✅ EXPORT PAR DÉFAUT – c’est ce qui manquait !
export default router