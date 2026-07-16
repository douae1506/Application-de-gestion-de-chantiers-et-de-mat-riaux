import { ref } from 'vue'
import api from '@/services/api'

// ────────────────────────────────────────────────────────────────
// État partagé (module-level) : toutes les instances du composable
// pointent vers les mêmes refs. Ça évite de refaire un appel API
// à chaque fois qu'un composant (cloche du header, widget dashboard…)
// utilise useNotifications(), et garde tout synchronisé.
// ────────────────────────────────────────────────────────────────
const notifications = ref([])
const unreadCount    = ref(0)
const loading        = ref(false)
const initialized    = ref(false)

let pollTimer = null

export function useNotifications() {
  async function fetchNotifications(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/notifications', {
        params: { per_page: params.perPage || 15, ...params }
      })
      notifications.value = data.data || []
      unreadCount.value = data.meta?.unread_count ?? unreadCount.value
    } catch (e) {
      console.error('Erreur chargement notifications', e)
    } finally {
      loading.value = false
    }
  }

  async function fetchUnreadCount() {
    try {
      const { data } = await api.get('/notifications/unread-count')
      unreadCount.value = data.count ?? 0
    } catch (e) {
      // Silencieux : ne pas gêner l'utilisateur si le polling échoue une fois
      console.error('Erreur comptage notifications', e)
    }
  }

  async function markAsRead(id) {
    const notif = notifications.value.find(n => n.id === id)
    if (notif && notif.read_at) return // déjà lue

    try {
      await api.put(`/notifications/${id}/read`)
      if (notif) notif.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (e) {
      console.error('Erreur marquage notification', e)
    }
  }

  async function markAllAsRead() {
    try {
      await api.put('/notifications/read-all')
      notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
      unreadCount.value = 0
    } catch (e) {
      console.error('Erreur marquage global notifications', e)
    }
  }

  // Charge les données une seule fois même si le composable est utilisé
  // dans plusieurs composants montés simultanément.
  function ensureInit() {
    if (initialized.value) return
    initialized.value = true
    fetchNotifications()
  }

  function startPolling(intervalMs = 30000) {
    if (pollTimer) return
    pollTimer = setInterval(fetchUnreadCount, intervalMs)
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  return {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    ensureInit,
    startPolling,
    stopPolling,
  }
}
