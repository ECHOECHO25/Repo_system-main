<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Publications</p>
      <h1 class="text-3xl font-semibold">Author Match Review</h1>
      <p class="mt-2 text-sm text-slate-400">
        Review unmatched authors from recent publication uploads and link them to faculty profiles.
      </p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex-1">
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500" for="match-search">
            Search Pending Matches
          </label>
          <input
            id="match-search"
            v-model="searchQuery"
            type="text"
            placeholder="Search author or publication title..."
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
            @input="debounceSearch"
          />
        </div>
        <div class="text-xs uppercase tracking-[0.3em] text-slate-500">
          <span>{{ pagination.total || matches.length }} pending</span>
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4">
      <div v-if="loading" class="flex items-center justify-center py-10 text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading matches
      </div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">
        {{ error }}
      </div>
      <div v-else>
        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-left text-sm text-slate-200">
            <thead class="bg-slate-900/90 text-xs uppercase tracking-[0.22em] text-slate-400">
              <tr>
                <th class="px-4 py-4">Author</th>
                <th class="px-4 py-4">Publication</th>
                <th class="px-4 py-4">Year</th>
                <th class="px-4 py-4">Faculty Link</th>
                <th class="px-4 py-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="match in matches"
                :key="match.id"
                class="border-t border-slate-800/70"
              >
                <td class="px-4 py-4 font-medium text-slate-100">
                  {{ match.author_name }}
                </td>
                <td class="px-4 py-4">
                  <span class="text-slate-200">{{ match.publication_title || 'Untitled' }}</span>
                </td>
                <td class="px-4 py-4">{{ match.publication_year || '-' }}</td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="rounded-full border border-emerald-400/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-emerald-200 hover:border-emerald-300"
                      @click="openMatchModal(match)"
                    >
                      Match
                    </button>
                    <span
                      class="rounded-full bg-amber-500/10 px-3 py-1 text-[10px] uppercase tracking-[0.22em] text-amber-200"
                    >
                      Unmatched
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4 text-right">
                  <button
                    type="button"
                    class="ml-2 rounded-full border border-rose-500/40 px-3 py-1 text-xs uppercase tracking-[0.3em] text-rose-200 hover:border-rose-400"
                    :disabled="actionLoading[match.id]"
                    @click="rejectMatch(match.id)"
                  >
                    Reject
                  </button>
                </td>
              </tr>
              <tr v-if="matches.length === 0">
                <td class="px-4 py-6 text-slate-400" colspan="5">
                  No pending matches found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.total_pages > 1" class="mt-4 flex items-center justify-center gap-2">
          <button
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            Prev
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="rounded-full border px-4 py-2 text-xs uppercase tracking-[0.3em]"
            :class="page === pagination.current_page
              ? 'border-emerald-400 bg-emerald-400/10 text-emerald-100'
              : 'border-slate-800 text-slate-200 hover:border-emerald-400'"
            @click="changePage(page)"
          >
            {{ page }}
          </button>
          <button
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === pagination.total_pages"
            @click="changePage(pagination.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showMatchModal && activeMatch"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-3xl rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Author Matches</p>
            <p class="mt-2 text-sm font-semibold text-slate-100">{{ activeMatch.author_name }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ activeMatch.publication_title || 'Untitled' }}</p>
          </div>
          <button
            type="button"
            class="text-xs uppercase tracking-[0.22em] text-slate-400 hover:text-slate-200"
            @click="closeMatchModal"
          >
            Close
          </button>
        </div>

        <div v-if="activeState" class="mt-4 rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
          <label class="block text-[10px] uppercase tracking-[0.2em] text-slate-500">Search Faculty</label>
          <div class="relative mt-2">
            <input
              v-model="activeState.query"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100"
              @input="onFacultyQuery(activeMatch)"
              @focus="openFacultyOptions(activeMatch.id)"
              @blur="closeFacultyOptions(activeMatch.id)"
            />
            <div
              v-if="activeState.optionsOpen && activeState.options.length"
              class="absolute z-20 mt-2 w-full max-h-56 overflow-y-auto rounded-xl border border-slate-700 bg-slate-950 p-2 text-sm shadow-2xl"
            >
              <button
                v-for="option in activeState.options"
                :key="`modal-${activeMatch.id}-${option.id}`"
                type="button"
                class="block w-full rounded-lg px-2 py-2 text-left text-slate-200 hover:bg-slate-800"
                @mousedown.prevent="selectFaculty(activeMatch.id, option)"
              >
                {{ option.name }}
              </button>
            </div>
          </div>

          <div class="mt-4 grid gap-2 md:grid-cols-2">
            <div>
              <label class="block text-[10px] uppercase tracking-[0.2em] text-slate-500">Non-Faculty Author Name</label>
              <input
                v-model="activeState.nonFacultyName"
                type="text"
                class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100"
              />
            </div>
            <div>
              <label class="block text-[10px] uppercase tracking-[0.2em] text-slate-500">Author Type</label>
              <select
                v-model="activeState.nonFacultyType"
                class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100"
              >
                <option v-for="type in nonFacultyTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <button
              type="button"
              class="rounded-full border border-emerald-400/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-emerald-200 hover:border-emerald-300 disabled:opacity-50"
              :disabled="actionLoading[activeMatch.id] || !activeState.facultyId"
              @click="confirmMatch(activeMatch)"
            >
              Assign
            </button>
            <button
              type="button"
              class="rounded-full border border-slate-700 px-4 py-2 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500 disabled:opacity-50"
              :disabled="actionLoading[activeMatch.id]"
              @click="addFacultyFromAuthor(activeMatch)"
            >
              Add Faculty
            </button>
            <button
              type="button"
              class="rounded-full border border-cyan-400/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-cyan-200 hover:border-cyan-300 disabled:opacity-50"
              :disabled="actionLoading[activeMatch.id]"
              @click="addNonFacultyAuthor(activeMatch)"
            >
              Add Non-Faculty
            </button>
            <button
              type="button"
              class="rounded-full border border-rose-500/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-rose-200 hover:border-rose-400 disabled:opacity-50"
              :disabled="actionLoading[activeMatch.id]"
              @click="rejectMatch(activeMatch.id)"
            >
              Reject
            </button>
          </div>
          <p v-if="activeState.error" class="mt-3 text-xs text-rose-300">{{ activeState.error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const loading = ref(false)
const error = ref('')
const matches = ref([])
const searchQuery = ref('')
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  total_pages: 0
})

