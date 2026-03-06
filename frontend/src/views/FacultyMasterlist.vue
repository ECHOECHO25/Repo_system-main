<template>
  <section class="min-h-screen px-6 pb-12">
    <div class="mx-auto w-full">
      <div class="space-y-4">
        <div>
          <p class="text-xs uppercase tracking-[0.28em] text-slate-400">Faculty</p>
          <h1 class="text-2xl font-semibold text-slate-100 sm:text-3xl">Masterlist of Researchers</h1>
          <p class="mt-1 max-w-3xl text-xs text-slate-400 sm:text-sm">
            Centralized roster for campus research staff. Manage records per campus, department, and unit.
          </p>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-2">
          <button
            class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 text-xs uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-400"
            @click="openModal()"
          >
            Add
          </button>
          <label class="rounded-full border border-slate-700 bg-slate-900/60 px-4 py-2 text-xs uppercase tracking-[0.2em] text-slate-200 transition hover:border-emerald-400 cursor-pointer">
            {{ importing ? 'Importing...' : 'Import CSV/XLS/XLSX' }}
            <input
              type="file"
              class="hidden"
              accept=".csv,.xls,.xlsx"
              @change="handleFileUpload"
            />
          </label>
          <button
            class="rounded-full border border-slate-700 bg-slate-900/60 px-4 py-2 text-xs uppercase tracking-[0.2em] text-slate-200 transition hover:border-emerald-400"
            @click="exportXlsx"
          >
            Export XLSX
          </button>
          <button
            v-if="canManage"
            class="rounded-full border border-rose-500/70 bg-rose-500/10 px-4 py-2 text-xs uppercase tracking-[0.2em] text-rose-200 transition hover:border-rose-400 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="selectedIds.length === 0"
            @click="deleteSelected"
          >
            Delete Selected
          </button>
        </div>
      </div>

      <div class="mt-6 grid gap-4 rounded-2xl border border-slate-800 bg-slate-900/50 p-4 sm:p-6">
        <div class="grid gap-3 md:grid-cols-[1.5fr_220px_220px]">
          <div>
            <label class="block text-[11px] uppercase tracking-[0.2em] text-slate-400">Search masterlist</label>
            <input
              v-model.trim="searchQuery"
              type="text"
              placeholder="Search name, college, department..."
              class="mt-2 w-full rounded-full border border-slate-700 bg-slate-950/60 px-4 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] uppercase tracking-[0.2em] text-slate-400">Campus</label>
            <select
              v-model="selectedCampus"
              class="mt-2 w-full rounded-full border border-slate-700 bg-slate-950/60 px-4 py-2 text-xs text-slate-100 focus:border-emerald-400 focus:outline-none"
            >
              <option value="">All Campuses</option>
              <option v-for="campus in campusOptions" :key="campus" :value="campus">
                {{ campus }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] uppercase tracking-[0.2em] text-slate-400">Teaching Filter</label>
            <select
              v-model="selectedTeaching"
              class="mt-2 w-full rounded-full border border-slate-700 bg-slate-950/60 px-4 py-2 text-xs text-slate-100 focus:border-emerald-400 focus:outline-none"
            >
              <option value="">All</option>
              <option value="Teaching">Teaching</option>
              <option value="Non-Teaching">Non-Teaching</option>
            </select>
          </div>
        </div>
      </div>

      <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/50">
        <div v-if="loading" class="px-4 py-6 text-sm text-slate-300">Loading masterlist...</div>
        <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">{{ error }}</div>
        <div v-else-if="filteredRows.length === 0" class="px-4 py-6 text-sm text-slate-400">
          No records found. Try a different search or campus filter.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full table-auto divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/80 text-xs uppercase tracking-[0.16em] text-slate-400">
              <tr>
                <th v-if="canManage" class="px-4 py-3">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleAll"
                    class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-400"
                  />
                </th>
                <th class="px-4 py-3">Campus</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Position</th>
                <th class="px-4 py-3">College/Division</th>
                <th class="px-4 py-3">Department/Office/Unit</th>
                <th class="px-4 py-3">Sex</th>
                <th class="px-4 py-3">Teaching</th>
                <th v-if="canManage" class="px-4 py-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-200">
              <tr v-for="row in filteredRows" :key="row.id">
                <td v-if="canManage" class="px-4 py-3">
                  <input
                    type="checkbox"
                    :value="row.id"
                    v-model="selectedIds"
                    class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-400"
                  />
                </td>
                <td class="px-4 py-3 whitespace-normal break-words">{{ row.campus || '-' }}</td>
                <td class="px-4 py-3 font-medium text-white whitespace-normal break-words">{{ row.name }}</td>
                <td class="px-4 py-3 whitespace-normal break-words">{{ row.position || '-' }}</td>
                <td class="px-4 py-3 whitespace-normal break-words">{{ row.college_division || '-' }}</td>
                <td class="px-4 py-3 whitespace-normal break-words">{{ row.department_office_unit || '-' }}</td>
                <td class="px-4 py-3">{{ row.sex || '-' }}</td>
                <td class="px-4 py-3">{{ row.teaching_status || '-' }}</td>
                <td v-if="canManage" class="px-4 py-3 text-right">
                  <button
                    v-if="canManage"
                    class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                    @click="openModal(row)"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="pagination.total_pages > 1" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 border-t border-slate-800 px-4 py-3 text-xs text-slate-400">
          <div>
            Showing {{ rows.length }} of {{ pagination.total }} records
          </div>
          <div class="flex items-center justify-center gap-2">
            <button
              class="rounded-full border border-slate-700 px-3 py-1 text-[10px] uppercase tracking-[0.2em] hover:border-emerald-400"
              :disabled="pagination.current_page <= 1"
              @click="goToPage(pagination.current_page - 1)"
            >
              Prev
            </button>
            <span class="text-[10px] uppercase tracking-[0.2em]">
              Page {{ pagination.current_page }} of {{ pagination.total_pages }}
            </span>
            <button
              class="rounded-full border border-slate-700 px-3 py-1 text-[10px] uppercase tracking-[0.2em] hover:border-emerald-400"
              :disabled="pagination.current_page >= pagination.total_pages"
              @click="goToPage(pagination.current_page + 1)"
            >
              Next
            </button>
          </div>
          <div></div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-40 bg-black/60" @click="closeModal"></div>
    </transition>

    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl" @click.stop>
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-200">
              {{ editingRow ? 'Edit Masterlist Record' : 'Add Masterlist Record' }}
            </h2>
            <button
              type="button"
              class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
              @click="closeModal"
            >
              Close
            </button>
          </div>

          <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submitForm">
            <label class="text-sm md:col-span-2">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Campus (optional)</span>
              <select
                v-model="form.campus"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              >
                <option value="">Select campus</option>
                <option v-for="campus in campusOptions" :key="campus" :value="campus">
                  {{ campus }}
                </option>
              </select>
            </label>

            <label class="text-sm md:col-span-2">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Name *</span>
              <input
                v-model.trim="form.name"
                type="text"
                required
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
            </label>

            <label class="text-sm">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Position</span>
              <input
                v-model.trim="form.position"
                type="text"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
            </label>

            <label class="text-sm">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Sex</span>
              <select
                v-model="form.sex"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              >
                <option value="">Select</option>
                <option value="M">M</option>
                <option value="F">F</option>
              </select>
            </label>

            <label class="text-sm">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Teaching Status</span>
              <select
                v-model="form.teaching_status"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              >
                <option value="">Select</option>
                <option value="Teaching">Teaching</option>
                <option value="Non-Teaching">Non-Teaching</option>
              </select>
            </label>

            <label class="text-sm md:col-span-2">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">College/Division</span>
              <input
                v-model.trim="form.college_division"
                type="text"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
            </label>

            <label class="text-sm md:col-span-2">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Department/Office/Unit</span>
              <input
                v-model.trim="form.department_office_unit"
                type="text"
                class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
            </label>

            <div class="md:col-span-2 flex items-center justify-end gap-2">
              <p v-if="submitError" class="mr-auto text-xs text-rose-300">{{ submitError }}</p>
              <button
                type="button"
                class="rounded-md border border-slate-700 px-3 py-2 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="rounded-md border border-emerald-500/70 bg-emerald-500/10 px-3 py-2 text-xs uppercase tracking-[0.18em] text-emerald-200 hover:border-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ submitting ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Import results modal -->
    <transition name="fade">
      <div v-if="showImportResults" class="fixed inset-0 z-40 bg-black/60" @click="closeImportResults"></div>
    </transition>
    <transition name="modal">
      <div v-if="showImportResults" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl" @click.stop>
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-200">Import Results</h3>
            <button
              type="button"
              class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
              @click="closeImportResults"
            >
              Close
            </button>
          </div>

          <p class="text-xs text-slate-300">
            Inserted: {{ importResults.inserted }} | Skipped: {{ importResults.skipped.length }}
          </p>

          <div v-if="importResults.skipped.length" class="mt-4 max-h-64 overflow-auto rounded-xl border border-slate-800">
            <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
              <thead class="bg-slate-900/70 text-[10px] uppercase tracking-[0.16em] text-slate-400">
                <tr>
                  <th class="px-3 py-2">Row</th>
                  <th class="px-3 py-2">Reason</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800 text-slate-200">
                <tr v-for="item in importResults.skipped" :key="`${item.row}-${item.reason}`">
                  <td class="px-3 py-2">{{ item.row }}</td>
                  <td class="px-3 py-2">{{ item.reason }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx'
import ExcelJS from 'exceljs'
import { useAuth } from '../composables/useAuth'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
const { isAuthenticated, role } = useAuth()
const canManage = computed(() => isAuthenticated.value && role.value !== 'viewer')
const rows = ref([])
const loading = ref(false)
const error = ref('')
const searchQuery = ref('')
const selectedCampus = ref('')
const selectedTeaching = ref('')
const page = ref(1)
const perPage = 20
const pagination = ref({
  current_page: 1,
  per_page: perPage,
  total: 0,
  total_pages: 1
})
const showModal = ref(false)
const showImportResults = ref(false)
const importResults = ref({ inserted: 0, skipped: [] })
const importing = ref(false)
const submitError = ref('')
const submitting = ref(false)
const editingRow = ref(null)
const selectedIds = ref([])

const form = ref({
  campus: '',
  name: '',
  position: '',
  college_division: '',
  department_office_unit: '',
  sex: '',
  teaching_status: ''
})

const campusOptions = computed(() => {
  const set = new Set(['La Trinidad', 'Bokod', 'Buguias'])
  rows.value.forEach((row) => {
    if (row.campus) set.add(row.campus)
  })
  return Array.from(set)
})

const filteredRows = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return rows.value.filter((row) => {
    if (selectedCampus.value && row.campus !== selectedCampus.value) return false
    if (selectedTeaching.value && row.teaching_status !== selectedTeaching.value) return false
    if (!q) return true
    const haystack = [
      row.name,
      row.position,
      row.college_division,
      row.department_office_unit,
      row.campus
    ]
      .map((v) => String(v ?? '').toLowerCase())
      .join(' ')
    return haystack.includes(q)
  })
})

