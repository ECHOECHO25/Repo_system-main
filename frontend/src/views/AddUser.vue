<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Admin</p>
      <h1 class="text-3xl font-semibold">Add User</h1>
      <p class="mt-2 text-sm text-slate-400">
        Create a new account with a role and status.
      </p>
    </div>

    <div class="max-w-2xl rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <p v-if="!isAdmin" class="mb-4 text-sm text-rose-300">
        Only admins can add users.
      </p>
      <form class="grid gap-4" @submit.prevent="handleSubmit">
        <div>
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Username</label>
          <input
            v-model="form.username"
            type="text"
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            required
          />
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            required
          />
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Role</label>
            <select
              v-model="form.role"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            >
              <option value="admin">Admin</option>
              <option value="editor">Editor</option>
            </select>
          </div>
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Status</label>
            <select
              v-model="form.status"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <p v-if="error" class="text-sm text-rose-300">{{ error }}</p>
        <p v-if="success" class="text-sm text-emerald-300">{{ success }}</p>

        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
            @click="resetForm"
          >
            Reset
          </button>
          <button
            type="submit"
            class="rounded-full bg-emerald-400/20 px-4 py-2 text-xs uppercase tracking-[0.3em] text-emerald-100 hover:bg-emerald-400/30"
            :disabled="loading || !isAdmin"
          >
            {{ loading ? 'Saving...' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
const { role } = useAuth()
const isAdmin = computed(() => role.value === 'admin')

const form = ref({
  username: '',
  password: '',
  role: 'editor',
  status: 'active'
})
const loading = ref(false)
const error = ref('')
const success = ref('')

const resetForm = () => {
  form.value = {
    username: '',
    password: '',
    role: 'editor',
    status: 'active'
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    if (!isAdmin.value) {
      error.value = 'Only admins can add users.'
      return
    }
    await axios.post(`${apiBase}/users`, form.value)
    success.value = 'User created.'
    form.value = {
      username: '',
      password: '',
      role: 'editor',
      status: 'active'
    }
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to create user'
  } finally {
    loading.value = false
  }
}
</script>
