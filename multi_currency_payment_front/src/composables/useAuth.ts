import router from '../router'
import api from '../services/api'

export function useAuth() {
  async function login(email: string, password: string) {
    


       const response = await api.post('/login', {
         email,
         password
       }
       )
       
       localStorage.setItem(
         'token',
         response.data.token
       )       
      
     localStorage.setItem(
      'user',
      JSON.stringify(response.data.user)
    )

       
       return response.data
 
  }

  function logout() {
    localStorage.removeItem('token')
    router.push('/login')
  }

  function isAuthenticated() {
    return !!localStorage.getItem('token')
  }

  return {
    login,
    logout,
    isAuthenticated
  }
}