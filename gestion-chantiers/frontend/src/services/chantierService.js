import api from './api'

export default {

  // ─── Chantiers ────────────────────────────────────────────

  getChantiers(params = {}) {
    return api.get('/admin/chantiers', { params })
  },

  getChantier(id) {
    return api.get(`/admin/chantiers/${id}`)
  },

  createChantier(data) {
    return api.post('/admin/chantiers', data)
  },

  updateChantier(id, data) {
    return api.put(`/admin/chantiers/${id}`, data)
  },

  deleteChantier(id) {
    return api.delete(`/admin/chantiers/${id}`)
  },

  // ─── Projets d'un chantier ────────────────────────────────

  getProjets(chantierId) {
    return api.get(`/admin/chantiers/${chantierId}/projets`)
  },

  createProjet(chantierId, data) {
    return api.post(`/admin/chantiers/${chantierId}/projets`, data)
  },
  async updateChantier(id, data) {
    const response = await api.put(`/admin/chantiers/${id}`, data)
    return response
  }
  
}