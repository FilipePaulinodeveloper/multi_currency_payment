import axios from 'axios'
import router from '../router'
const baseURL = import.meta.env.VITE_API_BASE_URL
const api = axios.create({
  baseURL: baseURL
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  
  

  return config
})

api.interceptors.response.use(
  response => response,

  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')

      router.push('/login')
    }

    return Promise.reject(error)
  }
)


export default api