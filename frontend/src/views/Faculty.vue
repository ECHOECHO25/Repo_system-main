<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Faculty</p>
      <h1 class="text-3xl font-semibold">Faculty Metrics</h1>
      <p class="mt-2 text-sm text-slate-400">
        Search a faculty member to view Google Scholar citations and indices.
      </p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex-1">
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500" for="faculty-search">
            Search Faculty
          </label>
          <input
            id="faculty-search"
            v-model="query"
            type="text"
            placeholder="Type a name (e.g., Roscion Ian C. Lumbres)"
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
          />
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.3em] text-slate-500">
          <span>{{ pagination.total || filteredFaculty.length }} results</span>
          <button
            type="button"
            class="rounded-full border border-slate-800 bg-slate-950/60 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-400 hover:text-white"
            @click="openAddModal"
          >
            Add
          </button>
          <label
            class="cursor-pointer rounded-full border border-slate-800 bg-slate-950/60 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-400 hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
            :class="{ 'pointer-events-none opacity-60': importing }"
          >
            {{ importing ? 'Importing...' : 'Import CSV/XLSX' }}
            <input
              type="file"
              accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
              class="hidden"
              :disabled="importing"
              @change="handleImport"
            />
          </label>
          <button
            type="button"
            class="rounded-full border border-slate-800 bg-slate-950/60 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-400 hover:text-white"
            @click="handleExport"
          >
            Export CSV
          </button>
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/60">
      <table class="w-full text-left text-sm text-slate-200">
        <thead class="bg-slate-900/90 text-xs uppercase tracking-[0.22em] text-slate-400">
          <tr>
            <th class="px-6 py-4">Name</th>
            <th class="px-6 py-4">Citations</th>
            <th class="px-6 py-4">H-index</th>
            <th class="px-6 py-4">i-10 Index</th>
            <th class="px-6 py-4">Scholar Profile</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="faculty in filteredFaculty"
            :key="faculty.id || faculty.name"
            class="border-t border-slate-800/70"
          >
            <td class="px-6 py-4 font-medium text-slate-100">{{ faculty.name }}</td>
            <td class="px-6 py-4">{{ faculty.citations ?? '-' }}</td>
            <td class="px-6 py-4">{{ faculty.hIndex ?? '-' }}</td>
            <td class="px-6 py-4">{{ faculty.i10Index ?? '-' }}</td>
            <td class="px-6 py-4">
              <a
                v-if="faculty.scholarUrl"
                :href="faculty.scholarUrl"
                target="_blank"
                rel="noreferrer"
                class="text-emerald-300 hover:text-emerald-200"
              >
                View profile
              </a>
              <span v-else class="text-slate-500">-</span>
            </td>
            <td class="px-6 py-4 text-right">
              <button
                type="button"
                class="rounded-full border border-rose-500/40 px-3 py-1 text-xs uppercase tracking-[0.3em] text-rose-200 hover:border-rose-400"
                @click="openDeleteModal(faculty)"
              >
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="filteredFaculty.length === 0">
            <td class="px-6 py-6 text-slate-400" colspan="6">
              No matches found. Try a different spelling.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="showAddModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Add Faculty Metrics</h2>
          <button
            type="button"
            class="text-slate-400 hover:text-white"
            @click="closeAddModal"
          >
            Close
          </button>
        </div>
        <form class="mt-4 grid gap-4" @submit.prevent="submitAddForm">
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
            <p v-if="errors.name" class="mt-2 text-xs text-rose-300">{{ errors.name }}</p>
          </div>
          <div class="grid gap-4 md:grid-cols-3">
            <div>
              <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Citations</label>
              <input
                v-model.number="form.citations"
                type="number"
                min="0"
                class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <p v-if="errors.citations" class="mt-2 text-xs text-rose-300">{{ errors.citations }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-[0.3em] text-slate-500">H-index</label>
              <input
                v-model.number="form.hIndex"
                type="number"
                min="0"
                class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <p v-if="errors.hIndex" class="mt-2 text-xs text-rose-300">{{ errors.hIndex }}</p>
            </div>
            <div>
              <label class="text-xs uppercase tracking-[0.3em] text-slate-500">i-10 Index</label>
              <input
                v-model.number="form.i10Index"
                type="number"
                min="0"
                class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <p v-if="errors.i10Index" class="mt-2 text-xs text-rose-300">{{ errors.i10Index }}</p>
            </div>
          </div>
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Scholar URL</label>
            <input
              v-model="form.scholarUrl"
              type="url"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
            <p v-if="errors.scholarUrl" class="mt-2 text-xs text-rose-300">
              {{ errors.scholarUrl }}
            </p>
          </div>
          <div class="flex items-center justify-end gap-3">
            <button
              type="button"
              class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
              @click="closeAddModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-full bg-emerald-400/20 px-4 py-2 text-xs uppercase tracking-[0.3em] text-emerald-100 hover:bg-emerald-400/30"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showImportSuccessModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Import successful</h2>
          <button
            type="button"
            class="text-slate-400 hover:text-white"
            @click="closeImportSuccessModal"
          >
            Close
          </button>
        </div>
        <div class="mt-4 text-sm text-slate-300">
          {{ importSummary }}
        </div>
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            class="rounded-full bg-emerald-400/20 px-4 py-2 text-xs uppercase tracking-[0.3em] text-emerald-100 hover:bg-emerald-400/30"
            @click="closeImportSuccessModal"
          >
            Ok
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showImportErrorsModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-2xl rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Not imported</h2>
          <button
            type="button"
            class="text-slate-400 hover:text-white"
            @click="closeImportErrorsModal"
          >
            Close
          </button>
        </div>
        <div class="mt-4 text-sm text-slate-300">
          The following rows were skipped due to validation errors.
        </div>
        <div class="mt-4 max-h-64 overflow-y-auto rounded-2xl border border-slate-800 bg-slate-900/60">
          <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase tracking-[0.22em] text-slate-500">
              <tr>
                <th class="px-4 py-3">Row</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Reason</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
              <tr v-for="item in importErrors" :key="item.row">
                <td class="px-4 py-3 text-slate-300">{{ item.row }}</td>
                <td class="px-4 py-3 text-slate-200">{{ item.name || '-' }}</td>
                <td class="px-4 py-3 text-slate-300">{{ item.reason }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
            @click="closeImportErrorsModal"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Delete faculty</h2>
          <button
            type="button"
            class="text-slate-400 hover:text-white"
            @click="closeDeleteModal"
          >
            Close
          </button>
        </div>
        <div class="mt-4 text-sm text-slate-300">
          Are you sure you want to delete
          <span class="font-semibold text-white">{{ selectedFaculty?.name }}</span>?
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
            @click="closeDeleteModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-full bg-rose-500/20 px-4 py-2 text-xs uppercase tracking-[0.3em] text-rose-100 hover:bg-rose-500/30"
            @click="confirmDelete"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const query = ref('')
const showAddModal = ref(false)
const facultyList = ref([])
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  per_page: 1000,
  total: 0,
  total_pages: 0
})
const importError = ref('')
const importing = ref(false)
const showImportSuccessModal = ref(false)
const showImportErrorsModal = ref(false)
const importSummary = ref('')
const importErrors = ref([])
const showDeleteModal = ref(false)
const selectedFaculty = ref(null)
const errors = ref({
  name: '',
  citations: '',
  hIndex: '',
  i10Index: '',
  scholarUrl: ''
})
const form = ref({
  name: '',
  citations: null,
  hIndex: null,
  i10Index: null,
  scholarUrl: ''
})

