<template>
  <div class="notif-wrapper" ref="wrapperRef">
    <button
      type="button"
      class="notif-btn"
      title="Notifications"
      @click="togglePanel"
      :class="{ active: panelOpen }"
    >
      <Bell class="notif-btn-icon" :stroke-width="2.1" />
      <span v-if="unreadCount > 0" class="notif-count-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>

    <Transition name="notif-fade">
      <div v-if="panelOpen" class="notif-panel">
        <div class="notif-panel-header">
          <div class="notif-panel-title">
            <span class="notif-panel-icon"><Bell :size="15" :stroke-width="2.3" /></span>
            <h4>Notifications</h4>
            <span v-if="unreadCount > 0" class="notif-unread-chip">{{ unreadCount }}</span>
          </div>
          <button
            v-if="unreadCount > 0"
            type="button"
            class="notif-mark-all-btn"
            @click="markAllAsRead"
          >
            Tout marquer comme lu
          </button>
        </div>

        <div class="notif-panel-body">
          <div v-if="loading" class="notif-state">
            <div class="notif-spinner"></div>
            <span>Chargement…</span>
          </div>

          <div v-else-if="!notifications.length" class="notif-state notif-empty">
            <BellOff :size="32" :stroke-width="1.5" />
            <p>Aucune notification pour le moment</p>
          </div>

          <ul v-else class="notif-list">
            <li
              v-for="n in notifications"
              :key="n.id"
              class="notif-item"
              :class="{ unread: !n.read_at }"
              @click="handleClick(n)"
            >
              <span class="notif-item-icon" :style="{ background: getNotifToneStyle(n.type).bg, color: getNotifToneStyle(n.type).fg }">
                <component :is="getNotifIcon(n.type).icon" :size="16" :stroke-width="2.2" />
              </span>
              <div class="notif-item-body">
                <p class="notif-item-title">{{ n.title }}</p>
                <p class="notif-item-message">{{ n.message }}</p>
                <span class="notif-item-time">{{ timeAgo(n.created_at) }}</span>
              </div>
              <span v-if="!n.read_at" class="notif-unread-dot"></span>
            </li>
          </ul>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, BellOff } from 'lucide-vue-next'
import { useNotifications } from '@/composables/useNotifications'
import { getNotifIcon, getNotifToneStyle } from '@/utils/notificationIcons'

const router = useRouter()
const {
  notifications,
  unreadCount,
  loading,
  fetchNotifications,
  markAsRead,
  markAllAsRead,
  ensureInit,
  startPolling,
  stopPolling,
} = useNotifications()

const panelOpen = ref(false)
const wrapperRef = ref(null)

function togglePanel() {
  panelOpen.value = !panelOpen.value
  if (panelOpen.value) {
    fetchNotifications()
  }
}

function closePanel() {
  panelOpen.value = false
}

function handleClickOutside(e) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    closePanel()
  }
}

function handleEscape(e) {
  if (e.key === 'Escape') closePanel()
}

async function handleClick(n) {
  if (!n.read_at) await markAsRead(n.id)
  const link = n.data?.link
  closePanel()
  if (link) router.push(link)
}