const fetchRows = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(`${apiBase}/faculty-masterlist`, {
      params: {
        search: searchQuery.value || undefined,
        campus: selectedCampus.value || undefined,
        teaching_status: selectedTeaching.value || undefined,
        page: page.value,
        per_page: perPage
      }
    })
    rows.value = response.data?.data || [] 
    pagination.value = response.data?.pagination || pagination.value
    selectedIds.value = []
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load masterlist.'
    rows.value = []
  } finally {
    loading.value = false
  }
}

const openModal = (row = null) => {
  submitError.value = ''
  editingRow.value = row
  form.value = {
    campus: row?.campus || '',
    name: row?.name || '',
    position: row?.position || '',
    college_division: row?.college_division || '',
    department_office_unit: row?.department_office_unit || '',
    sex: row?.sex || '',
    teaching_status: row?.teaching_status || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRow.value = null
  submitError.value = ''
}

const normalizePayload = () => ({
  campus: form.value.campus || null,
  name: form.value.name?.trim(),
  position: form.value.position || null,
  college_division: form.value.college_division || null,
  department_office_unit: form.value.department_office_unit || null,
  sex: form.value.sex || null,
  teaching_status: form.value.teaching_status || null
})

const submitForm = async () => {
  submitError.value = ''
  if (!form.value.name?.trim()) {
    submitError.value = 'Name is required.'
    return
  }

  try {
    submitting.value = true
    const payload = normalizePayload()
    if (editingRow.value) {
      await axios.put(`${apiBase}/faculty-masterlist/${editingRow.value.id}`, payload)
    } else {
      await axios.post(`${apiBase}/faculty-masterlist`, payload)
    }
    closeModal()
    await fetchRows()
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Failed to save record.'
  } finally {
    submitting.value = false
  }
}

const deleteRow = async (row) => {
  const confirmed = window.confirm('Delete this record? This action cannot be undone.')
  if (!confirmed) return
  try {
    await axios.delete(`${apiBase}/faculty-masterlist/${row.id}`)
    await fetchRows()
  } catch (err) {
    alert(err?.response?.data?.message || 'Failed to delete record.')
  }
}

const deleteSelected = async () => {
  if (!selectedIds.value.length) return
  const confirmed = window.confirm(`Delete ${selectedIds.value.length} selected record(s)? This action cannot be undone.`)
  if (!confirmed) return
  try {
    await Promise.all(selectedIds.value.map((id) => axios.delete(`${apiBase}/faculty-masterlist/${id}`)))
    await fetchRows()
  } catch (err) {
    alert(err?.response?.data?.message || 'Failed to delete selected records.')
  }
}

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  event.target.value = ''

  try {
    importing.value = true
    const data = await file.arrayBuffer()
    const workbook = XLSX.read(data, { type: 'array' })
    const sheet = workbook.Sheets[workbook.SheetNames[0]]
    const rawRows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' })

    let mapped = parseMasterlistRows(rawRows)
    if (!mapped.length) {
      const rowsRaw = XLSX.utils.sheet_to_json(sheet, { defval: '' })
      mapped = rowsRaw.map((row) => mapRowFromSheet(row)).filter(Boolean)
    }
    if (!mapped.length) {
      importResults.value = { inserted: 0, skipped: [{ row: 0, reason: 'No valid rows found' }] }
      showImportResults.value = true
      return
    }

    const response = await axios.post(`${apiBase}/faculty-masterlist/bulk-import`, {
      rows: mapped
    })
    importResults.value = {
      inserted: response.data?.inserted || 0,
      skipped: response.data?.skipped || []
    }
    showImportResults.value = true
    await fetchRows()
  } catch (err) {
    importResults.value = {
      inserted: 0,
      skipped: [{ row: 0, reason: err?.response?.data?.message || 'Import failed' }]
    }
    showImportResults.value = true
  } finally {
    importing.value = false
  }
}

