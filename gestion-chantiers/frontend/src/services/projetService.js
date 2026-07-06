import api from './api'

export default {
    getProjet(id) {
        return api.get(`/admin/projets/${id}`)
    },
    getProjets() {
        return api.get(`/admin/projets`)
    },
    createProjet(data) {
        return api.post(`/admin/projets`, data)
    },
    updateProjet(id, data) {
        return api.put(`/admin/projets/${id}`, data)
    },
    deleteProjet(id) {
        return api.delete(`/admin/projets/${id}`)
    }
}