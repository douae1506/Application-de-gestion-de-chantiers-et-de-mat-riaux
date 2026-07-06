import api from "./api"

export default {

  getClients(params = {}) {
    return api.get("/admin/clients", { params })
  },

  getClient(id) {
    return api.get(`/admin/clients/${id}`)
  },

  createClient(data) {
    return api.post("/admin/clients", data)
  },

  updateClient(id, data) {
    data.append("_method", "PUT")

    return api.post(`/admin/clients/${id}`, data, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    })
  },

  deleteClient(id) {
    return api.delete(`/admin/clients/${id}`)
  }
}