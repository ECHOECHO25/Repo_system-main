<template>
  <main class="mx-auto w-full max-w-none px-6 pb-16 pt-10 md:px-10 2xl:px-20 2xl:pb-24">
    <section class="grid items-center gap-12 lg:grid-cols-[1.15fr_0.85fr] xl:grid-cols-[1.2fr_0.8fr] 2xl:grid-cols-[1.25fr_0.75fr]">
      <div>
        <p class="inline-flex items-center rounded-full border border-slate-800 bg-slate-900/70 px-3 py-1 text-xs uppercase tracking-[0.28em] text-slate-400">
          Live research intelligence
        </p>
        <h1 class="mt-6 text-4xl font-black leading-tight text-white md:text-5xl">
          Monitor BSU research outputs with clarity, speed, and trust.
        </h1>
        <p class="mt-4 max-w-xl text-base text-slate-300">
          A single homepage for KPIs, publication insights, and faculty performance. Built for accurate tracking,
          clean reporting, and fast decision-making.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <button
            class="rounded-lg bg-lime-300 px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-200"
            @click="refreshData"
          >
            Refresh dashboard
          </button>
          <button class="rounded-lg border border-slate-700 px-5 py-3 text-sm font-semibold text-slate-100 hover:border-slate-400">
            View publications
          </button>
        </div>
        <div class="mt-8 flex flex-wrap gap-6 text-xs uppercase tracking-[0.24em] text-slate-500">
          <span>2017-2025 Coverage</span>
          <span>Real-time KPIs</span>
          <span>Export-ready</span>
        </div>
      </div>

      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <div class="flex items-center justify-between text-xs uppercase tracking-[0.22em] text-slate-400">
          <span>Dashboard Snapshot</span>
          <span class="rounded-full bg-green-500/10 px-3 py-1 text-green-300">Updated {{ dashboardData.last_updated || '—' }}</span>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <div
            v-for="kpi in topKpis"
            :key="kpi.label"
            class="rounded-2xl bg-slate-950/70 p-4"
          >
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">{{ kpi.label }}</p>
            <p class="mt-3 text-3xl font-semibold">{{ kpi.value }}</p>
          </div>
          <div v-if="loading" class="rounded-2xl bg-slate-950/70 p-4 md:col-span-2">
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Loading KPIs</p>
            <p class="mt-3 text-lg text-slate-400">Fetching live metrics...</p>
          </div>
        </div>
        <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
          <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Publications Trend</div>
          <div class="mt-3 h-24 rounded-xl bg-gradient-to-r from-green-500/20 via-lime-400/20 to-yellow-400/20"></div>
        </div>
      </div>
    </section>

    <section id="dashboard" class="mt-16 grid gap-6 lg:grid-cols-[0.95fr_1.05fr] xl:grid-cols-[0.9fr_1.1fr] 2xl:grid-cols-[0.85fr_1.15fr]">
      <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
        <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Alert Center</div>
        <h2 class="mt-4 text-2xl font-semibold">Data quality at a glance</h2>
        <p class="mt-3 text-sm text-slate-300">
          Surface missing H-index values, low citation profiles, and inactive publication streaks.
        </p>
        <div class="mt-6 grid gap-3">
          <div
            v-for="alert in dashboardData.alerts"
            :key="alert.type"
            class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950/50 p-4"
          >
            <div>
              <p class="text-sm font-semibold">{{ alert.type }}</p>
              <p class="text-xs text-slate-400">{{ alert.description }}</p>
            </div>
            <span class="text-2xl font-semibold" :class="alertCountClass(alert)">{{ alert.count }}</span>
          </div>
        </div>
      </div>

      <div class="grid gap-6">
        <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
          <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold">Recent Activity</h2>
            <span class="text-xs uppercase tracking-[0.22em] text-slate-500">System logs</span>
          </div>
          <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-left text-xs uppercase tracking-[0.22em] text-slate-500">
                <tr>
                  <th class="pb-3">Date</th>
                  <th class="pb-3">Action</th>
                  <th class="pb-3">Description</th>
                  <th class="pb-3">User</th>
                  <th class="pb-3 text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="log in dashboardData.recent_activity" :key="log.id">
                  <td class="py-3 text-slate-300">{{ formatDate(log.created_at) }}</td>
                  <td class="py-3">
                    <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-200">
                      {{ log.action_type }}
                    </span>
                  </td>
                  <td class="py-3 text-slate-200">{{ log.description }}</td>
                  <td class="py-3 text-slate-300">{{ log.updated_by }}</td>
                  <td class="py-3 text-right">
                    <span class="rounded-full bg-green-500/10 px-3 py-1 text-xs text-green-200">
                      {{ log.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="!dashboardData.recent_activity?.length">
                  <td colspan="5" class="py-6 text-center text-xs uppercase tracking-[0.22em] text-slate-500">
                    No recent activity logged.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
          <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Fast actions</div>
          <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
              Import Excel
            </button>
            <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
              Add publication
            </button>
            <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
              Export report
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="mt-16 grid gap-6 lg:grid-cols-2">
      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <h2 class="text-lg font-semibold">Publication Trends by Year</h2>
        <div class="relative mt-4 h-72">
          <canvas id="yearlyTrendsChart" ref="yearlyTrendsChart" class="h-full w-full"></canvas>
        </div>
      </div>
      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <h2 class="text-lg font-semibold">Publications by Type</h2>
        <div class="relative mt-4 h-72">
          <canvas id="typeChart" ref="typeChart" class="h-full w-full"></canvas>
        </div>
      </div>
    </section>

    
    <section class="mt-16 rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-semibold">Publications by College/Institute</h2>
        <span class="text-xs uppercase tracking-[0.22em] text-slate-500">Total count</span>
      </div>
      <div class="relative mt-4 h-80">
        <canvas id="collegeYearChart" ref="collegeYearChart" class="h-full w-full"></canvas>
      </div>
    </section>

    <section class="mt-16 rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-lg font-semibold">Publications per Year (by College)</h2>
        <div class="flex items-center gap-3">
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">College</label>
          <select
            v-model="selectedCollege"
            class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @change="renderCollegeTrend"
          >
            <option value="">All Colleges</option>
            <option v-for="college in collegeOptions" :key="college" :value="college">{{ college }}</option>
          </select>
        </div>
      </div>
      <div class="relative mt-4 h-72">
        <canvas id="collegeTrendChart" ref="collegeTrendChart" class="h-full w-full"></canvas>
      </div>
    </section>


    <section class="mt-16 rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <h2 class="text-lg font-semibold">Publications by College/Institute</h2>
      <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left text-xs uppercase tracking-[0.22em] text-slate-500">
            <tr>
              <th class="pb-3">College/Institute</th>
              <th class="pb-3 text-right">Publications</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-for="college in dashboardData.publications_by_college" :key="college.college_institute">
              <td class="py-3 text-slate-200">{{ college.college_institute }}</td>
              <td class="py-3 text-right font-semibold text-white">{{ college.count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section id="publications" class="mt-16 grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3">
      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Publications</p>
        <h3 class="mt-4 text-xl font-semibold">Smart filtering and search</h3>
        <p class="mt-2 text-sm text-slate-300">
          Slice records by year, college, category, or citation count. Find results instantly with multi-field
          search.
        </p>
      </div>
      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Faculty</p>
        <h3 class="mt-4 text-xl font-semibold">Researcher performance</h3>
        <p class="mt-2 text-sm text-slate-300">
          Track H-index, citations, and scholar profiles. Highlight top performers and data gaps in one view.
        </p>
      </div>
      <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Reports</p>
        <h3 class="mt-4 text-xl font-semibold">Export-ready analytics</h3>
        <p class="mt-2 text-sm text-slate-300">
          Generate publication summaries, trend charts, and audit trails for internal reviews.
        </p>
      </div>
    </section>

    <section id="reports" class="mt-16 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8">
      <div class="flex flex-wrap items-center justify-between gap-6">
        <div>
          <h2 class="text-3xl font-semibold">Ready to launch your REMIS portal?</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-300">
            Use the data already collected to create a searchable, reliable, and export-ready research system.
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-200">
            Start with Excel import
          </button>
          <button class="rounded-lg border border-slate-600 px-5 py-3 text-sm font-semibold hover:border-slate-400">
            Contact admin
          </button>
        </div>
      </div>
    </section>

    <div class="mt-10 text-right text-xs uppercase tracking-[0.22em] text-slate-500">
      Last updated: {{ dashboardData.last_updated }}
    </div>
  </main>
</template>

<script>
import axios from 'axios';
import Chart from 'chart.js/auto';

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

export default {
  name: 'Dashboard',
  data() {
    return {
      loading: true,
      dashboardData: {
        kpis: [],
        alerts: [],
        yearly_trends: [],
        publications_by_type: [],
        publications_by_college: [],
        publications_by_college_year: [],
        recent_activity: [],
        last_updated: ''
      },
      charts: {
        yearlyTrends: null,
        publicationType: null,
        collegeYear: null,
        collegeTrend: null
      },
      selectedCollege: ''
    }
  },
  computed: {
    topKpis() {
      return (this.dashboardData.kpis || []).slice(0, 4);
    },
    collegeOptions() {
      return (this.dashboardData.publications_by_college || [])
        .map(item => item.college_institute)
        .filter(Boolean)
        .sort((a, b) => a.localeCompare(b));
    }
  },
  mounted() {
    this.fetchDashboardData();
  },
  methods: {
    async fetchDashboardData() {
      this.loading = true;
      try {
        const response = await axios.get(`${apiBase}/dashboard`);
        this.dashboardData = response.data.data;

        // Wait for DOM update before rendering charts
        this.$nextTick(() => {
          this.renderCharts();
        });
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        alert('Failed to load dashboard data');
      } finally {
        this.loading = false;
      }
    },

    renderCharts() {
      requestAnimationFrame(() => {
      Chart.defaults.color = '#cbd5f5';
      Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.2)';

      // Yearly Trends Chart
      if (this.charts.yearlyTrends) {
        this.charts.yearlyTrends.destroy();
      }

      const yearlyCtx = this.$refs.yearlyTrendsChart?.getContext('2d');
      if (yearlyCtx && this.dashboardData.yearly_trends?.length) {
        const yearlyLabels = this.dashboardData.yearly_trends.map(t => t.year);
        const yearlyCounts = this.dashboardData.yearly_trends.map(t => Number(t.count) || 0);
        this.charts.yearlyTrends = new Chart(yearlyCtx, {
          type: 'line',
          data: {
            labels: yearlyLabels,
            datasets: [{
              label: 'Publications',
              data: yearlyCounts,
              borderColor: 'rgb(132, 204, 22)',
              backgroundColor: 'rgba(132, 204, 22, 0.2)',
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                ticks: { color: '#cbd5f5' },
                grid: { color: 'rgba(148, 163, 184, 0.2)' }
              },
              y: {
                ticks: { color: '#cbd5f5' },
                grid: { color: 'rgba(148, 163, 184, 0.2)' }
              }
            }
          }
        });
      }

      // Publication Type Chart
      if (this.charts.publicationType) {
        this.charts.publicationType.destroy();
      }

      const typeCtx = this.$refs.typeChart?.getContext('2d');
      if (typeCtx && this.dashboardData.publications_by_type?.length) {
        const typeLabels = this.dashboardData.publications_by_type.map(t => t.publication_type || 'Unknown');
        const typeCounts = this.dashboardData.publications_by_type.map(t => Number(t.count) || 0);
        this.charts.publicationType = new Chart(typeCtx, {
          type: 'doughnut',
          data: {
            labels: typeLabels,
            datasets: [{
              data: typeCounts,
              backgroundColor: [
                'rgba(22, 101, 52, 0.85)',
                'rgba(132, 204, 22, 0.85)',
                'rgba(234, 179, 8, 0.85)',
                'rgba(163, 163, 163, 0.85)',
                'rgba(253, 224, 71, 0.85)'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      }

      // College per Year Chart
      if (this.charts.collegeYear) {
        this.charts.collegeYear.destroy();
      }

      const collegeCtx = this.$refs.collegeYearChart?.getContext('2d');
      if (collegeCtx && this.dashboardData.publications_by_college?.length) {
        const raw = this.dashboardData.publications_by_college.map(row => ({
          college: row.college_institute || 'Unknown',
          count: Number(row.count) || 0
        }));

        const sorted = [...raw].sort((a, b) => b.count - a.count);
        const topLimit = 12;
        const top = sorted.slice(0, topLimit);
        const others = sorted.slice(topLimit);
        const otherTotal = others.reduce((sum, row) => sum + row.count, 0);

        if (otherTotal > 0) {
          top.push({ college: 'Other', count: otherTotal });
        }

        const labels = top.map(item => item.college);
        const data = top.map(item => item.count);

        this.charts.collegeYear = new Chart(collegeCtx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: 'Publications',
              data,
              backgroundColor: 'rgba(132, 204, 22, 0.7)',
              borderColor: 'rgba(132, 204, 22, 0.95)',
              borderWidth: 1
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              x: {
                ticks: { color: '#cbd5f5' },
                grid: { color: 'rgba(148, 163, 184, 0.2)' }
              },
              y: {
                ticks: { color: '#cbd5f5' },
                grid: { display: false }
              }
            }
          }
        });
      }

      this.renderCollegeTrend();
      });
    },

    renderCollegeTrend() {
      if (this.charts.collegeTrend) {
        this.charts.collegeTrend.destroy();
      }

      const trendCtx = this.$refs.collegeTrendChart?.getContext('2d');
      const raw = this.dashboardData.publications_by_college_year || [];
      if (!trendCtx || !raw.length) {
        return;
      }

      const normalized = raw.map(row => ({
        year: Number(row.year),
        college: row.college_institute || 'Unknown',
        count: Number(row.count) || 0
      }));

      const years = [...new Set(normalized.map(r => r.year))].sort((a, b) => a - b);
      const filtered = this.selectedCollege
        ? normalized.filter(r => r.college === this.selectedCollege)
        : normalized;

      const byYear = years.map(year => {
        return filtered
          .filter(r => r.year === year)
          .reduce((sum, row) => sum + row.count, 0);
      });

      this.charts.collegeTrend = new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: years,
          datasets: [{
            label: this.selectedCollege || 'All Colleges',
            data: byYear,
            borderColor: 'rgba(59, 130, 246, 0.9)',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            tension: 0.2,
            fill: true,
            pointRadius: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            x: {
              ticks: { color: '#cbd5f5' },
              grid: { color: 'rgba(148, 163, 184, 0.2)' }
            },
            y: {
              ticks: { color: '#cbd5f5' },
              grid: { color: 'rgba(148, 163, 184, 0.2)' }
            }
          }
        }
      });
    },

    refreshData() {
      this.fetchDashboardData();
    },

    formatDate(date) {
      return new Date(date).toLocaleString();
    },

    alertCountClass(alert) {
      if (alert.severity === 'warning') {
        return 'text-yellow-200';
      }
      if (alert.severity === 'info') {
        return 'text-lime-200';
      }
      return 'text-slate-200';
    }
  },
  beforeUnmount() {
    if (this.charts.yearlyTrends) {
      this.charts.yearlyTrends.destroy();
    }
    if (this.charts.publicationType) {
      this.charts.publicationType.destroy();
    }
    if (this.charts.collegeYear) {
      this.charts.collegeYear.destroy();
    }
    if (this.charts.collegeTrend) {
      this.charts.collegeTrend.destroy();
    }
  }
}
</script>

<style scoped>
canvas {
  width: 100% !important;
  height: 100% !important;
}
</style>
