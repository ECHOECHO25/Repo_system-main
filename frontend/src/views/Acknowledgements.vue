<template>
  <section class="rounded-3xl border border-slate-800 bg-slate-900/60 p-8 text-slate-100">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">MJSIR</p>
        <h1 class="text-2xl font-semibold uppercase tracking-[0.12em]">MJSIR Acknowledgement</h1>
        <p class="mt-2 max-w-2xl text-xs text-slate-400">
          Track distributed journal copies, recipients, and issuance details in one printable log.
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <input
          v-model.trim="searchQuery"
          type="text"
          placeholder="Search records..."
          class="w-64 rounded-full border border-slate-700 bg-slate-950/70 px-4 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
        />
        <button
          v-if="isAuthenticated"
          type="button"
          class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-400 hover:text-emerald-100"
          @click="openModal"
        >
          Add Distributed Journal
        </button>
      </div>
    </div>

    <p v-if="pageMessage" class="mt-4 text-xs text-emerald-300">{{ pageMessage }}</p>
    <p v-if="pageError" class="mt-4 text-xs text-rose-300">{{ pageError }}</p>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800">
      <div v-if="loading" class="px-4 py-6 text-sm text-slate-300">Loading acknowledgement records...</div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">{{ error }}</div>
      <div v-else-if="filteredRows.length === 0" class="px-4 py-6 text-sm text-slate-300">
        No acknowledgement records found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
          <thead class="bg-slate-900/70 text-xs uppercase tracking-[0.16em] text-slate-400">
            <tr>
              <th class="px-4 py-3 font-semibold">Date</th>
              <th class="px-4 py-3 font-semibold">Time</th>
              <th class="px-4 py-3 font-semibold">Name</th>
              <th class="px-4 py-3 font-semibold">Position</th>
              <th class="px-4 py-3 font-semibold">Affiliation/Agency</th>
              <th class="px-4 py-3 font-semibold">Volume</th>
              <th class="px-4 py-3 font-semibold">No. of Copies</th>
              <th class="px-4 py-3 font-semibold">Remarks</th>
              <th class="px-4 py-3 font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800 text-slate-200">
            <tr v-for="row in filteredRows" :key="row.groupKey">
              <td class="px-4 py-3">{{ formatDate(row.date_issued) }}</td>
              <td class="px-4 py-3">{{ formatTime(row.time_issued) }}</td>
              <td class="px-4 py-3">{{ row.name || '-' }}</td>
              <td class="px-4 py-3">{{ row.position || '-' }}</td>
              <td class="px-4 py-3">{{ row.affiliation || '-' }}</td>
              <td class="px-4 py-3">{{ row.itemsLabel || '-' }}</td>
              <td class="px-4 py-3">{{ row.copiesLabel || '-' }}</td>
              <td class="px-4 py-3">{{ row.remarksLabel || '-' }}</td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  class="mr-2 rounded border border-emerald-500/70 bg-emerald-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-emerald-200 hover:border-emerald-400"
                  @click="printRecord(row)"
                >
                  Print
                </button>
                <button
                  v-if="isAuthenticated"
                  type="button"
                  class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                  @click="openAddVolumeModal(row)"
                >
                  Edit
                </button>
                <button
                  v-if="isAuthenticated"
                  type="button"
                  class="ml-2 rounded border border-rose-500/70 bg-rose-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-rose-200 hover:border-rose-400"
                  @click="deleteRecord(row)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <transition name="fade">
    <div
      v-if="showModal && isAuthenticated"
      class="fixed inset-0 z-40 bg-black/60"
      @click="closeModal"
    ></div>
  </transition>

  <transition name="modal">
    <div v-if="showModal && isAuthenticated" class="fixed inset-0 z-50 grid place-items-center p-4">
      <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-2xl" @click.stop>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-200">Add Distributed Journal</h2>
          <button
            type="button"
            class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
            @click="closeModal"
          >
            Close
          </button>
        </div>

        <form class="grid gap-4 md:grid-cols-2" @submit.prevent="createRow">
          <div class="relative md:col-span-2 rounded-xl border border-slate-800 bg-slate-950/60 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Distribution Schedule</p>

            <label class="text-sm">
              <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Date and Time *</span>
              <input
                readonly
                :value="displayDateTime"
                class="w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-950/80 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
                @click="openDateTimePicker"
              />
            </label>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
              <label class="text-sm">
                <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Type Date (YYYY-MM-DD)</span>
                <input
                  v-model.trim="form.date_issued"
                  type="text"
                  inputmode="numeric"
                  maxlength="10"
                  placeholder="YYYY-MM-DD"
                  class="w-full rounded-lg border border-slate-700 bg-slate-950/80 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
                />
              </label>
              <label class="text-sm">
                <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Type Time (HH:MM)</span>
                <input
                  v-model.trim="form.time_issued"
                  type="text"
                  inputmode="numeric"
                  maxlength="5"
                  placeholder="HH:MM"
                  class="w-full rounded-lg border border-slate-700 bg-slate-950/80 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
                />
              </label>
            </div>

            <div v-if="pickerOpen" class="absolute left-4 right-4 top-24 z-20 rounded-xl border border-slate-700 bg-slate-900 p-4 shadow-2xl">
              <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                <div>
                  <div class="mb-2 flex items-center justify-between">
                    <button type="button" class="rounded border border-slate-700 px-2 py-1 text-xs" @click="changeMonth(-1)"><</button>
                    <div class="text-sm font-semibold">{{ monthYearLabel }}</div>
                    <button type="button" class="rounded border border-slate-700 px-2 py-1 text-xs" @click="changeMonth(1)">></button>
                  </div>

                  <div class="grid grid-cols-7 gap-1 text-center text-xs text-slate-400">
                    <span v-for="w in weekDays" :key="w">{{ w }}</span>
                  </div>

                  <div class="mt-2 grid grid-cols-7 gap-1">
                    <button
                      v-for="(cell, index) in calendarCells"
                      :key="`${cell.day}-${index}`"
                      type="button"
                      class="h-8 rounded text-xs"
                      :class="[
                        cell.currentMonth ? 'text-slate-200' : 'text-slate-500',
                        isSelectedDay(cell) ? 'bg-sky-600 text-white' : 'hover:bg-slate-800'
                      ]"
                      @click="selectDay(cell)"
                    >
                      {{ cell.day }}
                    </button>
                  </div>

                  <div class="mt-3 flex items-center justify-between text-xs">
                    <button type="button" class="text-sky-300 hover:text-sky-200" @click="clearDateTime">Clear</button>
                    <button type="button" class="text-sky-300 hover:text-sky-200" @click="setTodayNow">Today</button>
                  </div>
                </div>

                <div class="border-l border-slate-700 pl-4">
                  <div class="mb-2 grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                    <div class="rounded bg-sky-600 py-1">{{ pad2(pickerHour) }}</div>
                    <div class="rounded bg-sky-600 py-1">{{ pad2(pickerMinute) }}</div>
                    <div class="rounded bg-sky-600 py-1">{{ pickerMeridiem }}</div>
                  </div>

                  <div class="grid grid-cols-3 gap-2">
                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="h in hourOptions"
                        :key="`h${h}`"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerHour === h ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerHour = h"
                      >
                        {{ pad2(h) }}
                      </button>
                    </div>

                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="m in minuteOptions"
                        :key="`m${m}`"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerMinute === m ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerMinute = m"
                      >
                        {{ pad2(m) }}
                      </button>
                    </div>

                    <div class="h-48 overflow-y-auto rounded border border-slate-700">
                      <button
                        v-for="ap in meridiemOptions"
                        :key="ap"
                        type="button"
                        class="block w-full px-2 py-1 text-center text-sm hover:bg-slate-800"
                        :class="pickerMeridiem === ap ? 'bg-sky-600 text-white' : 'text-slate-200'"
                        @click="pickerMeridiem = ap"
                      >
                        {{ ap }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3 flex justify-end gap-2">
                <button type="button" class="rounded border border-slate-700 px-3 py-1 text-xs" @click="pickerOpen = false">Cancel</button>
                <button type="button" class="rounded border border-sky-500 bg-sky-600/20 px-3 py-1 text-xs text-sky-200" @click="applyDateTime">Select</button>
              </div>
            </div>
          </div>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Name *</span>
            <input
              ref="journalTitleInput"
              v-model.trim="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Position *</span>
            <input
              v-model.trim="form.position"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Affiliation/Agency *</span>
            <input
              v-model.trim="form.affiliation"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 rounded-xl border border-slate-800 bg-slate-950/60 p-3">
            <div class="mb-2 flex items-center justify-between">
              <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Items</p>
              <button
                type="button"
                class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                @click="addFormItem"
              >
                Add Item
              </button>
            </div>

            <div
              v-for="(item, index) in form.items"
              :key="`form-item-${index}`"
              class="mb-2 grid items-center gap-2 md:grid-cols-[minmax(0,1fr)_96px_minmax(0,1fr)_84px]"
            >
              <input
                v-model.trim="item.volume"
                type="text"
                placeholder="Volume"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.number="item.copies"
                type="number"
                min="1"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.trim="item.remark"
                type="text"
                placeholder="Remark (optional)"
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <button
                type="button"
                class="w-full rounded border border-rose-500/60 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-rose-300 hover:border-rose-400"
                @click="removeFormItem(index)"
              >
                Remove
              </button>
            </div>
          </div>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Issued By *</span>
            <input
              v-model.trim="form.issued_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.18em] text-slate-400">Received By *</span>
            <input
              v-model.trim="form.received_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 flex items-center gap-3">
            <p v-if="submitError" class="text-xs text-rose-300">{{ submitError }}</p>
            <p v-if="submitMessage" class="text-xs text-emerald-300">{{ submitMessage }}</p>
            <button
              type="submit"
              :disabled="submitting"
              class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200 transition hover:border-emerald-400 hover:text-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ submitting ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>

  <transition name="fade">
    <div
      v-if="showAddVolumeModal && isAuthenticated"
      class="fixed inset-0 z-[60] bg-black/60"
      @click="closeAddVolumeModal"
    ></div>
  </transition>

  <transition name="modal">
    <div v-if="showAddVolumeModal && isAuthenticated" class="fixed inset-0 z-[70] grid place-items-center p-4">
      <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-2xl" @click.stop>
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-100">Edit Record</h3>
          <button
            type="button"
            class="rounded-md border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
            @click="closeAddVolumeModal"
          >
            Close
          </button>
        </div>

        <form class="grid gap-3 md:grid-cols-2" @submit.prevent="submitEditRecord">
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Date *</span>
            <input
              v-model="editForm.date_issued"
              type="date"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Time *</span>
            <input
              v-model="editForm.time_issued"
              type="time"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Name</span>
            <input
              v-model.trim="editForm.name"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Position</span>
            <input
              v-model.trim="editForm.position"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Affiliation/Agency</span>
            <input
              v-model.trim="editForm.affiliation"
              type="text"
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 rounded-xl border border-slate-800 bg-slate-950/60 p-3">
            <div class="mb-2 flex items-center justify-between">
              <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Items</p>
              <button
                type="button"
                class="rounded border border-sky-500/70 bg-sky-500/10 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-sky-200 hover:border-sky-400"
                @click="addEditItem"
              >
                Add Item
              </button>
            </div>

            <div
              v-for="(item, index) in editForm.items"
              :key="`item-${index}`"
              class="mb-2 grid items-center gap-2 md:grid-cols-[minmax(0,1fr)_96px_minmax(0,1fr)_84px]"
            >
              <input
                v-model.trim="item.volume"
                type="text"
                placeholder="Volume"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.number="item.copies"
                type="number"
                min="1"
                required
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <input
                v-model.trim="item.remark"
                type="text"
                placeholder="Remark (optional)"
                class="min-w-0 rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              />
              <button
                type="button"
                class="w-full rounded border border-rose-500/60 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-rose-300 hover:border-rose-400"
                @click="removeEditItem(index)"
              >
                Remove
              </button>
            </div>
          </div>

          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Issued By *</span>
            <input
              v-model.trim="editForm.issued_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>
          <label class="text-sm">
            <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-slate-400">Received By *</span>
            <input
              v-model.trim="editForm.received_by"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            />
          </label>

          <div class="md:col-span-2 flex items-center justify-end gap-2">
            <p v-if="submitError" class="mr-auto text-xs text-rose-300">{{ submitError }}</p>
            <p v-if="submitMessage" class="mr-auto text-xs text-emerald-300">{{ submitMessage }}</p>
            <button
              type="button"
              class="rounded-md border border-slate-700 px-3 py-2 text-xs uppercase tracking-[0.18em] text-slate-300 hover:border-slate-500"
              @click="closeAddVolumeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="rounded-md border border-sky-500/70 bg-sky-500/10 px-3 py-2 text-xs uppercase tracking-[0.18em] text-sky-200 hover:border-sky-400 disabled:cursor-not-allowed disabled:opacity-60"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>

</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'

const loading = ref(false)
const error = ref('')
const rows = ref([])
const submitting = ref(false)
const submitError = ref('')
const submitMessage = ref('')
const pageError = ref('')
const pageMessage = ref('')
const searchQuery = ref('')
const showModal = ref(false)
const journalTitleInput = ref(null)
const showAddVolumeModal = ref(false)
const editForm = ref({
  id: null,
  date_issued: '',
  time_issued: '',
  name: '',
  position: '',
  affiliation: '',
  issued_by: '',
  received_by: '',
  remarks: '',
  items: [{ volume: '', copies: 1, remark: '' }]
})
let previousBodyOverflow = ''

const form = ref({
  date_issued: '',
  time_issued: '',
  name: '',
  position: '',
  affiliation: '',
  issued_by: '',
  received_by: '',
  items: [{ volume: '', copies: 1, remark: '' }]
})

const { isAuthenticated, checkAuth } = useAuth()

const pickerOpen = ref(false)
const pickerYear = ref(new Date().getFullYear())
const pickerMonth = ref(new Date().getMonth())
const pickerDay = ref(new Date().getDate())
const pickerHour = ref(12)
const pickerMinute = ref(0)
const pickerMeridiem = ref('AM')

const weekDays = ['S', 'M', 'T', 'W', 'T', 'F', 'S']
const hourOptions = Array.from({ length: 12 }, (_, i) => i + 1)
const minuteOptions = Array.from({ length: 60 }, (_, i) => i)
const meridiemOptions = ['AM', 'PM']

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const groupedRows = computed(() =>
  rows.value.map((row) => {
    const items = Array.isArray(row.items) ? row.items : []
    const sortedItems = [...items].sort((a, b) =>
      String(a.volume ?? '').localeCompare(String(b.volume ?? ''), undefined, { numeric: true, sensitivity: 'base' })
    )
    const itemsLabel = sortedItems
      .map((item) => String(item.volume ?? '').trim())
      .filter(Boolean)
      .join(', ')
    const copiesLabel = sortedItems
      .map((item) => String(item.copies ?? '').trim())
      .filter(Boolean)
      .join(', ')
    const remarksLabel = sortedItems
      .map((item) => String(item.remark ?? '').trim())
      .filter(Boolean)
      .join(' | ')

    return {
      ...row,
      groupKey: row.id || `${row.date_issued}-${row.time_issued}-${row.name}`,
      itemRows: sortedItems,
      itemsLabel,
      copiesLabel,
      remarksLabel
    }
  })
)

const filteredRows = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return groupedRows.value

  return groupedRows.value.filter((row) => {
    const haystack = [
      formatDate(row.date_issued),
      formatTime(row.time_issued),
      row.name,
      row.position,
      row.affiliation,
      row.itemsLabel,
      row.copiesLabel,
      row.remarksLabel,
      row.issued_by,
      row.received_by,
      row.remarks
    ]
      .map((v) => String(v ?? '').toLowerCase())
      .join(' ')

    return haystack.includes(q)
  })
})

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString()
}