const facultyOptions = ref([])
const rowStates = ref({})
const actionLoading = ref({})
const showMatchModal = ref(false)
const activeMatchId = ref(null)
const nonFacultyTypes = [
  { value: 'internal', label: 'Internal' },
  { value: 'external', label: 'External' },
  { value: 'international', label: 'International' }
]

const activeMatch = computed(() =>
  matches.value.find((item) => item.id === activeMatchId.value) || null
)
const activeState = computed(() =>
  activeMatchId.value ? rowStates.value[activeMatchId.value] || null : null
)

const normalizePerson = (value) =>
  String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

const findFacultyOptions = (query, authorName) => {
  const normalizedQuery = normalizePerson(query)
  const fallbackAuthor = normalizePerson(authorName)
  const needle = normalizedQuery || fallbackAuthor

  if (!needle) return facultyOptions.value.slice(0, 20)
  const matched = facultyOptions.value.filter((option) =>
    normalizePerson(option.name).includes(needle)
  )
  return (matched.length ? matched : facultyOptions.value).slice(0, 20)
}

const buildRowState = (match, existing = null) => {
  const current = existing || {}
  const prefilled = current.query || match.author_name || ''
  const exact = facultyOptions.value.find(
    (option) => normalizePerson(option.name) === normalizePerson(match.author_name)
  )

  return {
    query: prefilled,
    facultyId: current.facultyId || (exact ? String(exact.id) : ''),
    facultyName: current.facultyName || (exact ? exact.name : ''),
    nonFacultyName: current.nonFacultyName || match.author_name || '',
    nonFacultyType: current.nonFacultyType || 'external',
    optionsOpen: false,
    options: findFacultyOptions(prefilled, match.author_name),
    error: ''
  }
}

const syncRowStates = () => {
  const next = {}
  for (const match of matches.value) {
    next[match.id] = buildRowState(match, rowStates.value[match.id])
  }
  rowStates.value = next
}

const fetchMatches = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    const response = await axios.get(`${apiBase}/publication-author-links/pending`, { params })
    matches.value = response.data.data || []
    if (response.data.pagination) {
      pagination.value = response.data.pagination
    }
    syncRowStates()
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load pending matches.'
  } finally {
    loading.value = false
  }
}

const fetchFacultyOptions = async () => {
  try {
    const perPage = 500
    let page = 1
    let totalPages = 1
    const all = []

    do {
      const response = await axios.get(`${apiBase}/faculty`, {
        params: { page, per_page: perPage, sort: 'name', order: 'asc' }
      })
      const rows = response.data?.data || []
      all.push(...rows)
      totalPages = Number(response.data?.pagination?.total_pages || 1)
      page += 1
    } while (page <= totalPages)

    facultyOptions.value = all
      .filter((row) => row && row.id && row.name)
      .map((row) => ({
        id: Number(row.id),
        name: row.name
      }))
    syncRowStates()
  } catch (err) {
    facultyOptions.value = []
    rowStates.value = {}
  }
}

