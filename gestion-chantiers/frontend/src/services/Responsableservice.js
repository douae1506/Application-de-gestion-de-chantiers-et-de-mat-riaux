import api from './api'

export default {
  // 1. Tableau de bord
  getDashboard() {
    return api.get('/responsable/dashboard')
  },

  // 2. Chantiers
  getChantiers(params = {}) {
    return api.get('/responsable/chantiers', { params })
  },
  getChantier(id) {
    return api.get(`/responsable/chantiers/${id}`)
  },

  // 3. Projets
  getProjets() {
    return api.get('/responsable/projets')
  },
  getProjet(id) {
    return api.get(`/responsable/projets/${id}`)
  },
  createProjet(chantierId, data) {
    return api.post(`/responsable/chantiers/${chantierId}/projets`, data)
  },

  // 4. Phases (planification)
  createPhase(projetId, data) {
    return api.post(`/responsable/projets/${projetId}/phases`, data)
  },
  updatePhase(phaseId, data) {
    return api.put(`/responsable/phases/${phaseId}`, data)
  },
  deletePhase(phaseId) {
    return api.delete(`/responsable/phases/${phaseId}`)
  },

  // 5. Avancement
  updateAvancement(phaseId, data) {
    return api.patch(`/responsable/phases/${phaseId}/avancement`, data)
  },

  // 6. Planning
  getPlanning(params = {}) {
    return api.get('/responsable/planning', { params })
  },

  // 7. Consommation
  getConsommation(projetId) {
    return api.get(`/responsable/projets/${projetId}/consommation`)
  },
}