const formatTime = (value) => {
  if (!value) return '-'
  const date = new Date(`1970-01-01T${value}`)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const escapeHtml = (value) =>
  String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')

const printRecord = (row) => {
  const popup = window.open('', '_blank', 'width=900,height=700')
  if (!popup) return
  const itemRows = Array.isArray(row.itemRows) ? row.itemRows : []
  const printableItemRows = itemRows.slice(0, 6)
  while (printableItemRows.length < 6) {
    printableItemRows.push({ volume: '', copies: '', remark: '' })
  }

  const html = `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>MJSIR Acknowledgement</title>
        <style>
          @page { size: A4; margin: 12mm; }
          body { font-family: Arial, sans-serif; margin: 0; color: #111; }
          .sheet { border: 1px solid #777; padding: 14px; min-height: 270mm; box-sizing: border-box; }
          .top { display: grid; grid-template-columns: 1fr 270px; gap: 8px; }
          .brand { border: 1px solid #888; padding: 8px; text-align: center; }
          .brand-title { font-size: 28px; font-weight: 700; letter-spacing: 1px; }
          .brand-sub { font-size: 24px; font-weight: 700; letter-spacing: 1px; margin-top: 2px; }
          .meta table, .main table, .items, .signatures table { width: 100%; border-collapse: collapse; }
          .meta td, .meta th, .main td, .main th, .items td, .items th, .signatures td {
            border: 1px solid #888; padding: 6px 8px; font-size: 13px; vertical-align: top;
          }
          .meta th, .main th, .items th { background: #f8f8f8; text-transform: uppercase; letter-spacing: .6px; font-size: 12px; }
          .main { margin-top: 10px; }
          .items { margin-top: 12px; table-layout: fixed; }
          .items th:nth-child(1), .items td:nth-child(1) { width: 48%; }
          .items th:nth-child(2), .items td:nth-child(2) { width: 20%; text-align: center; }
          .items th:nth-child(3), .items td:nth-child(3) { width: 32%; }
          .blank-row td { height: 26px; }
          .signatures { margin-top: 34px; }
          .line { border-bottom: 1px solid #111; margin-top: 24px; }
          .sig-title { margin-top: 8px; font-size: 12px; font-style: italic; }
          .note { margin-top: 42px; font-size: 13px; text-align: center; line-height: 1.5; }
          .note a { color: #0b62c4; text-decoration: underline; }
          .foot { margin-top: 46px; font-size: 13px; line-height: 1.5; }
        </style>
      </head>
      <body>
        <div class="sheet">
          <div class="top">
            <div class="brand">
              <div class="brand-title">MJSIR</div>
              <div class="brand-sub">ACKNOWLEDGEMENT</div>
            </div>
            <div class="meta">
              <table>
                <tr><th>Code</th><th>Revision Number</th></tr>
                <tr><td>OF-REPO-05</td><td>&nbsp;</td></tr>
                <tr><th>Date of Effectivity</th><th>&nbsp;</th></tr>
                <tr><td>July 17, 2018</td><td>&nbsp;</td></tr>
              </table>
            </div>
          </div>

          <div class="main">
            <table>
              <tr><th style="width: 18%;">Date</th><td style="width: 32%;">${escapeHtml(formatDate(row.date_issued))}</td><th style="width: 18%;">Time</th><td>${escapeHtml(formatTime(row.time_issued))}</td></tr>
              <tr><th>Name</th><td colspan="3">${escapeHtml(row.name || '')}</td></tr>
              <tr><th>Position</th><td colspan="3">${escapeHtml(row.position || '')}</td></tr>
              <tr><th>Affiliation/Agency</th><td colspan="3">${escapeHtml(row.affiliation || '')}</td></tr>
            </table>
          </div>

          <table class="items">
            <tr><th>Volume</th><th>No. of Copies</th><th>Remark</th></tr>
            ${printableItemRows
              .map(
                (item) => `
            <tr${item.volume || item.copies || item.remark ? '' : ' class="blank-row"'}>
              <td>${escapeHtml(item.volume || '')}</td>
              <td>${escapeHtml(item.copies ?? '')}</td>
              <td>${escapeHtml(item.remark || '')}</td>
            </tr>`
              )
              .join('')}
          </table>

          <div class="signatures">
            <table>
              <tr>
                <td style="width: 50%;">
                  <div><strong>ISSUED BY:</strong> ${escapeHtml(row.issued_by || '')}</div>
                  <div class="line"></div>
                  <div class="sig-title">Signature over printed name</div>
                </td>
                <td style="width: 50%;">
                  <div><strong>RECEIVED BY:</strong> ${escapeHtml(row.received_by || '')}</div>
                  <div class="line"></div>
                  <div class="sig-title">Signature over printed name</div>
                </td>
              </tr>
            </table>
          </div>

          <div class="note">
            Kindly return this acknowledgement receipt after signing. Kindly send back to<br/>
            <a href="mailto:repo@bsu.edu.ph">repo@bsu.edu.ph</a> or (074) 422-1877.<br/>
            Thank you very much!
          </div>

          <div class="foot">
            Please accomplish this form in two copies:<br/>
            1) REPO Copy<br/>
            2) Receiving Copy
          </div>
        </div>
      </body>
    </html>
  `

  popup.document.open()
  popup.document.write(html)
  popup.document.close()
  popup.focus()
  popup.onload = () => popup.print()
}

const deleteRecord = async (row) => {
  if (!isAuthenticated.value) return
  const confirmed = window.confirm('Delete this record? This action cannot be undone.')
  if (!confirmed) return

  try {
    await axios.delete(`${apiBase}/acknowledgements/${row.id}`)
    pageMessage.value = 'Record deleted successfully.'
    pageError.value = ''
    await fetchRows()
  } catch (err) {
    pageError.value = err?.response?.data?.message || 'Failed to delete record.'
    pageMessage.value = ''
  }
}

const pad2 = (n) => String(n).padStart(2, '0')

const displayDateTime = computed(() => {
  if (!form.value.date_issued || !form.value.time_issued) return 'Click to select date and time'
  return `${formatDate(form.value.date_issued)} ${formatTime(form.value.time_issued)}`
})

const monthYearLabel = computed(() =>
  new Date(pickerYear.value, pickerMonth.value, 1).toLocaleDateString([], { month: 'long', year: 'numeric' })
)

const calendarCells = computed(() => {
  const first = new Date(pickerYear.value, pickerMonth.value, 1)
  const start = first.getDay()
  const daysCurrent = new Date(pickerYear.value, pickerMonth.value + 1, 0).getDate()
  const daysPrev = new Date(pickerYear.value, pickerMonth.value, 0).getDate()

  const cells = []
  for (let i = start - 1; i >= 0; i--) {
    cells.push({ day: daysPrev - i, currentMonth: false, monthOffset: -1 })
  }
  for (let d = 1; d <= daysCurrent; d++) {
    cells.push({ day: d, currentMonth: true, monthOffset: 0 })
  }
  while (cells.length < 42) {
    cells.push({ day: cells.length - (start + daysCurrent) + 1, currentMonth: false, monthOffset: 1 })
  }
  return cells
})

const isSelectedDay = (cell) => cell.currentMonth && cell.day === pickerDay.value

const openDateTimePicker = () => {
  if (form.value.date_issued) {
    const [y, m, d] = form.value.date_issued.split('-').map(Number)
    pickerYear.value = y
    pickerMonth.value = m - 1
    pickerDay.value = d
  } else {
    const now = new Date()
    pickerYear.value = now.getFullYear()
    pickerMonth.value = now.getMonth()
    pickerDay.value = now.getDate()
  }

  if (form.value.time_issued) {
    const [h, m] = form.value.time_issued.split(':').map(Number)
    const isPm = h >= 12
    pickerMeridiem.value = isPm ? 'PM' : 'AM'
    const h12 = h % 12 === 0 ? 12 : h % 12
    pickerHour.value = h12
    pickerMinute.value = m
  }

  pickerOpen.value = true
}

const changeMonth = (delta) => {
  const next = new Date(pickerYear.value, pickerMonth.value + delta, 1)
  pickerYear.value = next.getFullYear()
  pickerMonth.value = next.getMonth()
  const maxDay = new Date(pickerYear.value, pickerMonth.value + 1, 0).getDate()
  if (pickerDay.value > maxDay) pickerDay.value = maxDay
}

const selectDay = (cell) => {
  if (cell.currentMonth) {
    pickerDay.value = cell.day
    return
  }

  changeMonth(cell.monthOffset)
  pickerDay.value = cell.day
}

const clearDateTime = () => {
  form.value.date_issued = ''
  form.value.time_issued = ''
  pickerOpen.value = false
}

const setTodayNow = () => {
  const now = new Date()
  pickerYear.value = now.getFullYear()
  pickerMonth.value = now.getMonth()
  pickerDay.value = now.getDate()
  const h = now.getHours()
  pickerMeridiem.value = h >= 12 ? 'PM' : 'AM'
  pickerHour.value = h % 12 === 0 ? 12 : h % 12
  pickerMinute.value = now.getMinutes()
}

const applyDateTime = () => {
  const hour24 = pickerMeridiem.value === 'PM'
    ? (pickerHour.value % 12) + 12
    : (pickerHour.value % 12)

  form.value.date_issued = `${pickerYear.value}-${pad2(pickerMonth.value + 1)}-${pad2(pickerDay.value)}`
  form.value.time_issued = `${pad2(hour24)}:${pad2(pickerMinute.value)}`
  pickerOpen.value = false
}

const fetchRows = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(`${apiBase}/acknowledgements`, {
      params: { per_page: 200 }
    })
    rows.value = response.data?.data || []
  } catch (err) {
    rows.value = []
    error.value = err?.response?.data?.message || 'Failed to load acknowledgement records.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.value = {
    date_issued: '',
    time_issued: '',
    name: '',
    position: '',
    affiliation: '',
    issued_by: '',
    received_by: '',
    items: [{ volume: '', copies: 1, remark: '' }]
  }
  pickerOpen.value = false
}

