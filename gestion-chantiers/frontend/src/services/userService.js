import api from './api'

export default {

    getUsers(params={}){

        return api.get('/admin/users',{
            params
        })

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