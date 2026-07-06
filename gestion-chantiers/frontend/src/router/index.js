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

// Pages pour les rôles (si vous les avez)
import ChefProjetDashboard from '@/views/chef-projet/DashboardView.vue'
import ResponsableDashboard from '@/views/responsable/DashboardView.vue'
import MagasinierDashboard from '@/views/magasinier/DashboardView.vue'

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

  // Zone admin
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', name: 'AdminDashboard', component: DashboardView },
      { path: 'utilisateurs', name: 'AdminUsers', component: UsersView },
      { path: 'clients', name: 'AdminClients', component: ClientsView },
      { path: 'chantiers', name: 'AdminChantiers', component: ChantiersView },
      { path: 'chantiers/:id', name: 'chantier-detail', component: ChantierDetailView, props: true },
      { path: 'chantiers/create', name: 'chantier-create', component: CreateChantier },
      { path: 'chantiers/:id/edit', name: 'chantier-edit', component: ChantierEdit, meta: { requiresAuth: true } },
      { path: 'projets', name: 'AdminProjets', component: ProjetsView },
      { path: 'projets/:id', name: 'projet-detail', component: ProjetDetail },
      { path: 'projets/edit/:id', name: 'projet-edit', component: ProjetEditView },
      { path: 'projets/create', name: 'projet-create', component: ProjetCreateView },
      // Stock
      { path: 'produits', name: 'AdminProduits', component: ProduitsView },
      { path: 'stocks', name: 'AdminStocks', component: StocksView },
      { path: 'mouvements', name: 'AdminMouvements', component: MouvementsView },
      { path: 'rapports', name: 'AdminRapports', component: RapportsView },
      // Profiles (si vous les avez)
      { path: 'users/:id', name: 'user-profile', component: () => import('@/views/admin/UserProfile.vue') },
      { path: 'clients/:id', name: 'client-profile', component: () => import('@/views/admin/ClientProfile.vue') },
      { path: 'fournisseurs', name: 'AdminFournisseurs', component: FournisseursView },
       
    ]
  },

  // Zone chef de projet
  {
    path: '/chef-projet',
    meta: { requiresAuth: true, role: 'chef_projet' },
    children: [
      { path: 'dashboard', name: 'ChefProjetDashboard', component: ChefProjetDashboard },
    ]
  },

  // Zone responsable
  {
    path: '/responsable',
    meta: { requiresAuth: true, role: 'responsable' },
    children: [
      { path: 'dashboard', name: 'ResponsableDashboard', component: ResponsableDashboard },
    ]
  },

  // Zone magasinier
  {
    path: '/magasinier',
    meta: { requiresAuth: true, role: 'magasinier' },
    children: [
      { path: 'dashboard', name: 'MagasinierDashboard', component: MagasinierDashboard },
    ]
  },

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
  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return '/login'
    }
    const requiredRole = to.meta.role
    if (requiredRole && auth.role !== requiredRole && auth.role !== 'admin') {
      return auth.dashboardRoute()
    }
  }
  return true
})

// ✅ EXPORT PAR DÉFAUT – c’est ce qui manquait !
export default router