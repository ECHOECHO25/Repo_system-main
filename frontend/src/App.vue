<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 text-slate-100">
    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(22,101,52,0.18),_transparent_55%),radial-gradient(circle_at_20%_20%,_rgba(250,204,21,0.08),_transparent_45%),radial-gradient(circle_at_80%_0%,_rgba(132,204,22,0.12),_transparent_50%),radial-gradient(circle_at_70%_80%,_rgba(100,116,139,0.08),_transparent_55%)]"></div>
      <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(250,204,21,0.2)_1px,transparent_1px)] [background-size:40px_40px]"></div>

      <nav class="mx-auto grid w-full max-w-none items-center gap-6 px-6 py-6 md:grid-cols-[1fr_auto_1fr] md:px-10 2xl:px-20">
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-900">
            <span class="text-lg font-black">R</span>
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.32em] text-slate-400">BSU REMIS</div>
            <div class="text-lg font-semibold">Research Monitoring System</div>
          </div>
        </div>
        <div class="flex justify-center">
          <div class="flex flex-wrap items-center gap-2 rounded-full border border-slate-800 bg-slate-900/60 px-2 py-2">
            <router-link
              v-for="item in menuItems"
              :key="item.path"
              :to="item.path"
              class="rounded-full px-4 py-2 text-xs uppercase tracking-[0.22em] text-slate-300 transition hover:bg-slate-800/70 hover:text-white"
              :class="{ 'bg-slate-800/80 text-white': $route.path === item.path }"
            >
              {{ item.name }}
            </router-link>
          </div>
        </div>
        <div class="flex justify-end text-xs uppercase tracking-[0.3em] text-slate-400">
          {{ currentTime }}
        </div>
      </nav>

      <main class="mx-auto w-full max-w-none px-6 pb-12 md:px-10 2xl:px-20">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
  ChartBarIcon,
  DocumentTextIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const currentTime = ref(new Date().toLocaleString())

const menuItems = [
  { name: 'Dashboard', path: '/dashboard', icon: ChartBarIcon },
  { name: 'Publications', path: '/publications', icon: DocumentTextIcon },
  { name: 'Faculty', path: '/faculty', icon: UserGroupIcon }
]

let timer

onMounted(() => {
  timer = setInterval(() => {
    currentTime.value = new Date().toLocaleString()
  }, 1000)
})

onUnmounted(() => {
  clearInterval(timer)
})
</script>