function timeAgo(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const diffSec = Math.floor((Date.now() - date.getTime()) / 1000)

  if (diffSec < 60) return "à l'instant"
  if (diffSec < 3600) return `il y a ${Math.floor(diffSec / 60)} min`
  if (diffSec < 86400) return `il y a ${Math.floor(diffSec / 3600)} h`
  if (diffSec < 172800) return 'hier'
  if (diffSec < 604800) return `il y a ${Math.floor(diffSec / 86400)} j`
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

onMounted(() => {
  ensureInit()
  startPolling()
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  stopPolling()
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
/* ──────────────────────────────────────────────────────────────
   IMPORTANT : ce composant est entièrement autonome en styles.
   On ne compte sur AUCUNE classe CSS définie dans le layout parent
   (AdminLayout / Responsablelayout), car le CSS "scoped" de Vue ne
   traverse pas les composants enfants au-delà de leur racine.
   ────────────────────────────────────────────────────────────── */
.notif-wrapper {
  position: relative;
  display: inline-flex;
}

.notif-btn {
  width: 40px;
  height: 40px;
  border-radius: 11px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  position: relative;
  transition: all 0.18s ease;
}
.notif-btn-icon { width: 19px; height: 19px; }

.notif-btn:hover {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #2563eb;
  border-color: #bfdbfe;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px -2px rgba(37, 99, 235, 0.18);
}
.notif-btn.active {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  border-color: #1d4ed8;
  box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.45);
}

.notif-count-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 19px;
  height: 19px;
  padding: 0 4px;
  border-radius: 999px;
  background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
  color: #fff;
  font-size: 0.64rem;
  font-weight: 800;
  line-height: 19px;
  text-align: center;
  border: 2px solid #fff;
  box-shadow: 0 2px 6px rgba(225, 29, 72, 0.45);
}

/* ── PANNEAU ── */
.notif-panel {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 384px;
  max-width: calc(100vw - 2rem);
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #eef1f5;
  box-shadow: 0 24px 55px -15px rgba(15, 23, 42, 0.28), 0 6px 16px rgba(15, 23, 42, 0.07);
  z-index: 200;
  overflow: hidden;
}

.notif-fade-enter-active, .notif-fade-leave-active {
  transition: opacity 0.16s ease, transform 0.16s ease;
}
.notif-fade-enter-from, .notif-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.98);
}

.notif-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1.1rem 1.2rem;
  background: linear-gradient(120deg, #1d4ed8 0%, #2563eb 45%, #3b82f6 100%);
}
.notif-panel-title { display: flex; align-items: center; gap: 0.5rem; }
.notif-panel-icon {
  width: 26px; height: 26px; border-radius: 8px;
  background: rgba(255,255,255,0.18);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
}
.notif-panel-title h4 {
  margin: 0;
  font-size: 0.98rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.01em;
}
.notif-unread-chip {
  font-size: 0.68rem;
  font-weight: 800;
  color: #1d4ed8;
  background: #fff;
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
  min-width: 18px;
  text-align: center;
}
.notif-mark-all-btn {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  font-size: 0.74rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  padding: 0.35rem 0.6rem;
  border-radius: 8px;
  transition: background 0.15s;
}
.notif-mark-all-btn:hover { background: rgba(255,255,255,0.28); }

.notif-panel-body {
  max-height: 420px;
  overflow-y: auto;
}
.notif-panel-body::-webkit-scrollbar { width: 6px; }
.notif-panel-body::-webkit-scrollbar-thumb { background: #dbe4ec; border-radius: 999px; }

.notif-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  padding: 2.75rem 1.5rem;
  color: #94a3b8;
  font-size: 0.85rem;
}
.notif-empty svg { opacity: 0.55; }
.notif-spinner {
  width: 22px; height: 22px;
  border: 2.5px solid #dbeafe;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: notif-spin 0.7s linear infinite;
}
@keyframes notif-spin { to { transform: rotate(360deg); } }

.notif-list { list-style: none; margin: 0; padding: 0.45rem; }

.notif-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem 0.7rem;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.14s;
}
.notif-item:hover { background: #f6f9fc; }
.notif-item.unread { background: #f0f7ff; }
.notif-item.unread:hover { background: #e4f1fd; }

.notif-item-icon {
  flex-shrink: 0;
  width: 36px; height: 36px;
  border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
}

.notif-item-body { flex: 1; min-width: 0; }
.notif-item-title {
  margin: 0 0 0.15rem;
  font-size: 0.83rem;
  font-weight: 650;
  color: #0f172a;
  line-height: 1.3;
}
.notif-item-message {
  margin: 0 0 0.3rem;
  font-size: 0.78rem;
  color: #64748b;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.notif-item-time {
  font-size: 0.7rem;
  color: #94a3b8;
  font-weight: 500;
}

.notif-unread-dot {
  flex-shrink: 0;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  margin-top: 0.35rem;
}

@media (max-width: 480px) {
  .notif-panel {
    position: fixed;
    top: 64px;
    right: 0.75rem;
    left: 0.75rem;
    width: auto;
  }
}
</style>
