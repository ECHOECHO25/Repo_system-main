<template>
  <main class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
    <!-- Hero Header Section -->
    <div class="mx-auto w-full px-3 py-6 sm:px-6 sm:py-8 lg:px-8 2xl:px-12">
      <!-- System Status Badge -->
      <div class="mb-4 sm:mb-6">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-4 py-2 backdrop-blur-sm">
          <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
          <span class="text-[10px] font-medium uppercase tracking-wider text-emerald-300 sm:text-xs">
            Live System
          </span>
        </div>
      </div>

      <!-- Main Title and Description -->
      <div class="mb-8 sm:mb-12">
        <h1 class="mb-3 text-2xl font-bold text-white sm:text-4xl md:text-5xl lg:text-6xl">
          Publications & Citations Monitoring
        </h1>
        <p class="mb-3 max-w-3xl text-sm leading-relaxed text-slate-300 sm:text-lg">
          Comprehensive research tracking system for Benguet State University. Monitor publications, 
          citations, and research impact across all colleges, institutes, and research centers in real-time.
        </p>
        <p class="max-w-3xl text-[11px] leading-relaxed text-slate-400 sm:text-sm">
          This dashboard consolidates data from REPO submissions, provides ISO-compliant reporting, 
          supports accreditation requirements, and enables data-driven decision making for research 
          management and performance evaluation.
        </p>
      </div>

      <!-- Quick Stats Cards -->
      <div class="mb-10 sm:mb-12">
        <div class="mb-4 sm:mb-6">
          <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">Key Performance Indicators</h2>
          <p class="text-[11px] text-slate-400 sm:text-sm">
            Real-time overview of critical research metrics across all academic units.
          </p>
        </div>
        <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 xl:grid-cols-4">
          <div v-for="kpi in topKpis" :key="kpi.label" 
               class="group relative overflow-hidden rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm transition-all hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/10">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500/10 to-transparent blur-2xl"></div>
            <div class="relative">
              <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">
                {{ kpi.label }}
              </p>
              <p class="mb-3 text-2xl font-bold text-white sm:text-4xl">
                {{ kpi.value || 0 }}
              </p>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                {{ getKpiDescription(kpi.label) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center rounded-2xl border border-slate-700/50 bg-slate-800/30 p-16">
        <div class="text-center">
          <div class="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-emerald-500/20 border-t-emerald-500"></div>
          <p class="mb-2 text-sm font-medium text-slate-300">Loading Dashboard Data</p>
          <p class="text-xs text-slate-500">Fetching latest metrics from database...</p>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-10 sm:space-y-12">
        <!-- Charts Section -->
        <div>
          <div class="mb-4 sm:mb-6">
            <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">Research Analytics</h2>
            <p class="text-[11px] text-slate-400 sm:text-sm">
              Visual representation of publication trends and distribution patterns to identify growth areas and institutional strengths.
            </p>
          </div>
          <div class="grid gap-6 lg:grid-cols-2">
            <!-- Yearly Trends Chart -->
            <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
              <div class="mb-4 sm:mb-6">
                <h3 class="mb-2 text-base font-bold text-white sm:text-xl">Publication Trends Over Time</h3>
                <p class="mb-3 text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                  Year-over-year publication growth analysis showing research output trajectory. 
                  This trend helps identify peak production years and forecast future targets.
                </p>
              </div>
              <div class="relative h-56 sm:h-64 lg:h-72">
                <canvas ref="yearlyTrendsChart"></canvas>
              </div>
              <div class="mt-4 border-t border-slate-700/50 pt-4">
                <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                  Upward trends indicate increasing research activity. Use this data to validate 
                  annual reports and set realistic growth targets.
                </p>
              </div>
            </div>

            <!-- Publication Types Chart -->
            <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
              <div class="mb-4 sm:mb-6">
                <h3 class="mb-2 text-base font-bold text-white sm:text-xl">Publication Type Distribution</h3>
                <p class="mb-3 text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                  Breakdown of publications by format including journals, conference papers, books, 
                  and other scholarly outputs to ensure balanced research portfolio.
                </p>
              </div>
              <div class="relative h-56 sm:h-64 lg:h-72">
                <canvas ref="typeChart"></canvas>
              </div>
              <div class="mt-4 border-t border-slate-700/50 pt-4">
                <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                  A healthy research portfolio typically includes 60-70% journal articles, 20-25% 
                  conference proceedings, and 10-15% books or chapters.
                </p>
              </div>
            </div>
          </div>
        </div>
        <!-- Faculty Metrics Section -->
        <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
          <div class="mb-4 sm:mb-6">
            <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">Faculty Research Profile</h2>
            <p class="mb-3 text-[11px] leading-relaxed text-slate-400 sm:text-sm">
              Comprehensive overview of faculty research metrics including Google Scholar profiles, 
              citation counts, H-index, and i-10 index. These indicators help identify research leaders 
              and opportunities for faculty development.
            </p>
          </div>
          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-700/30 bg-slate-900/50 p-4 sm:p-5">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 sm:text-sm">Total Active Faculty</p>
                  <p class="mt-2 text-xl font-bold text-white sm:text-3xl">{{ facultyMetrics.metrics.total_faculty || 0 }}</p>
                </div>
                <div class="rounded-lg bg-blue-500/10 p-3">
                  <svg class="h-7 w-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
              </div>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                Faculty members registered with active research profiles
              </p>
            </div>

            <div class="rounded-xl border border-slate-700/30 bg-slate-900/50 p-4 sm:p-5">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 sm:text-sm">With Google Scholar Profile</p>
                  <p class="mt-2 text-xl font-bold text-white sm:text-3xl">{{ facultyMetrics.metrics.with_profile || 0 }}</p>
                </div>
                <div class="rounded-lg bg-green-500/10 p-3">
                  <svg class="h-7 w-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                Linked accounts for automatic citation tracking
              </p>
            </div>

            <div class="rounded-xl border border-slate-700/30 bg-slate-900/50 p-4 sm:p-5">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 sm:text-sm">Missing H-Index Data</p>
                  <p class="mt-2 text-xl font-bold text-white sm:text-3xl">{{ facultyMetrics.metrics.missing_h_index || 0 }}</p>
                </div>
                <div class="rounded-lg bg-yellow-500/10 p-3">
                  <svg class="h-7 w-7 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
              </div>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                Profiles without H-index measuring research productivity
              </p>
            </div>

            <div class="rounded-xl border border-slate-700/30 bg-slate-900/50 p-4 sm:p-5">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 sm:text-sm">Missing Citation Counts</p>
                  <p class="mt-2 text-xl font-bold text-white sm:text-3xl">{{ facultyMetrics.metrics.missing_citations || 0 }}</p>
                </div>
                <div class="rounded-lg bg-orange-500/10 p-3">
                  <svg class="h-7 w-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                Profiles without citation data indicating research influence
              </p>
            </div>

            <div class="rounded-xl border border-slate-700/30 bg-slate-900/50 p-4 sm:p-5">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-medium text-slate-400 sm:text-sm">Missing i-10 Index</p>
                  <p class="mt-2 text-xl font-bold text-white sm:text-3xl">{{ facultyMetrics.metrics.missing_i10_index || 0 }}</p>
                </div>
                <div class="rounded-lg bg-red-500/10 p-3">
                  <svg class="h-7 w-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">
                Profiles missing i-10 index showing sustained impact
              </p>
            </div>
          </div>
        </div>
        <!-- College Trends -->
        <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
          <div class="mb-4 flex flex-wrap items-start justify-between gap-4 sm:mb-6">
            <div class="flex-1">
              <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">College-Specific Trends</h2>
              <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                Compare publication trends across different colleges and institutes. Track performance 
                over time to identify high-performing units and areas needing support.
              </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
              <label class="text-[11px] font-medium text-slate-400 sm:text-sm">Select College:</label>
              <select
                v-model="selectedCollege"
                @change="renderCollegeTrend"
                class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-[11px] text-white shadow-sm transition-colors hover:border-emerald-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:px-4 sm:text-sm"
              >
                <option value="">All Colleges</option>
                <option v-for="college in collegeOptions" :key="college" :value="college">
                  {{ college }}
                </option>
              </select>
            </div>
          </div>
          <div class="relative h-56 sm:h-64 lg:h-80">
            <canvas ref="collegeTrendChart"></canvas>
          </div>
        </div>
        <!-- Publications by College Chart -->
        <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
          <div class="mb-4 sm:mb-6">
            <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">Publications by College</h2>
            <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
              Top contributing colleges and institutes ranked by total publication output.
            </p>
          </div>
          <div class="relative h-64 sm:h-72 lg:h-96">
            <canvas ref="collegeYearChart"></canvas>
          </div>
        </div>
        <!-- Detailed Publications Table -->
        <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
          <div class="mb-4 sm:mb-6">
            <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">Detailed College Rankings</h2>
            <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
              Complete breakdown of publication counts by college with percentage contribution 
              to total institutional output.
            </p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-700">
                  <th class="pb-3 text-left text-[11px] font-semibold text-slate-300 sm:text-sm">Rank</th>
                  <th class="pb-3 text-left text-[11px] font-semibold text-slate-300 sm:text-sm">College/Institute</th>
                  <th class="pb-3 text-right text-[11px] font-semibold text-slate-300 sm:text-sm">Publications</th>
                  <th class="pb-3 text-right text-[11px] font-semibold text-slate-300 sm:text-sm">Percentage</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700/50">
                <tr v-for="(college, index) in publicationsByCollegeTop" :key="college.college_institute" 
                    class="transition-colors hover:bg-slate-800/30">
                  <td class="py-3 text-[11px] text-slate-400 sm:py-4 sm:text-sm">
                    <div class="flex items-center gap-2">
                      <span class="font-bold">{{ index + 1 }}</span>
                      <span v-if="index === 0" class="text-yellow-400">🥇</span>
                      <span v-else-if="index === 1" class="text-slate-300">🥈</span>
                      <span v-else-if="index === 2" class="text-orange-400">🥉</span>
                    </div>
                  </td>
                  <td class="py-3 text-[11px] font-medium text-white sm:py-4 sm:text-sm">
                    {{ college.college_institute }}
                  </td>
                  <td class="py-3 text-right text-[11px] font-bold text-emerald-400 sm:py-4 sm:text-sm">
                    {{ college.count }}
                  </td>
                  <td class="py-3 text-right text-[11px] text-slate-400 sm:py-4 sm:text-sm">
                    {{ calculatePercentage(college.count) }}%
                  </td>
                </tr>
              </tbody>
              <tfoot class="border-t-2 border-slate-700">
                <tr>
                  <td colspan="2" class="py-3 text-[11px] font-bold text-white sm:py-4 sm:text-sm">TOTAL</td>
                  <td class="py-3 text-right text-[11px] font-bold text-emerald-400 sm:py-4 sm:text-sm">
                    {{ totalPublications }}
                  </td>
                  <td class="py-3 text-right text-[11px] font-bold text-slate-300 sm:py-4 sm:text-sm">100%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <!-- Quick Action Cards -->
        <div>
          <div class="mb-4 sm:mb-6">
            <h2 class="mb-2 text-lg font-bold text-white sm:text-2xl">System Features</h2>
            <p class="text-[11px] text-slate-400 sm:text-sm">
              Quick access to key features for managing publications, faculty profiles, and generating reports.
            </p>
          </div>
          <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group cursor-pointer rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm transition-all hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/10">
              <div class="mb-4 inline-flex rounded-lg bg-blue-500/10 p-3">
                <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h3 class="mb-2 text-sm font-semibold text-white sm:text-lg">Publications Database</h3>
              <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                Browse, search, and filter all research publications with advanced filtering by year, 
                college, type, and keywords.
              </p>
            </div>

            <div class="group cursor-pointer rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm transition-all hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/10">
              <div class="mb-4 inline-flex rounded-lg bg-green-500/10 p-3">
                <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <h3 class="mb-2 text-sm font-semibold text-white sm:text-lg">Faculty Profiles</h3>
              <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                Manage researcher profiles including Google Scholar integration, H-index tracking, 
                citation counts, and publication history.
              </p>
            </div>

            <div class="group cursor-pointer rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm transition-all hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/10">
              <div class="mb-4 inline-flex rounded-lg bg-purple-500/10 p-3">
                <svg class="h-8 w-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h3 class="mb-2 text-sm font-semibold text-white sm:text-lg">Reports & Analytics</h3>
              <p class="text-[11px] leading-relaxed text-slate-400 sm:text-sm">
                Generate comprehensive reports in PDF, Excel, or CSV formats for administrative use, 
                accreditation requirements, and strategic planning.
              </p>
            </div>
          </div>
        </div>
        <!-- System Information Footer -->
        <div class="rounded-2xl border border-slate-700/50 bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 sm:p-6 backdrop-blur-sm">
          <div class="flex flex-wrap items-center justify-between gap-4 text-center sm:text-left">
            <div>
              <p class="mb-1 text-[11px] font-medium text-slate-300 sm:text-sm">System Status</p>
              <p class="text-[10px] text-slate-500 sm:text-xs">All systems operational</p>
            </div>
            <div>
              <p class="mb-1 text-[11px] font-medium text-slate-300 sm:text-sm">Last Updated</p>
              <p class="text-[10px] text-slate-500 sm:text-xs">{{ dashboardData.last_updated || 'Never' }}</p>
            </div>
            <div>
              <p class="mb-1 text-[11px] font-medium text-slate-300 sm:text-sm">Total Records</p>
              <p class="text-[10px] text-slate-500 sm:text-xs">{{ totalPublications }} publications</p>
            </div>
          </div>
        </div>
      </div>
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
        yearly_trends: [],
        publications_by_type: [],
        publications_by_college: [],
        publications_by_college_year: [],
        last_updated: ''
      },
      facultyMetrics: {
        metrics: {
          total_faculty: 0,
          with_profile: 0,
          missing_citations: 0,
          missing_h_index: 0,
          missing_i10_index: 0
        }
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
    },
    publicationsByCollegeTop() {
      return (this.dashboardData.publications_by_college || []).slice(0, 10);
    },
    totalPublications() {
      return (this.dashboardData.publications_by_college || [])
        .reduce((sum, college) => sum + (Number(college.count) || 0), 0);
    }
  },
  mounted() {
    this.fetchDashboardData();
    this.fetchFacultyMetrics();
  },
  methods: {
    async fetchDashboardData() {
      this.loading = true;
      try {
        const response = await axios.get(`${apiBase}/dashboard`);
        this.dashboardData = response.data.data;
        this.$nextTick(() => {
          this.renderCharts();
        });
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        this.loading = false;
      }
    },

    async fetchFacultyMetrics() {
      try {
        const response = await axios.get(`${apiBase}/dashboard/faculty-metrics`);
        this.facultyMetrics = response.data.data;
      } catch (error) {
        console.error('Error fetching faculty metrics:', error);
      }
    },

    getKpiDescription(label) {
      const descriptions = {
        'Total Publications': 'Cumulative count of all research outputs recorded',
        'Publications 2025': 'Current year publication count',
        'Total Faculty': 'Active faculty members with research profiles',
        'Avg H-Index': 'Average H-index indicating overall research impact',
        'Total Citations': 'Sum of all citations received'
      };
      return descriptions[label] || 'Real-time metric from database';
    },

    calculatePercentage(count) {
      if (!this.totalPublications) return 0;
      return ((count / this.totalPublications) * 100).toFixed(1);
    },

    renderCharts() {
      this.renderYearlyTrends();
      this.renderPublicationType();
      this.renderCollegeYearChart();
      this.renderCollegeTrend();
    },

    renderYearlyTrends() {
      if (this.charts.yearlyTrends) {
        this.charts.yearlyTrends.destroy();
      }

      const ctx = this.$refs.yearlyTrendsChart?.getContext('2d');
      if (!ctx || !this.dashboardData.yearly_trends?.length) return;

      this.charts.yearlyTrends = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.dashboardData.yearly_trends.map(t => t.year),
          datasets: [{
            label: 'Publications',
            data: this.dashboardData.yearly_trends.map(t => Number(t.count) || 0),
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              padding: 12,
              titleColor: '#f1f5f9',
              bodyColor: '#cbd5e1',
              borderColor: 'rgba(148, 163, 184, 0.2)',
              borderWidth: 1
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(148, 163, 184, 0.1)' },
              ticks: { color: '#94a3b8' }
            },
            y: {
              grid: { color: 'rgba(148, 163, 184, 0.1)' },
              ticks: { color: '#94a3b8' }
            }
          }
        }
      });
    },

    renderPublicationType() {
      if (this.charts.publicationType) {
        this.charts.publicationType.destroy();
      }

      const ctx = this.$refs.typeChart?.getContext('2d');
      if (!ctx || !this.dashboardData.publications_by_type?.length) return;

      this.charts.publicationType = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: this.dashboardData.publications_by_type.map(t => t.publication_type),
          datasets: [{
            data: this.dashboardData.publications_by_type.map(t => Number(t.count) || 0),
            backgroundColor: [
              'rgba(59, 130, 246, 0.8)',
              'rgba(16, 185, 129, 0.8)',
              'rgba(245, 158, 11, 0.8)',
              'rgba(239, 68, 68, 0.8)',
              'rgba(139, 92, 246, 0.8)',
            ],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#cbd5e1', padding: 15 }
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              padding: 12,
              titleColor: '#f1f5f9',
              bodyColor: '#cbd5e1'
            }
          }
        }
      });
    },

    renderCollegeYearChart() {
      if (this.charts.collegeYear) {
        this.charts.collegeYear.destroy();
      }

      const ctx = this.$refs.collegeYearChart?.getContext('2d');
      if (!ctx || !this.dashboardData.publications_by_college?.length) return;

      const sorted = [...this.dashboardData.publications_by_college]
        .sort((a, b) => (Number(b.count) || 0) - (Number(a.count) || 0))
        .slice(0, 12);

      this.charts.collegeYear = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: sorted.map(item => item.college_institute),
          datasets: [{
            label: 'Publications',
            data: sorted.map(item => Number(item.count) || 0),
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderRadius: 6
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              padding: 12
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(148, 163, 184, 0.1)' },
              ticks: { color: '#94a3b8' }
            },
            y: {
              grid: { display: false },
              ticks: { color: '#94a3b8' }
            }
          }
        }
      });
    },

    renderCollegeTrend() {
      if (this.charts.collegeTrend) {
        this.charts.collegeTrend.destroy();
      }

      const ctx = this.$refs.collegeTrendChart?.getContext('2d');
      const raw = this.dashboardData.publications_by_college_year || [];
      if (!ctx || !raw.length) return;

      const years = [...new Set(raw.map(r => Number(r.year)))].sort((a, b) => a - b);
      const filtered = this.selectedCollege
        ? raw.filter(r => r.college_institute === this.selectedCollege)
        : raw;

      const byYear = years.map(year => {
        return filtered
          .filter(r => Number(r.year) === year)
          .reduce((sum, row) => sum + (Number(row.count) || 0), 0);
      });

      this.charts.collegeTrend = new Chart(ctx, {
        type: 'line',
        data: {
          labels: years,
          datasets: [{
            label: this.selectedCollege || 'All Colleges',
            data: byYear,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.9)',
              padding: 12
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(148, 163, 184, 0.1)' },
              ticks: { color: '#94a3b8' }
            },
            y: {
              grid: { color: 'rgba(148, 163, 184, 0.1)' },
              ticks: { color: '#94a3b8' }
            }
          }
        }
      });
    }
  },
  beforeUnmount() {
    Object.values(this.charts).forEach(chart => {
      if (chart) chart.destroy();
    });
  }
}
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(15, 23, 42, 0.5);
}

::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.5);
}
</style>