const openFacultyOptions = (id) => {
  if (!rowStates.value[id]) return
  rowStates.value[id].optionsOpen = true
}

const closeFacultyOptions = (id) => {
  if (!rowStates.value[id]) return
  setTimeout(() => {
    if (rowStates.value[id]) rowStates.value[id].optionsOpen = false
  }, 120)
}

const onFacultyQuery = (match) => {
  const state = rowStates.value[match.id]
  if (!state) return
  state.options = findFacultyOptions(state.query, match.author_name)
  state.optionsOpen = true
  state.facultyId = ''
  state.facultyName = ''
  state.error = ''
}

const selectFaculty = (id, option) => {
  const state = rowStates.value[id]
  if (!state) return
  state.query = option.name
  state.facultyId = String(option.id)
  state.facultyName = option.name
  state.optionsOpen = false
  state.error = ''
}

const confirmMatch = async (match) => {
  const id = match.id
  const state = rowStates.value[id]
  if (!state) return
  state.error = ''
  const facultyId = state.facultyId
  if (!facultyId) {
    state.error = 'Select a faculty to confirm.'
    return
  }
  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'confirmed',
      faculty_id: facultyId
    })
    await fetchMatches()
    if (activeMatchId.value === id) {
      closeMatchModal()
    }
  } catch (err) {
    state.error = err?.response?.data?.message || 'Failed to confirm match.'
  } finally {
    actionLoading.value[id] = false
  }
}

const addFacultyFromAuthor = async (match) => {
  const id = match.id
  const state = rowStates.value[id]
  if (!state) return
  state.error = ''
  actionLoading.value[id] = true
  try {
    const response = await axios.post(`${apiBase}/faculty`, {
      name: match.author_name,
      status: 'active'
    })
    const facultyId = response?.data?.data?.id
    const facultyName = response?.data?.data?.name || match.author_name
    if (!facultyId) {
      state.error = 'Faculty created but no ID returned.'
      return
    }
    state.facultyId = String(facultyId)
    state.facultyName = facultyName
    state.query = facultyName
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'confirmed',
      faculty_id: facultyId
    })
    await fetchFacultyOptions()
    await fetchMatches()
    if (activeMatchId.value === id) {
      closeMatchModal()
    }
  } catch (err) {
    state.error = err?.response?.data?.message || 'Failed to add faculty.'
  } finally {
    actionLoading.value[id] = false
  }
}

const addNonFacultyAuthor = async (match) => {
  const id = match.id
  const state = rowStates.value[id]
  if (!state) return
  state.error = ''
  const name = String(state.nonFacultyName || '').trim()
  if (!name) {
    state.error = 'Non-faculty author name is required.'
    return
  }
  if (!['internal', 'external', 'international'].includes(state.nonFacultyType)) {
    state.error = 'Select a valid author type.'
    return
  }

  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'confirmed',
      non_faculty_author_name: name,
      non_faculty_type: state.nonFacultyType
    })
    await fetchMatches()
    if (activeMatchId.value === id) {
      closeMatchModal()
    }
  } catch (err) {
    state.error = err?.response?.data?.message || 'Failed to add non-faculty author.'
  } finally {
    actionLoading.value[id] = false
  }
}

const rejectMatch = async (id) => {
  if (rowStates.value[id]) {
    rowStates.value[id].error = ''
  }
  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'rejected'
    })
    await fetchMatches()
    if (activeMatchId.value === id) {
      closeMatchModal()
    }
  } catch (err) {
    if (rowStates.value[id]) {
      rowStates.value[id].error = err?.response?.data?.message || 'Failed to reject match.'
    }
  } finally {
    actionLoading.value[id] = false
  }
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.total_pages) return
  pagination.value.current_page = page
  fetchMatches()
}

const openMatchModal = (match) => {
  activeMatchId.value = match.id
  if (!rowStates.value[match.id]) {
    rowStates.value[match.id] = buildRowState(match)
  }
  showMatchModal.value = true
}

const closeMatchModal = () => {
  showMatchModal.value = false
  activeMatchId.value = null
}

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const total = pagination.value.total_pages
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  for (let i = start; i <= end; i += 1) {
    pages.push(i)
  }
  return pages
})

let searchTimeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchMatches()
  }, 300)
}

onMounted(() => {
  fetchMatches()
  fetchFacultyOptions()
})
</script>
