<template>
  <div class="notif-wrapper" ref="wrapperRef">
    <button
      class="topbar-icon-btn notif-btn"
      title="Notifications"
      @click="togglePanel"
      :class="{ active: panelOpen }"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      <span v-if="unreadCount > 0" class="notif-count-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>

    <Transition name="notif-fade">
      <div v-if="panelOpen" class="notif-panel">
        <div class="notif-panel-header">
          <div class="notif-panel-title">
            <h4>Notifications</h4>
            <span v-if="unreadCount > 0" class="notif-unread-chip">{{ unreadCount }} non lue{{ unreadCount > 1 ? 's' : '' }}</span>
          </div>
          <button
            v-if="unreadCount > 0"
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
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
              <span class="notif-item-icon">{{ n.icon || '🔔' }}</span>
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
import { useNotifications } from '@/composables/useNotifications'

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
.notif-wrapper {
  position: relative;
}

.notif-btn.active {
  background: var(--primary-cyan, #e0f2fe);
  color: var(--primary-blue, #0284c7);
  border-color: var(--primary-blue, #0284c7);
}

.notif-count-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  line-height: 18px;
  text-align: center;
  border: 1.5px solid #fff;
  box-shadow: 0 1px 3px rgba(239, 68, 68, 0.4);
}

/* ── PANNEAU ── */
.notif-panel {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 380px;
  max-width: calc(100vw - 2rem);
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e6edf3;
  box-shadow: 0 20px 45px -12px rgba(10, 37, 64, 0.22), 0 4px 12px rgba(10, 37, 64, 0.06);
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
  padding: 1rem 1.15rem;
  background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
  border-bottom: 1px solid #eef2f6;
}
.notif-panel-title { display: flex; align-items: center; gap: 0.55rem; }
.notif-panel-title h4 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #0a2540;
  letter-spacing: -0.01em;
}
.notif-unread-chip {
  font-size: 0.68rem;
  font-weight: 700;
  color: #0284c7;
  background: #e0f2fe;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}
.notif-mark-all-btn {
  background: none;
  border: none;
  color: #0284c7;
  font-size: 0.76rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  padding: 0.25rem 0.4rem;
  border-radius: 6px;
  transition: background 0.15s;
}
.notif-mark-all-btn:hover { background: #e0f2fe; }

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
  padding: 2.5rem 1.5rem;
  color: #94a3b8;
  font-size: 0.85rem;
}
.notif-empty svg { width: 34px; height: 34px; opacity: 0.5; }
.notif-spinner {
  width: 22px; height: 22px;
  border: 2.5px solid #e0f2fe;
  border-top-color: #0284c7;
  border-radius: 50%;
  animation: notif-spin 0.7s linear infinite;
}
@keyframes notif-spin { to { transform: rotate(360deg); } }

.notif-list { list-style: none; margin: 0; padding: 0.4rem; }

.notif-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem 0.7rem;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.14s;
}
.notif-item:hover { background: #f6f9fc; }
.notif-item.unread { background: #f3f9ff; }
.notif-item.unread:hover { background: #e9f4fd; }

.notif-item-icon {
  flex-shrink: 0;
  width: 36px; height: 36px;
  border-radius: 10px;
  background: #eef4fa;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.05rem;
}

.notif-item-body { flex: 1; min-width: 0; }
.notif-item-title {
  margin: 0 0 0.15rem;
  font-size: 0.83rem;
  font-weight: 650;
  color: #0a2540;
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
  background: #0284c7;
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