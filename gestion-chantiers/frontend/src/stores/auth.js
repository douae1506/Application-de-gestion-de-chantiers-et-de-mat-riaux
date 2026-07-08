import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

export const useAuthStore = defineStore('auth', () => {
  // --- State ---
  const user  = ref(JSON.parse(localStorage.getItem('user')  ?? 'null'))
  const token = ref(localStorage.getItem('token') ?? null)

  // --- Getters ---
  const isAuthenticated = computed(() => !!token.value)
  const role            = computed(() => user.value?.role ?? null)
  const isAdmin         = computed(() => role.value === 'admin')
  const isResponsable   = computed(() => role.value === 'responsable')
  const isChefProjet    = computed(() => role.value === 'chef_projet')
  const isMagasinier    = computed(() => role.value === 'magasinier')
  const permissions     = computed(() => user.value?.permissions ?? [])

  function hasPermission(permission) {
    if (!permission) return true
    if (role.value === 'admin') return true
    return permissions.value.includes(permission)
  }

  function hasAnyPermission(perms = []) {
    if (!perms.length) return true
    return perms.some((p) => hasPermission(p))
  }

  // --- Helpers ---
  function authHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
    }
  }

  function persist(userData, tokenValue) {
    user.value  = userData
    token.value = tokenValue
    localStorage.setItem('user',  JSON.stringify(userData))
    localStorage.setItem('token', tokenValue)
  }

  function clear() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  // --- Actions ---
  async function register(payload) {
    const res  = await fetch(`${API_BASE}/auth/register`, {
      method:  'POST',
      headers: authHeaders(),
      body:    JSON.stringify(payload),
    })
    const data = await res.json()

    if (!res.ok) throw data   // { message, errors }

    persist(data.user, data.token)
    return data.user
  }

  async function login(email, password) {
    const res  = await fetch(`${API_BASE}/auth/login`, {
      method:  'POST',
      headers: authHeaders(),
      body:    JSON.stringify({ email, password }),
    })
    const data = await res.json()

    if (!res.ok) throw data

    persist(data.user, data.token)
    return data.user
  }

  async function logout() {
    if (token.value) {
      await fetch(`${API_BASE}/auth/logout`, {
        method:  'POST',
        headers: authHeaders(),
      }).catch(() => {})
    }
    clear()
  }

  async function fetchMe() {
    if (!token.value) return null
    const res  = await fetch(`${API_BASE}/auth/me`, { headers: authHeaders() })
    if (!res.ok) { clear(); return null }
    const data = await res.json()
    user.value = data.user
    localStorage.setItem('user', JSON.stringify(data.user))
    return data.user
  }

  // Route de redirection selon le rôle.
  // IMPORTANT : ne fait jamais confiance aveuglément à user.redirect_to
  // (peut être une valeur mise en cache avant une mise à jour des droits).
  // On revalide côté client avec les permissions actuelles ; si elles sont
  // absentes/invalides, on nettoie la session pour éviter toute boucle de
  // redirection infinie entre /login et /admin/dashboard.
  function dashboardRoute() {
    if (!isAuthenticated.value || !user.value) return '/login'
    if (hasPermission('view_dashboard')) return '/admin/dashboard'
    // Session authentifiée mais sans permission connue (cache obsolète,
    // rôle invalide, etc.) : on force une reconnexion propre.
    clear()
    return '/login'
  }

  return {
    user, token,
    isAuthenticated, role, isAdmin, isResponsable, isChefProjet, isMagasinier,
    permissions, hasPermission, hasAnyPermission,
    register, login, logout, fetchMe, dashboardRoute,
  }
})