const filteredFaculty = computed(() => {
  const needle = query.value.trim().toLowerCase()
  if (!needle) return facultyList.value
  return facultyList.value.filter((faculty) =>
    faculty.name.toLowerCase().includes(needle)
  )
})

const mapBackendFaculty = (faculty) => ({
  id: faculty.id,
  name: faculty.name,
  citations: faculty.google_scholar_citations ?? null,
  hIndex: faculty.h_index ?? null,
  i10Index: faculty.i10_index ?? null,
  scholarUrl: faculty.google_scholar_account ?? '',
  status: faculty.status
})

const fetchFaculty = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    if (query.value.trim()) {
      params.search = query.value.trim()
    }
    const response = await axios.get(`${apiBase}/faculty`, { params })
    facultyList.value = (response.data.data || []).map(mapBackendFaculty)
    if (response.data.pagination) {
      pagination.value = response.data.pagination
    }
  } catch (error) {
    console.error('Error fetching faculty:', error)
  } finally {
    loading.value = false
  }
}

let searchTimeout
watch(query, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchFaculty()
  }, 300)
})

onMounted(() => {
  fetchFaculty()
})

const openAddModal = () => {
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  errors.value = {
    name: '',
    citations: '',
    hIndex: '',
    i10Index: '',
    scholarUrl: ''
  }
}

const openDeleteModal = (faculty) => {
  selectedFaculty.value = faculty
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  selectedFaculty.value = null
  showDeleteModal.value = false
}

