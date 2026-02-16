<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 text-slate-100">
    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(22,101,52,0.18),_transparent_55%),radial-gradient(circle_at_20%_20%,_rgba(250,204,21,0.08),_transparent_45%),radial-gradient(circle_at_80%_0%,_rgba(132,204,22,0.12),_transparent_50%),radial-gradient(circle_at_70%_80%,_rgba(100,116,139,0.08),_transparent_55%)]"></div>
      <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(250,204,21,0.2)_1px,transparent_1px)] [background-size:40px_40px]"></div>

      <nav
        v-if="!isLoginRoute"
        class="mx-auto grid w-full max-w-none items-center gap-6 px-6 py-6 md:grid-cols-[1fr_auto_1fr] md:px-10 2xl:px-20"
      >
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/70 text-slate-100 hover:border-emerald-400"
            @click="toggleSidebar"
          >
            <Bars3Icon class="h-5 w-5" />
          </button>
          <div class="grid h-15 w-15 place-items-center overflow-hidden rounded-full bg-transparent p-1 text-slate-900">
            <img src="/src/assets/logo_repo.png" alt="REPO logo" class="h-full w-full object-contain" />
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.32em] text-slate-400">BSU REMIS</div>
            <div class="text-lg font-semibold">Research Monitoring System</div>
          </div>
        </div>
        <div class="flex justify-center">
          <div class="flex flex-wrap items-center gap-2 rounded-full border border-slate-800 bg-slate-900/60 px-2 py-2">
            <router-link
              v-for="item in topMenuItems"
              :key="item.path"
              :to="item.path"
              class="rounded-full px-4 py-2 text-xs uppercase tracking-[0.22em] text-slate-300 transition hover:bg-slate-800/70 hover:text-white"
              :class="{ 'bg-slate-800/80 text-white': $route.path === item.path }"
            >
              {{ item.name }}
            </router-link>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 text-xs uppercase tracking-[0.3em] text-slate-400">
          <span>{{ currentTime }}</span>
          <button
            v-if="!isAuthenticated"
            type="button"
            class="rounded-full border border-slate-800 bg-slate-900/60 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-400 hover:text-white"
            @click="$router.push('/login')"
          >
            Login
          </button>
        </div>
      </nav>

      <main
        class="mx-auto w-full max-w-none pb-12"
        :class="isLoginRoute ? 'pt-10 px-0' : 'px-6 md:px-10 2xl:px-20'"
      >
        <router-view />
      </main>
    </div>
  </div>

  <transition name="fade">
    <div
      v-if="sidebarOpen && !isLoginRoute"
      class="fixed inset-0 z-40 bg-black/60"
      @click="closeSidebar"
    ></div>
  </transition>

  <transition name="slide">
    <aside
      v-if="sidebarOpen && !isLoginRoute"
      class="fixed left-0 top-0 z-50 h-full w-72 border-r border-slate-800 bg-slate-950/95 px-6 py-6"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="grid h-15 w-15 place-items-center overflow-hidden rounded-full bg-transparent p-1 text-slate-900">
            <img src="/src/assets/logo_repo.png" alt="REPO logo" class="h-full w-full object-contain" />
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.32em] text-slate-400">BSU REMIS</div>
            <div class="text-lg font-semibold text-slate-100">Research Monitoring</div>
          </div>
        </div>
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 text-slate-100 hover:border-emerald-400"
          @click="closeSidebar"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <nav class="mt-8 space-y-2">
        <router-link
          v-for="item in filteredMenuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 rounded-2xl border border-transparent px-4 py-3 text-sm uppercase tracking-[0.22em] text-slate-300 transition hover:border-emerald-400 hover:text-white"
          :class="{ 'border-emerald-400 bg-emerald-400/10 text-emerald-100': $route.path === item.path }"
          @click="closeSidebar"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span>{{ item.name }}</span>
        </router-link>
      </nav>

      <div v-if="isAuthenticated" class="mt-8 border-t border-slate-800 pt-4">
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm uppercase tracking-[0.22em] text-slate-300 transition hover:bg-slate-900/60 hover:text-white"
          @click="handleLogout"
        >
          <span>Logout</span>
        </button>
      </div>
    </aside>
  </transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useAuth } from './composables/useAuth'
import {
  Bars3Icon,
  ChartBarIcon,
  DocumentTextIcon,
  XMarkIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const currentTime = ref(new Date().toLocaleString())
const sidebarOpen = ref(false)
const route = useRoute()
const isLoginRoute = computed(() => route.path === '/login')
const { isAuthenticated, role, checkAuth, clearUser } = useAuth()

const menuItems = [
  { name: 'Dashboard', path: '/dashboard', icon: ChartBarIcon, showTop: true },
  { name: 'Publications', path: '/publications', icon: DocumentTextIcon, showTop: true },
  { name: 'Faculty', path: '/faculty', icon: UserGroupIcon, showTop: true },
  { name: 'Audit Logs', path: '/audit-logs', icon: DocumentTextIcon, showTop: false, requiresAuth: true },
  { name: 'Add User', path: '/add-user', icon: UserGroupIcon, showTop: false, requiresAdmin: true }
]

const filteredMenuItems = computed(() =>
  menuItems.filter((item) => {
    if (item.requiresAdmin) {
      return isAuthenticated.value && role.value === 'admin'
    }
    if (item.requiresAuth) {
      return isAuthenticated.value
    }
    return true
  })
)

const topMenuItems = computed(() => filteredMenuItems.value.filter((item) => item.showTop))

let timer

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const handleLogout = async () => {
  try {
    await axios.post(`${import.meta.env.VITE_API_URL || 'http://localhost:8080/api'}/auth/logout`)
  } catch {
    // ignore
  }
  clearUser()
  window.location.href = '/login'
}

onMounted(() => {
  checkAuth()
  timer = setInterval(() => {
    currentTime.value = new Date().toLocaleString()
  }, 1000)
})

onUnmounted(() => {
  clearInterval(timer)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.25s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
