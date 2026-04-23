import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './assets/main.css'
import { createPinia } from 'pinia'
import axios from 'axios'

import Dashboard from './views/Dashboard.vue'
import Publications from './views/Publications.vue'
import Faculty from './views/Faculty.vue'
import Login from './views/Login.vue'
import AuditLogs from './views/AuditLogs.vue'
import AddUser from './views/AddUser.vue'
import Acknowledgements from './views/Acknowledgements.vue'
import AuthorMatches from './views/AuthorMatches.vue'
import Authors from './views/Authors.vue'
import Approval from './views/Approval.vue'
import { useAuth } from './composables/useAuth'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard },
  { path: '/publications', component: Publications },
  { path: '/faculty', component: Faculty },
  { path: '/audit-logs', component: AuditLogs, meta: { requiresAuth: true } },
  { path: '/authors', component: Authors, meta: { requiresAuth: true } },
  { path: '/author-matches', component: AuthorMatches, meta: { requiresAuth: true } },
  { path: '/approval', component: Approval, meta: { requiresAuth: true } },
  { path: '/acknowledgements', component: Acknowledgements },
  { path: '/add-user', component: AddUser, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

axios.defaults.withCredentials = true

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost/Repo_system-main/backend/public/api'
const { setUser, clearUser } = useAuth()
let isHandlingUnauthorized = false

axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error?.response?.status
    const requestUrl = String(error?.config?.url || '')
    const isLoginRequest = requestUrl.includes('/auth/login')
    const isAuthCheck = requestUrl.includes('/auth/me')

    if (status === 401 && !isLoginRequest && !isAuthCheck) {
      clearUser()
      if (router.currentRoute.value.path !== '/login' && !isHandlingUnauthorized) {
        isHandlingUnauthorized = true
        try {
          await router.push('/login')
        } finally {
          isHandlingUnauthorized = false
        }
      }
    }
    return Promise.reject(error)
  }
)

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true

  try {
    const response = await axios.get(`${apiBase}/auth/me`)
    if (response?.data?.data) {
      setUser(response.data.data)
    }
    return true
  } catch {
    clearUser()
    return { path: '/login' }
  }

})

const app = createApp(App)
app.use(router)
app.use(createPinia())
app.mount('#app')

