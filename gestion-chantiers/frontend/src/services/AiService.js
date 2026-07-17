import api from './api'

export default {

  // Fonctionnalité 1 : Chat IA
  getHistory() {
    return api.get('/admin/ia/history')
  },

  clearHistory() {
    return api.delete('/admin/ia/history')
  },

  sendMessage(message) {
    return api.post('/admin/ia/chat', { message })
  },

  // Fonctionnalité 2 : Résumé intelligent d'un chantier
  resumeChantier(chantierId) {
    return api.post(`/admin/ia/chantiers/${chantierId}/resume`)
  },

  // Fonctionnalité 3 : Analyse intelligente du stock
  analyseStock() {
    return api.post('/admin/ia/stock/analyse')
  },
}