const mapRowFromSheet = (row) => {
  const normalized = {}
  Object.keys(row).forEach((key) => {
    const clean = String(key).toLowerCase().replace(/[^a-z0-9]+/g, '')
    normalized[clean] = row[key]
  })

  const name = normalized.name || normalized.facultyname || normalized.fullname
  if (!name || String(name).trim() === '') return null

  return {
    campus: normalized.campus || '',
    name: String(name).trim(),
    position: normalized.position || '',
    college_division: normalized.collegedivision || normalized.collegeinstitute || normalized.college || '',
    department_office_unit:
      normalized.departmentofficeunit || normalized.department || normalized.office || normalized.unit || '',
    sex: normalized.sex || normalized.gender || '',
    teaching_status:
      normalized.teachingstatus ||
      normalized.teaching ||
      normalized.teachingnonteaching ||
      normalized.status ||
      ''
  }
}

const parseMasterlistRows = (rawRows) => {
  let currentCampus = ''
  let currentTeaching = ''
  let headerDetected = false
  const records = []

  rawRows.forEach((row) => {
    const rawCells = row.map((cell) => String(cell ?? '').trim())
    const nonEmpty = rawCells.filter((cell) => cell !== '')
    if (!nonEmpty.length) return

    const line = nonEmpty.join(' ')
    const upperLine = line.toUpperCase()

    // Detect place
    const placeMatch = upperLine.match(/^[A-Z]\.\s+(.+)$/)
    if (placeMatch && !upperLine.includes('TEACHING')) {
      const place = placeMatch[1].trim()
      if (place.includes('LA TRINIDAD')) currentCampus = 'La Trinidad'
      else if (place.includes('BOKOD')) currentCampus = 'Bokod'
      else if (place.includes('BUGUIAS')) currentCampus = 'Buguias'
      else currentCampus = titleCase(place)
      return
    }

    // Detect type
    const typeMatch = upperLine.match(/^[A-Z]\.\d+\.\s+(TEACHING|NON-TEACHING)$/i)
    if (typeMatch) {
      currentTeaching = typeMatch[1].toUpperCase() === 'NON-TEACHING' ? 'Non-Teaching' : 'Teaching'
      return
    }

    // Detect header row
    if (!headerDetected && upperLine.includes('NAME') && upperLine.includes('COLLEGE/DIVISION')) {
      headerDetected = true
      return
    }

    if (!headerDetected) return

    const indexCol = rawCells.findIndex((cell) => /^\d+$/.test(String(cell ?? '').trim()))
    if (indexCol === -1) return

    const name = String(rawCells[indexCol + 1] ?? '').trim()
    if (!name) return

    records.push({
      campus: currentCampus || '',
      name,
      position: String(rawCells[indexCol + 2] ?? '').trim(),
      college_division: String(rawCells[indexCol + 3] ?? '').trim(),
      department_office_unit: String(rawCells[indexCol + 4] ?? '').trim(),
      sex: String(rawCells[indexCol + 5] ?? '').trim(),
      teaching_status: currentTeaching || ''
    })
  })

  return records
}

