<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 text-slate-100">

    <!-- ================= NAVBAR ================= -->
    <nav
      v-if="!isLoginRoute"
      class="mx-auto flex flex-wrap items-center justify-between gap-4 px-6 py-6"
    >
      <!-- Left -->
      <div class="flex items-center gap-3">
        <button
          class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/70 hover:border-emerald-400"
          @click="toggleSidebar"
        >
          <Bars3Icon class="h-5 w-5" />
        </button>

        <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-full bg-transparent p-1">
          <img src="/src/assets/logo_repo.png" alt="REPO logo" class="h-full w-full object-contain" />
        </div>

        <div>
          <div class="text-xs uppercase tracking-[0.32em] text-slate-400">
            BSU REMIS
          </div>
          <div class="text-lg font-semibold">
            Research Monitoring System
          </div>
        </div>
      </div>

      <!-- Center Tabs -->
      <div class="order-3 w-full lg:order-none lg:w-auto">
        <div class="flex flex-wrap items-center justify-start gap-2 rounded-full border border-slate-800 bg-slate-950/60 p-1 text-xs uppercase tracking-[0.28em] text-slate-400">
          <router-link
            v-for="item in topNavItems"
            :key="item.path"
            :to="item.path"
            class="rounded-full px-4 py-2 text-[10px] transition sm:text-xs"
            :class="$route.path === item.path
              ? 'bg-emerald-500/15 text-emerald-100 border border-emerald-400/60'
              : 'hover:text-white'"
          >
            {{ item.name }}
          </router-link>
        </div>
      </div>

      <!-- Right -->
      <div class="flex items-center gap-4 text-xs uppercase tracking-[0.3em] text-slate-400">
        <span>{{ currentTime }}</span>
        <button
          v-if="!isAuthenticated"
          class="rounded-full border border-slate-800 bg-slate-900/60 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-400 hover:text-white"
          @click="$router.push('/login')"
        >
          Login
        </button>
      </div>
    </nav>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="px-6 pb-12">
      <router-view />
    </main>
  </div>

  <!-- ================= SIDEBAR BACKDROP ================= -->
  <transition name="fade">
    <div
      v-if="sidebarOpen && !isLoginRoute"
      class="fixed inset-0 z-40 bg-black/60"
      @click="closeSidebar"
    />
  </transition>

  <!-- ================= SIDEBAR ================= -->
  <transition name="slide">
    <aside
      v-if="sidebarOpen && !isLoginRoute"
      class="fixed left-0 top-0 z-50 h-full w-72 border-r border-slate-800 bg-slate-950 px-6 py-6"
    >
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-full bg-transparent p-1">
            <img src="/src/assets/logo_repo.png" alt="REPO logo" class="h-full w-full object-contain" />
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.32em] text-slate-400">
              BSU REMIS
            </div>
            <div class="text-lg font-semibold">
              Dashboard Menu
            </div>
          </div>
        </div>

        <button
          class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 hover:border-emerald-400"
          @click="closeSidebar"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Menu Items -->
      <nav class="space-y-3">

        <router-link
          v-for="item in filteredMenuItems"
          :key="item.path"
          :to="item.path"
          @click="closeSidebar"
          class="group flex items-center gap-4 rounded-xl px-4 py-3 transition"
          :class="[
            $route.path === item.path
              ? 'bg-emerald-500/10 border border-emerald-400 text-emerald-100'
              : 'hover:bg-slate-900/60 hover:text-white'
          ]"
        >
          <!-- Icon Container -->
          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/70 transition group-hover:border-emerald-400"
          >
            <component
              :is="item.icon"
              class="h-5 w-5 text-emerald-400 transition group-hover:text-white"
            />
          </div>

          <span
            class="uppercase tracking-[0.18em]"
            :class="item.path === '/acknowledgements' ? 'text-[11px]' : 'text-sm'"
          >
            {{ item.name }}
          </span>
        </router-link>

      </nav>

      <!-- Logout -->
      <div class="mt-8 border-t border-slate-800 pt-4" v-if="isAuthenticated">
        <button
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm uppercase tracking-[0.2em] hover:bg-slate-900/60 transition"
          @click="handleLogout"
        >
          <ArrowLeftOnRectangleIcon class="h-5 w-5 text-emerald-400" />
          Logout
        </button>
      </div>
    </aside>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from './composables/useAuth'
import axios from 'axios'

import {
  Bars3Icon,
  XMarkIcon,
  ChartBarIcon,
  DocumentTextIcon,
  UserGroupIcon,
  BookOpenIcon,
  ClockIcon,
  UserPlusIcon,
  ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline'

const sidebarOpen = ref(false)
const currentTime = ref(new Date().toLocaleString())
const route = useRoute()
const isLoginRoute = computed(() => route.path === '/login')
const { isAuthenticated, role, checkAuth, clearUser } = useAuth()

let timer

const menuItems = [
  { name: 'Dashboard', path: '/dashboard', icon: ChartBarIcon },
  { name: 'Publications', path: '/publications', icon: DocumentTextIcon },
  { name: 'Faculty', path: '/faculty', icon: UserGroupIcon },
  { name: 'Acknowledgements', path: '/acknowledgements', icon: BookOpenIcon },
  { name: 'Audit Logs', path: '/audit-logs', icon: ClockIcon, requiresAuth: true },
  { name: 'Add User', path: '/add-user', icon: UserPlusIcon, requiresAdmin: true }
]

const topNavItems = [
  { name: 'Dashboard', path: '/dashboard' },
  { name: 'Publications', path: '/publications' },
  { name: 'Faculty', path: '/faculty' },
  { name: 'Acknowledgements', path: '/acknowledgements' }
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

const toggleSidebar = () => (sidebarOpen.value = !sidebarOpen.value)
const closeSidebar = () => (sidebarOpen.value = false)

const handleLogout = async () => {
  try {
    await axios.post('http://localhost:8080/api/auth/logout')
  } catch {}
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