const createRow = async () => {
  if (!isAuthenticated.value) return
  submitting.value = true
  submitError.value = ''
  submitMessage.value = ''
  pageError.value = ''
  pageMessage.value = ''
  try {
    if (!form.value.date_issued || !form.value.time_issued) {
      submitError.value = 'Please select both date and time.'
      return
    }

    const datePattern = /^\d{4}-\d{2}-\d{2}$/
    const timePattern = /^\d{2}:\d{2}$/
    if (!datePattern.test(form.value.date_issued)) {
      submitError.value = 'Use date format YYYY-MM-DD.'
      return
    }
    if (!timePattern.test(form.value.time_issued)) {
      submitError.value = 'Use time format HH:MM.'
      return
    }

    if (!form.value.name) {
      submitError.value = 'Name is required.'
      return
    }

    if (!form.value.items.length) {
      submitError.value = 'Please add at least one item.'
      return
    }

    for (let i = 0; i < form.value.items.length; i++) {
      const item = form.value.items[i]
      if (!item.volume || !item.copies) {
        submitError.value = `Item ${i + 1}: Volume and copies are required.`
        return
      }
    }

    if (!form.value.issued_by || !form.value.received_by) {
      submitError.value = 'Issued By and Received By are required.'
      return
    }

    const payload = {
      date_issued: form.value.date_issued,
      time_issued: form.value.time_issued || null,
      name: form.value.name,
      position: form.value.position,
      affiliation: form.value.affiliation,
      issued_by: form.value.issued_by || null,
      received_by: form.value.received_by || null,
      remarks: null,
      items: form.value.items.map((item) => ({
        volume: item.volume || null,
        copies: item.copies || 1,
        remark: item.remark || null
      }))
    }

    await axios.post(`${apiBase}/acknowledgements`, payload)
    submitMessage.value = 'Record added successfully.'
    resetForm()
    showModal.value = false
    await fetchRows()
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Failed to add record.'
  } finally {
    submitting.value = false
  }
}