const titleCase = (value) =>
  String(value || '')
    .toLowerCase()
    .split(' ')
    .map((word) => (word ? word[0].toUpperCase() + word.slice(1) : ''))
    .join(' ')

const allSelected = computed(() => selectedIds.value.length && selectedIds.value.length === filteredRows.value.length)
const toggleAll = (event) => {
  if (event.target.checked) {
    selectedIds.value = filteredRows.value.map((row) => row.id)
  } else {
    selectedIds.value = []
  }
}

const closeImportResults = () => {
  showImportResults.value = false
}

const exportXlsx = async () => {
  try {
    const response = await axios.get(`${apiBase}/faculty-masterlist`, {
      params: {
        search: searchQuery.value || undefined,
        campus: selectedCampus.value || undefined,
        teaching_status: selectedTeaching.value || undefined,
        page: 1,
        per_page: 5000
      }
    })
    const data = response.data?.data || []

    const workbook = new ExcelJS.Workbook()
    const sheet = workbook.addWorksheet('Plantilla')

    sheet.columns = [
      { key: 'index', width: 5 },
      { key: 'name', width: 28 },
      { key: 'position', width: 24 },
      { key: 'college', width: 30 },
      { key: 'department', width: 40 },
      { key: 'sex', width: 6 }
    ]

    // Load BSU logo - FIXED PATH
    let bsuId = null
    try {
      // Try multiple possible paths
      const possiblePaths = [
        new URL('../assets/logo_bsu.png', import.meta.url),
        new URL('../assets/logo-bsu.png', import.meta.url),
        new URL('../assets/logo_repo.png', import.meta.url)
      ]
      
      for (const logoUrl of possiblePaths) {
        try {
          const bsuBuffer = await fetch(logoUrl).then((res) => {
            if (!res.ok) throw new Error('Failed to load logo')
            return res.arrayBuffer()
          })
          bsuId = workbook.addImage({ buffer: bsuBuffer, extension: 'png' })
          console.log('✅ BSU logo loaded successfully from:', logoUrl.href)
          break
        } catch (e) {
          console.log('❌ Failed to load logo from:', logoUrl.href, e.message)
          continue
        }
      }
      
      if (!bsuId) {
        console.warn('⚠️ Could not load BSU logo from any path')
      }
    } catch (error) {
      console.error('Error loading BSU logo:', error)
    }

    // Load Bagong Pilipinas logo
    let bagongId = null
    try {
      const bagongUrl = new URL('../assets/Bagong_Pilipinas_Logo.svg', import.meta.url)
      const bagongBuffer = await svgToPngBuffer(bagongUrl.toString(), 180, 90)
      bagongId = workbook.addImage({ buffer: bagongBuffer, extension: 'png' })
      console.log('✅ Bagong Pilipinas logo loaded successfully')
    } catch (error) {
      console.error('Error loading Bagong Pilipinas logo:', error)
    }

    sheet.mergeCells('B2', 'E2')
    sheet.mergeCells('B3', 'E3')
    sheet.mergeCells('B4', 'E4')
    sheet.mergeCells('B5', 'E5')

    sheet.getCell('B2').value = 'Republic of the Philippines'
    sheet.getCell('B3').value = 'Benguet State University'
    sheet.getCell('B4').value = 'La Trinidad, Benguet'
    sheet.getCell('B5').value = `CHECKLIST as of ${new Date().toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })}`

    sheet.getCell('B2').alignment = { horizontal: 'center' }
    sheet.getCell('B3').alignment = { horizontal: 'center' }
    sheet.getCell('B4').alignment = { horizontal: 'center' }
    sheet.getCell('B5').alignment = { horizontal: 'center' }

    sheet.getCell('B2').font = { name: 'Arial Narrow', size: 9 }
    sheet.getCell('B3').font = { name: 'Old English Text MT', size: 11, color: { argb: '008000' } }
    sheet.getCell('B4').font = { name: 'Arial Narrow', size: 9 }
    sheet.getCell('B5').font = { name: 'Arial Narrow', size: 9, bold: true }

    if (bsuId) {
      sheet.addImage(bsuId, {
        tl: { col: 2.1, row: 1.2 },
        ext: { width: 75, height: 75 }
      })
      console.log('✅ BSU logo added to sheet')
    } else {
      console.warn('⚠️ Skipping BSU logo - image not loaded')
    }
    
    if (bagongId) {
      sheet.addImage(bagongId, {
        tl: { col: 4.6, row: 1.2 },
        ext: { width: 90, height: 50 }
      })
      console.log('✅ Bagong Pilipinas logo added to sheet')
    }

    let rowCursor = 7

    const addHeaderRow = () => {
      sheet.getRow(rowCursor).values = ['', 'NAME', 'POSITION', 'COLLEGE/DIVISION', 'DEPARTMENT/OFFICE/UNIT', 'SEX']
      sheet.getRow(rowCursor).font = { name: 'Arial Narrow', size: 9, bold: true }
      sheet.getRow(rowCursor).alignment = { horizontal: 'center' }
      sheet.getRow(rowCursor).border = borderAll()
      rowCursor += 1
    }

    const addSectionRow = (label) => {
      sheet.mergeCells(`A${rowCursor}`, `F${rowCursor}`)
      sheet.getCell(`A${rowCursor}`).value = label
      sheet.getCell(`A${rowCursor}`).font = { name: 'Arial Narrow', size: 9, bold: true }
      rowCursor += 1
    }

    addHeaderRow()

    const campuses = ['La Trinidad', 'Bokod', 'Buguias']
    const campusLetter = { 'La Trinidad': 'A', 'Bokod': 'B', 'Buguias': 'C' }
    const typeLabel = { Teaching: '1', 'Non-Teaching': '2' }

    campuses.forEach((campus) => {
      const campusRows = data.filter((row) => (row.campus || '').toLowerCase() === campus.toLowerCase())
      if (!campusRows.length) return
      addSectionRow(`${campusLetter[campus]}. ${campus.toUpperCase()}`)

      ;['Teaching', 'Non-Teaching'].forEach((type) => {
        const typeRows = campusRows.filter((row) => (row.teaching_status || '') === type)
        if (!typeRows.length) return
        addSectionRow(`${campusLetter[campus]}.${typeLabel[type]}. ${type.toUpperCase()}`)

        typeRows.forEach((row, idx) => {
          sheet.getRow(rowCursor).values = [
            idx + 1,
            row.name || '',
            row.position || '',
            row.college_division || '',
            row.department_office_unit || '',
            row.sex || ''
          ]
          sheet.getRow(rowCursor).font = { name: 'Arial Narrow', size: 9 }
          sheet.getRow(rowCursor).border = borderAll()
          rowCursor += 1
        })
      })
    })

    const buffer = await workbook.xlsx.writeBuffer()
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'faculty-masterlist.xlsx'
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
    console.log('✅ Excel file exported successfully')
  } catch (err) {
    console.error('Export error:', err)
    alert(err?.response?.data?.message || 'Failed to export.')
  }
}

