import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './assets/main.css'
import { createPinia } from 'pinia'

import Dashboard from './views/Dashboard.vue'
import Publications from './views/Publications.vue'
import Faculty from './views/Faculty.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/dashboard', component: Dashboard },
  { path: '/publications', component: Publications },
  { path: '/faculty', component: Faculty }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

const app = createApp(App)
app.use(router)
app.mount('#app')
app.use(createPinia())