const openAddVolumeModal = (row) => {
  if (!isAuthenticated.value) return
  pageError.value = ''
  pageMessage.value = ''
  submitError.value = ''
  submitMessage.value = ''
  editForm.value = {
    id: row.id,
    date_issued: row.date_issued || '',
    time_issued: row.time_issued || '',
    name: row.name || '',
    position: row.position || '',
    affiliation: row.affiliation || '',
    issued_by: row.issued_by || '',
    received_by: row.received_by || '',
    remarks: row.remarks || '',
    items: Array.isArray(row.items) && row.items.length
      ? row.items.map((item) => ({
          volume: item.volume || '',
          copies: item.copies || 1,
          remark: item.remark || ''
        }))
      : [{ volume: '', copies: 1, remark: '' }]
  }
  showAddVolumeModal.value = true
}

const closeAddVolumeModal = () => {
  showAddVolumeModal.value = false
  submitError.value = ''
  submitMessage.value = ''
}

const addEditItem = () => {
  editForm.value.items.push({ volume: '', copies: 1, remark: '' })
}

const removeEditItem = (index) => {
  if (editForm.value.items.length === 1) return
  editForm.value.items.splice(index, 1)
}

const addFormItem = () => {
  form.value.items.push({ volume: '', copies: 1, remark: '' })
}

