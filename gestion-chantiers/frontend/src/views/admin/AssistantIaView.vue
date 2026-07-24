<template>
  <div class="chat-wrap">
    <div class="chat-header">
      <div class="chat-header-title">
        <span class="chat-header-icon">✨</span>
        <div>
          <h1>Assistant IA</h1>
          <p>Posez une question sur vos chantiers, projets, stocks ou mouvements.</p>
        </div>
      </div>
      <button v-if="messages.length" class="chat-clear-btn" @click="resetConversation">
        Nouvelle conversation
      </button>
    </div>

    <div class="chat-body" ref="scrollArea">
      <!-- État vide : suggestions -->
      <div v-if="!messages.length" class="chat-empty">
        <p class="chat-empty-title">Que voulez-vous savoir ?</p>
        <div class="chat-suggestions">
          <button
            v-for="(q, i) in suggestions"
            :key="i"
            class="chat-suggestion-chip"
            @click="send(q)"
          >
            {{ q }}
          </button>
        </div>
      </div>

      <!-- Historique de conversation -->
      <div
        v-for="(msg, i) in messages"
        :key="i"
        class="chat-message"
        :class="msg.role"
      >
        <div class="chat-avatar" :class="msg.role">
          {{ msg.role === 'user' ? initials : '✨' }}
        </div>
        <div class="chat-bubble" :class="{ 'chat-bubble-error': msg.error }">
          <p>{{ msg.content }}</p>
        </div>
      </div>

      <!-- Indicateur de frappe -->
      <div v-if="loading" class="chat-message assistant">
        <div class="chat-avatar assistant">✨</div>
        <div class="chat-bubble chat-typing">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>

    <!-- Suggestions rapides (une fois la conversation démarrée) -->
    <div v-if="messages.length" class="chat-quick-suggestions">
      <button
        v-for="(q, i) in suggestions.slice(0, 4)"
        :key="i"
        class="chat-suggestion-chip small"
        @click="send(q)"
      >
        {{ q }}
      </button>
    </div>

    <form class="chat-input-bar" @submit.prevent="send()">
      <textarea
        v-model="draft"
        rows="1"
        placeholder="Écrivez votre question…"
        @keydown.enter.exact.prevent="send()"
      ></textarea>
      <button type="submit" class="chat-send-btn" :disabled="loading || !draft.trim()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13" />
          <polygon points="22 2 15 22 11 13 2 9 22 2" />
        </svg>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import aiService from '@/services/aiService'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const initials = computed(() => {
  const nom = auth.user?.nom_complet || ''
  return nom
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase())
    .join('') || 'U'
})

const suggestions = [
  'Quels sont les produits en rupture de stock ?',
  'Quels sont les produits sous le seuil minimum ?',
  'Quels sont les chantiers en retard ?',
  'Quel chantier consomme le plus de matériaux ?',
  'Quel est le fournisseur le plus utilisé ?',
  'Combien de projets sont terminés ?',
  'Quels sont les cinq derniers mouvements ?',
  'Quels sont les stocks ayant le plus de produits ?',
  'Quels chantiers sont actuellement en cours ?',
  'Combien de produits avons-nous ?',
]

// Historique affiché dans la page ET envoyé à Laravel pour donner du
// contexte à Gemini sur les échanges précédents.
const messages = ref([])
const draft = ref('')
const loading = ref(false)
const scrollArea = ref(null)

async function scrollToBottom() {
  await nextTick()
  if (scrollArea.value) {
    scrollArea.value.scrollTop = scrollArea.value.scrollHeight
  }
}

