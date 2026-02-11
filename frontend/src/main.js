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

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/publications', component: Publications, meta: { requiresAuth: true } },
  { path: '/faculty', component: Faculty, meta: { requiresAuth: true } },
  { path: '/audit-logs', component: AuditLogs, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

axios.defaults.withCredentials = true

let authChecked = false
let isAuthenticated = false

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true

  if (!authChecked) {
    try {
      await axios.get(`${import.meta.env.VITE_API_URL || 'http://localhost:8080/api'}/auth/me`)
      isAuthenticated = true
    } catch {
      isAuthenticated = false
    }
    authChecked = true
  }

  if (!isAuthenticated) {
    return { path: '/login' }
  }

  return true
})

const app = createApp(App)
app.use(router)
app.mount('#app')
app.use(createPinia())
