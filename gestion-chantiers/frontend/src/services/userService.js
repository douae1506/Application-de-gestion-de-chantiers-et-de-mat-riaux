import api from './api'

export default {

    getUsers(params={}){

        return api.get('/admin/users',{
            params
        })

    },

    // Liste allégée des chefs de projet (id, nom, prénom), accessible à
    // l'admin et au responsable (droits create/edit_projets), sans requérir
    // la permission 'view_users' réservée à la gestion des utilisateurs.
    getChefsProjet(){

        return api.get('/admin/users/chefs-projet')

    },
   getUser(id){
    return api.get(`/admin/users/${id}`)
},

    createUser(data){

        return api.post(
            '/admin/users',
            data
        )

    },

    updateUser(id,data){

    data.append('_method','PUT')

    return api.post(
        `/admin/users/${id}`,
        data,
        {
            headers:{
                'Content-Type':'multipart/form-data'
            }
        }
    )

},

    deleteUser(id){
    return api.delete(
        `/admin/users/${id}`
    )
},

    toggleStatus(id){

        return api.patch(
            `/admin/users/${id}/toggle-status`
        )

    }

}