async function send(preset) {
  const text = (preset ?? draft.value).trim()
  if (!text || loading.value) return

  messages.value.push({ role: 'user', content: text })
  draft.value = ''
  loading.value = true
  scrollToBottom()

  // On transmet les 10 derniers échanges pour garder le fil de la
  // conversation sans envoyer un historique trop volumineux à Gemini.
  const history = messages.value
    .slice(0, -1)
    .slice(-10)
    .map((m) => ({ role: m.role, content: m.content }))

  try {
    const { data } = await aiService.chat(text, history)
    messages.value.push({ role: 'assistant', content: data.reply })
  } catch (e) {
    messages.value.push({
      role: 'assistant',
      error: true,
      content:
        e.response?.data?.message ||
        "Une erreur est survenue lors de la génération de la réponse. Vérifiez la configuration de la clé Gemini côté serveur.",
    })
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

function resetConversation() {
  messages.value = []
}
</script>

<style scoped>
.chat-wrap {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 140px);
  min-height: 480px;
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #f5f3ff, #eff6ff);
}
.chat-header-title { display: flex; align-items: center; gap: 0.75rem; }
.chat-header-icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem;
  background: linear-gradient(135deg, #7c3aed, #2563eb);
}
.chat-header-title h1 { font-size: 1.1rem; color: #1e293b; margin: 0; }
.chat-header-title p { font-size: 0.82rem; color: #64748b; margin: 0; }
.chat-clear-btn {
  font-size: 0.8rem; font-weight: 600; color: #475569;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  padding: 0.45rem 0.85rem; cursor: pointer; white-space: nowrap;
}
.chat-clear-btn:hover { background: #f8fafc; }

.chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: #f8fafc;
}

.chat-empty { margin: auto 0; text-align: center; }
.chat-empty-title { font-weight: 600; color: #334155; margin-bottom: 1rem; }
.chat-suggestions {
  display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;
  max-width: 640px; margin: 0 auto;
}
.chat-suggestion-chip {
  font-size: 0.82rem; color: #334155; background: #fff;
  border: 1px solid #e2e8f0; border-radius: 999px;
  padding: 0.5rem 0.9rem; cursor: pointer; transition: all .15s;
}
.chat-suggestion-chip:hover { border-color: #a5b4fc; background: #eef2ff; }
.chat-suggestion-chip.small { font-size: 0.76rem; padding: 0.4rem 0.75rem; }

.chat-quick-suggestions {
  display: flex; gap: 0.5rem; overflow-x: auto;
  padding: 0.6rem 1.25rem;
  border-top: 1px solid #e2e8f0;
  background: #fff;
}
.chat-quick-suggestions .chat-suggestion-chip { flex-shrink: 0; }

.chat-message { display: flex; gap: 0.6rem; max-width: 78%; }
.chat-message.user { align-self: flex-end; flex-direction: row-reverse; }
.chat-message.assistant { align-self: flex-start; }

.chat-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
}
.chat-avatar.user { background: #2563eb; color: #fff; }
.chat-avatar.assistant { background: linear-gradient(135deg, #7c3aed, #2563eb); color: #fff; }

.chat-bubble {
  padding: 0.65rem 0.9rem;
  border-radius: 14px;
  font-size: 0.9rem;
  line-height: 1.55;
  white-space: pre-line;
}
.chat-message.user .chat-bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
.chat-message.assistant .chat-bubble { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
.chat-bubble-error { border-color: #fecaca !important; background: #fef2f2 !important; color: #b91c1c !important; }

.chat-typing { display: flex; gap: 4px; align-items: center; padding: 0.85rem 1rem; }
.chat-typing span {
  width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;
  animation: chat-bounce 1.2s infinite ease-in-out;
}
.chat-typing span:nth-child(2) { animation-delay: 0.15s; }
.chat-typing span:nth-child(3) { animation-delay: 0.3s; }
@keyframes chat-bounce { 0%, 60%, 100% { transform: translateY(0); opacity: .5; } 30% { transform: translateY(-5px); opacity: 1; } }

.chat-input-bar {
  display: flex; align-items: flex-end; gap: 0.6rem;
  padding: 0.9rem 1.25rem;
  border-top: 1px solid #e2e8f0;
  background: #fff;
}
.chat-input-bar textarea {
  flex: 1; resize: none; max-height: 120px;
  border: 1px solid #e2e8f0; border-radius: 10px;
  padding: 0.6rem 0.85rem; font-size: 0.9rem; font-family: inherit;
  outline: none;
}
.chat-input-bar textarea:focus { border-color: #93c5fd; }
.chat-send-btn {
  width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: #2563eb; color: #fff; border: none; cursor: pointer;
}
.chat-send-btn svg { width: 18px; height: 18px; }
.chat-send-btn:hover:not(:disabled) { background: #1d4ed8; }
.chat-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 640px) {
  .chat-wrap { height: calc(100vh - 110px); border-radius: 0; }
  .chat-message { max-width: 90%; }
}
</style>
