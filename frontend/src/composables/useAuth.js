import { computed, ref } from 'vue'
import axios from 'axios'

const user = ref(null)
const checked = ref(false)

const isAuthenticated = computed(() => !!user.value)
const role = computed(() => user.value?.role || null)

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const checkAuth = async () => {
  try {
    const response = await axios.get(`${apiBase}/auth/me`)
    user.value = response.data?.data || null
  } catch {
    user.value = null
  } finally {
    checked.value = true
  }
}

const setUser = (nextUser) => {
  user.value = nextUser || null
  checked.value = true
}

const clearUser = () => {
  user.value = null
  checked.value = true
}

export const useAuth = () => ({
  user,
  checked,
  isAuthenticated,
  role,
  checkAuth,
  setUser,
  clearUser
})
