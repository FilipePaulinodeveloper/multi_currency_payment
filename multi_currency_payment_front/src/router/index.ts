import { createRouter, createWebHistory } from 'vue-router'
import login from '../views/login.vue'
import Default from '../layouts/default.vue'
import HomeTable from '../views/index.vue'

import PaymentsList from '../views/PaymentsList.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {path: '/login', component: login},
    
     {
        path: '/',
        component: Default,
        children: [
            // 1. Rota principal / Dashboard
            // {
            //     path: '',
            //     name: 'Home',
            //     component: HomeTable,
            //     meta: { requiresAuth: true }
            // },

            {
                path: '/',
                name: 'payments',
                component: PaymentsList,
                meta: { requiresAuth: true }
            },



            // 5. READ (Opcional - Ver detalhes de um único funcionário)
            // {
            //     path: 'employes',
            //     name: 'EmployeesList',
            //     component: EmployeesList, // Componente de detalhes
            //     meta: { requiresAuth: true }
            // },
            // {
            //     path: 'financials-admin',
            //     name: 'financials-admin',
            //     component: FinancialListAdmin, // Componente de detalhes
            //     meta: { requiresAuth: true }
            // }
        ]
        
    }
    // { path: '/about', component: About },
  ],
})
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
    
  if (to.meta.requiresAuth && !token) {    
      return next('/login')
  }

  
  
  next()
})

export default router