const borderAll = () => ({
  top: { style: 'thin' },
  left: { style: 'thin' },
  right: { style: 'thin' },
  bottom: { style: 'thin' }
})

const svgToPngBuffer = async (url, width, height) => {
  const svgText = await fetch(url).then((res) => res.text())
  const svgBlob = new Blob([svgText], { type: 'image/svg+xml' })
  const svgUrl = URL.createObjectURL(svgBlob)

  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      const ctx = canvas.getContext('2d')
      if (!ctx) {
        URL.revokeObjectURL(svgUrl)
        reject(new Error('Canvas not supported'))
        return
      }
      ctx.drawImage(img, 0, 0, width, height)
      canvas.toBlob(async (blob) => {
        URL.revokeObjectURL(svgUrl)
        if (!blob) {
          reject(new Error('Failed to convert svg'))
          return
        }
        resolve(await blob.arrayBuffer())
      }, 'image/png')
    }
    img.onerror = () => {
      URL.revokeObjectURL(svgUrl)
      reject(new Error('Failed to load svg'))
    }
    img.src = svgUrl
  })
}

onMounted(fetchRows)

watch([searchQuery, selectedCampus, selectedTeaching], () => {
  page.value = 1
  fetchRows()
})

const goToPage = (nextPage) => {
  const totalPages = pagination.value.total_pages || 1
  if (nextPage < 1 || nextPage > totalPages) return
  page.value = nextPage
  fetchRows()
}
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

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
