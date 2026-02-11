<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex min-h-screen w-full max-w-none items-center justify-center px-6">
      <div class="w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900/60 p-8">
        <div class="flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-full">
            <img src="/src/assets/logo_repo.png" alt="REPO logo" class="h-full w-full object-contain" />
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.32em] text-slate-400">BSU REMIS</div>
            <div class="text-lg font-semibold">Research Monitoring System</div>
          </div>
        </div>

        <h1 class="mt-6 text-2xl font-semibold">Sign in</h1>
        <p class="mt-2 text-sm text-slate-400">Use your assigned username and password.</p>

        <form class="mt-6 space-y-4" @submit.prevent="handleLogin">
          <div>
            <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Username</label>
            <input
              v-model="form.username"
              type="text"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              required
            />
          </div>
          <div>
            <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              required
            />
          </div>
          <br>
          <p v-if="error" class="text-sm text-rose-300">{{ error }}</p>
          <button
            type="submit"
            class="w-full rounded-full bg-emerald-400/20 px-4 py-3 text-xs uppercase tracking-[0.28em] text-emerald-100 hover:bg-emerald-400/30"
            :disabled="loading"
          >
            {{ loading ? 'Signing in...' : 'Login' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const form = ref({
  username: '',
  password: ''
})
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await axios.post(`${apiBase}/auth/login`, form.value)
    window.location.href = '/dashboard'
  } catch (err) {
    error.value = err?.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