const removeFormItem = (index) => {
  if (form.value.items.length === 1) return
  form.value.items.splice(index, 1)
}

const submitEditRecord = async () => {
  if (!isAuthenticated.value) return
  submitError.value = ''
  submitMessage.value = ''
  pageError.value = ''
  pageMessage.value = ''

  if (!editForm.value.id) {
    submitError.value = 'No record selected.'
    return
  }

  if (!editForm.value.date_issued || !editForm.value.time_issued) {
    submitError.value = 'Date and Time are required.'
    return
  }

  for (let i = 0; i < editForm.value.items.length; i++) {
    const item = editForm.value.items[i]
    if (!item.volume || !item.copies) {
      submitError.value = `Item ${i + 1}: Volume and copies are required.`
      return
    }
  }
  if (!editForm.value.issued_by || !editForm.value.received_by) {
    submitError.value = 'Issued By and Received By are required.'
    return
  }

  const payload = {
    date_issued: editForm.value.date_issued,
    time_issued: editForm.value.time_issued || null,
    name: editForm.value.name || null,
    position: editForm.value.position || null,
    affiliation: editForm.value.affiliation || null,
    issued_by: editForm.value.issued_by || null,
    received_by: editForm.value.received_by || null,
    remarks: editForm.value.remarks || null,
    items: editForm.value.items.map((item) => ({
      volume: item.volume,
      copies: item.copies,
      remark: item.remark
    }))
  }

  try {
    submitting.value = true
    await axios.put(`${apiBase}/acknowledgements/${editForm.value.id}`, payload)
    submitMessage.value = 'Record updated successfully.'
    closeAddVolumeModal()
    await fetchRows()
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Failed to update record.'
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  pickerOpen.value = false
  showAddVolumeModal.value = false
  submitError.value = ''
  submitMessage.value = ''
}

const openModal = () => {
  if (!isAuthenticated.value) return
  pageError.value = ''
  pageMessage.value = ''
  submitError.value = ''
  submitMessage.value = ''
  showModal.value = true
}

const handleEscKey = (event) => {
  if (event.key !== 'Escape') return
  if (showAddVolumeModal.value) {
    closeAddVolumeModal()
    return
  }
  if (pickerOpen.value) {
    pickerOpen.value = false
    return
  }
  if (showModal.value) closeModal()
}

watch(showModal, async (isOpen) => {
  if (isOpen) {
    previousBodyOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    await nextTick()
    journalTitleInput.value?.focus()
    return
  }
  document.body.style.overflow = previousBodyOverflow || ''
})

onMounted(async () => {
  await checkAuth()
  fetchRows()
  window.addEventListener('keydown', handleEscKey)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscKey)
  document.body.style.overflow = previousBodyOverflow || ''
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

