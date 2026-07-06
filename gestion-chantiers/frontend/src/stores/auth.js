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

  // Route de redirection selon le rôle
  function dashboardRoute() {
    return user.value?.redirect_to ?? '/'
  }

  return {
    user, token,
    isAuthenticated, role, isAdmin, isResponsable, isChefProjet, isMagasinier,
    register, login, logout, fetchMe, dashboardRoute,
  }
})