const confirmDelete = async () => {
  if (!selectedFaculty.value) return
  try {
    await axios.delete(`${apiBase}/faculty/${selectedFaculty.value.id}`)
    closeDeleteModal()
    fetchFaculty()
  } catch (error) {
    console.error('Error deleting faculty:', error)
  }
}

const closeImportSuccessModal = () => {
  showImportSuccessModal.value = false
}

const closeImportErrorsModal = () => {
  showImportErrorsModal.value = false
}

const isValidUrl = (value) => {
  try {
    const url = new URL(value)
    return ['http:', 'https:'].includes(url.protocol)
  } catch (error) {
    return false
  }
}

const validateFaculty = (candidate, existingNames) => {
  const nextErrors = {
    name: '',
    citations: '',
    hIndex: '',
    i10Index: '',
    scholarUrl: ''
  }

  if (!candidate.name || !candidate.name.trim()) {
    nextErrors.name = 'Name is required.'
  } else if (existingNames.has(candidate.name.trim().toLowerCase())) {
    nextErrors.name = 'Name already exists.'
  }

  if (candidate.citations !== null && candidate.citations !== '') {
    const parsed = Number(candidate.citations)
    if (Number.isNaN(parsed)) {
      nextErrors.citations = 'Citations must be a number.'
    } else if (parsed < 0) {
      nextErrors.citations = 'Citations must be 0 or higher.'
    }
  }

  if (candidate.hIndex !== null && candidate.hIndex !== '') {
    const parsed = Number(candidate.hIndex)
    if (Number.isNaN(parsed)) {
      nextErrors.hIndex = 'H-index must be a number.'
    } else if (parsed < 0) {
      nextErrors.hIndex = 'H-index must be 0 or higher.'
    }
  }

  if (candidate.i10Index !== null && candidate.i10Index !== '') {
    const parsed = Number(candidate.i10Index)
    if (Number.isNaN(parsed)) {
      nextErrors.i10Index = 'i-10 Index must be a number.'
    } else if (parsed < 0) {
      nextErrors.i10Index = 'i-10 Index must be 0 or higher.'
    }
  }

  if (candidate.scholarUrl && candidate.scholarUrl.trim()) {
    if (!isValidUrl(candidate.scholarUrl.trim())) {
      nextErrors.scholarUrl = 'Enter a valid URL (http/https).'
    }
  }

  return nextErrors
}

const hasErrors = (nextErrors) =>
  Object.values(nextErrors).some((message) => message && message.length > 0)

const submitAddForm = async () => {
  const existingNames = new Set(
    facultyList.value.map((faculty) => faculty.name.trim().toLowerCase())
  )
  const nextErrors = validateFaculty(form.value, existingNames)
  errors.value = nextErrors
  if (hasErrors(nextErrors)) return
  try {
    const payload = {
      name: form.value.name.trim(),
      google_scholar_citations: form.value.citations ?? null,
      h_index: form.value.hIndex ?? null,
      i10_index: form.value.i10Index ?? null,
      google_scholar_account: form.value.scholarUrl?.trim() || null,
      status: 'active'
    }
    await axios.post(`${apiBase}/faculty`, payload)
    form.value = {
      name: '',
      citations: null,
      hIndex: null,
      i10Index: null,
      scholarUrl: ''
    }
    showAddModal.value = false
    fetchFaculty()
  } catch (error) {
    console.error('Error creating faculty:', error)
  }
}

