<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="toast"
          :class="'toast--' + t.type"
        >
          <svg v-if="t.type === 'success'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          <svg v-else-if="t.type === 'error'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
          <svg v-else viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
          <span>{{ t.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'
const { toasts } = useToast()
</script>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: .75rem 1.1rem;
  border-radius: 12px;
  font-size: .875rem;
  font-weight: 500;
  min-width: 260px;
  max-width: 380px;
  box-shadow: 0 8px 24px rgba(0,0,0,.15);
  pointer-events: all;
}

.toast svg { width: 18px; height: 18px; flex-shrink: 0; }

.toast--success { background: #0f172a; color: #4ade80; }
.toast--success svg { color: #22c55e; }
.toast--error   { background: #0f172a; color: #f87171; }
.toast--error   svg { color: #ef4444; }
.toast--info    { background: #0f172a; color: #93c5fd; }
.toast--info    svg { color: #60a5fa; }

.toast-enter-active, .toast-leave-active { transition: all .3s cubic-bezier(.4,0,.2,1); }
.toast-enter-from { opacity:0; transform: translateX(60px); }
.toast-leave-to   { opacity:0; transform: translateX(60px); }
</style>