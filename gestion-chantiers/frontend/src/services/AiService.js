import api from './api'

// Tous les appels à Gemini transitent par le backend Laravel : ce service
// n'appelle jamais l'API Gemini directement, la clé API n'existe que
// côté serveur (voir backend/.env : GEMINI_API_KEY).
export default {
  // Fonctionnalité 1 : chat IA
  chat(message, history = []) {
    return api.post('/admin/ai/chat', { message, history })
  },

}