const handleExport = () => {
  const headers = ['Name', 'Citations', 'H-index', 'i-10 Index', 'Scholar URL']
  const rows = filteredFaculty.value.map((faculty) => [
    faculty.name,
    faculty.citations ?? '',
    faculty.hIndex ?? '',
    faculty.i10Index ?? '',
    faculty.scholarUrl ?? ''
  ])
  const csv = [headers, ...rows]
    .map((row) =>
      row
        .map((cell) => `"${String(cell).replace(/"/g, '""')}"`)
        .join(',')
    )
    .join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', 'faculty-metrics.csv')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

const normalizeHeader = (header) =>
  String(header || '')
    .toLowerCase()
    .replace(/[\s_-]+/g, '')

const mapRowToFaculty = (row, headers = []) => {
  if (Array.isArray(row)) {
    return {
      name: row[0] || '',
      citations: row[1] !== null && row[1] !== '' && row[1] !== undefined ? Number(row[1]) : null,
      hIndex: row[2] !== null && row[2] !== '' && row[2] !== undefined ? Number(row[2]) : null,
      i10Index: row[3] !== null && row[3] !== '' && row[3] !== undefined ? Number(row[3]) : null,
      scholarUrl: row[4] || ''
    }
  }

  const headerMap = headers.reduce((acc, header) => {
    acc[normalizeHeader(header)] = header
    return acc
  }, {})

  const getValue = (keys) => {
    for (const key of keys) {
      const header = headerMap[normalizeHeader(key)]
      if (header && row[header] !== undefined) return row[header]
    }
    return undefined
  }

  const toNumberOrNull = (value) => {
    if (value === null || value === undefined || value === '') return null
    const parsed = Number(value)
    return Number.isNaN(parsed) ? value : parsed
  }

  return {
    name: getValue(['name', 'faculty', 'facultyname', 'nameoffacultyresearcher']) || '',
    citations: toNumberOrNull(getValue(['citations', 'citation', 'googlescholarcitation'])),
    hIndex: toNumberOrNull(getValue(['hindex', 'h-index'])),
    i10Index: toNumberOrNull(getValue(['i10index', 'i-10index', 'i10'])),
    scholarUrl: getValue(['scholarurl', 'google scholar account', 'googlescholaraccount'])
  }
}

const parseCsv = (text) => {
  const rows = text.split(/\r?\n/).filter((row) => row.trim())
  if (!rows.length) return []
  const dataRows = rows.map((row) =>
    row
      .split(/,(?=(?:(?:[^\"]*\"){2})*[^\"]*$)/)
      .map((value) => value.replace(/^"|"$/g, '').replace(/""/g, '"').trim())
  )
  const [headers, ...body] = dataRows
  return body.map((row) => mapRowToFaculty(row, headers))
}

const parseXlsx = async (file) => {
  const buffer = await file.arrayBuffer()
  const workbook = XLSX.read(buffer, { type: 'array' })
  const sheetName = workbook.SheetNames[0]
  if (!sheetName) return []
  const sheet = workbook.Sheets[sheetName]
  const json = XLSX.utils.sheet_to_json(sheet, { header: 1 })
  if (!json.length) return []
  const [headers, ...body] = json
  return body.map((row) => mapRowToFaculty(row, headers))
}

const handleImport = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  importing.value = true
  importError.value = ''
  importSummary.value = ''
  importErrors.value = []
  try {
    let imported = []
    if (file.name.toLowerCase().endsWith('.xlsx')) {
      imported = await parseXlsx(file)
    } else {
      const text = await file.text()
      imported = parseCsv(text)
    }

    if (imported.length === 0) {
      importErrors.value = [
        {
          row: '-',
          name: '',
          reason: 'No valid rows found in the file.'
        }
      ]
      importSummary.value = 'Imported 0 row(s). 0 row(s) skipped.'
      showImportErrorsModal.value = true
      return
    }

    const existingNames = new Set(
      facultyList.value.map((faculty) => faculty.name.trim().toLowerCase())
    )
    const valid = []
    let invalidCount = 0
    for (let index = 0; index < imported.length; index += 1) {
      const row = imported[index]
      const rowErrors = validateFaculty(row, existingNames)
      if (hasErrors(rowErrors)) {
        invalidCount += 1
        importErrors.value.push({
          row: index + 2,
          name: row.name || '',
          reason: Object.values(rowErrors).filter(Boolean).join(', ')
        })
        continue
      }
      existingNames.add(row.name.trim().toLowerCase())
      valid.push(row)
    }
    if (invalidCount > 0) {
      importError.value = `${invalidCount} row(s) skipped due to validation errors.`
    }

    let inserted = 0
    let failed = 0
    for (let index = 0; index < valid.length; index += 1) {
      const row = valid[index]
      const payload = {
        name: row.name.trim(),
        google_scholar_citations: row.citations ?? null,
        h_index: row.hIndex ?? null,
        i10_index: row.i10Index ?? null,
        google_scholar_account: row.scholarUrl?.trim() || null,
        status: 'active'
      }
      try {
        await axios.post(`${apiBase}/faculty`, payload)
        inserted += 1
      } catch (error) {
        failed += 1
        importErrors.value.push({
          row: index + 2,
          name: row.name || '',
          reason: 'Failed to save to server.'
        })
        console.error('Error importing faculty:', error)
      }
    }
    importSummary.value = `Imported ${inserted} row(s). ${invalidCount + failed} row(s) skipped.`
    showImportSuccessModal.value = inserted > 0
    showImportErrorsModal.value = importErrors.value.length > 0 || inserted === 0
    fetchFaculty()
  } catch (error) {
    console.error('Error importing faculty:', error)
    importErrors.value = [
      {
        row: '-',
        name: '',
        reason: 'Import failed. Please check the file format.'
      }
    ]
    showImportErrorsModal.value = true
  } finally {
    event.target.value = ''
    importing.value = false
  }
}
</script>
