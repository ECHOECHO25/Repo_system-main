<?php

namespace App\Controllers\Api;

use App\Models\PublicationModel;
use App\Models\FacultyModel;
use App\Models\TrackingLogModel;
use CodeIgniter\RESTful\ResourceController;

class DashboardController extends ResourceController
{
    protected $modelName = 'App\Models\PublicationModel';
    protected $format    = 'json';

    public function __construct()
    {
        $origin = getenv('FRONTEND_ORIGIN') ?: 'http://localhost:5173';
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Get dashboard statistics and KPIs
     * GET /api/dashboard
     */
    public function index()
    {
        try {
            $cache = null;
            $cacheKey = 'dashboard:index';
            try {
                $cache = \Config\Services::cache();
                $cached = $cache->get($cacheKey);
                if ($cached) {
                    return $this->respond($cached);
                }
            } catch (\Throwable $e) {
                $cache = null;
            }

            $publicationModel = new PublicationModel();
            $facultyModel = new FacultyModel();
            $trackingModel = new TrackingLogModel();

            // Publication statistics
            $pubStats = $publicationModel->getStatistics();
            
            // Faculty metrics
            $facultyMetrics = $facultyModel->getMetrics();
            
            // Get publications by year for trends
            $yearlyTrends = [];
            $years = [];
            for ($year = 2017; $year <= 2025; $year++) {
                $count = $publicationModel->where('year', $year)->countAllResults();
                $yearlyTrends[] = [
                    'year' => $year,
                    'count' => $count
                ];
                $years[] = $year;
            }

            $collegeTrends = $publicationModel->getCollegeTrends($years);

            // Recent activity
            $recentLogs = $trackingModel->getRecent(10);
            
            // Alerts
            $alerts = [
                [
                    'type' => 'Missing H-Index',
                    'description' => 'Faculty without H-index data',
                    'count' => $facultyMetrics['missing_h_index'],
                    'severity' => 'warning',
                    'status' => 'Review'
                ],
                [
                    'type' => 'Low Citations',
                    'description' => 'Faculty with <100 citations',
                    'count' => $facultyMetrics['low_citations'],
                    'severity' => 'info',
                    'status' => 'Monitor'
                ],
                [
                    'type' => 'No Recent Publications',
                    'description' => 'Faculty with no 2024-2025 publications',
                    'count' => count($facultyModel->getInactiveFaculty([2024, 2025])),
                    'severity' => 'warning',
                    'status' => 'Action Needed'
                ]
            ];

            // KPIs
            $kpis = [
                [
                    'label' => 'Total Publications',
                    'value' => $pubStats['total_publications'],
                    'icon' => 'book',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Publications 2025',
                    'value' => $publicationModel->where('year', 2025)->countAllResults(),
                    'icon' => 'file-text',
                    'color' => 'success'
                ],
                [
                    'label' => 'Total Faculty',
                    'value' => $facultyMetrics['total_faculty'],
                    'icon' => 'users',
                    'color' => 'info'
                ],
                [
                    'label' => 'Avg H-Index',
                    'value' => $facultyMetrics['avg_h_index'],
                    'icon' => 'trending-up',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Total Citations',
                    'value' => $pubStats['total_citations'],
                    'icon' => 'award',
                    'color' => 'danger'
                ]
            ];

            $payload = [
                'status' => 'success',
                'data' => [
                    'kpis' => $kpis,
                    'alerts' => $alerts,
                    'yearly_trends' => $yearlyTrends,
                    'publications_by_type' => $pubStats['by_type'],
                    'publications_by_college' => $pubStats['by_college'],
                    'publications_by_college_year' => $collegeTrends,
                    'recent_activity' => $recentLogs,
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];

            if ($cache) {
                try {
                    $cache->save($cacheKey, $payload, 60);
                } catch (\Throwable $e) {
                    // Ignore cache errors
                }
            }

            return $this->respond($payload);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get publication summary by year
     * GET /api/dashboard/publications-summary
     */
    public function publicationsSummary()
    {
        try {
            $cache = null;
            $cacheKey = 'dashboard:publications-summary';
            try {
                $cache = \Config\Services::cache();
                $cached = $cache->get($cacheKey);
                if ($cached) {
                    return $this->respond($cached);
                }
            } catch (\Throwable $e) {
                $cache = null;
            }

            $publicationModel = new PublicationModel();
            $stats = $publicationModel->getStatistics();

            $payload = [
                'status' => 'success',
                'data' => [
                    'total' => $stats['total_publications'],
                    'by_year' => $stats['by_year'],
                    'by_type' => $stats['by_type'],
                    'by_college' => $stats['by_college']
                ]
            ];

            if ($cache) {
                try {
                    $cache->save($cacheKey, $payload, 60);
                } catch (\Throwable $e) {
                    // Ignore cache errors
                }
            }

            return $this->respond($payload);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculty metrics summary
     * GET /api/dashboard/faculty-metrics
     */
    public function facultyMetrics()
    {
        try {
            $cache = null;
            $cacheKey = 'dashboard:faculty-metrics';
            try {
                $cache = \Config\Services::cache();
                $cached = $cache->get($cacheKey);
                if ($cached) {
                    return $this->respond($cached);
                }
            } catch (\Throwable $e) {
                $cache = null;
            }

            $facultyModel = new FacultyModel();
            $metrics = $facultyModel->getMetrics();
            $topCitations = $facultyModel->getTopByCitations(5);
            $topHIndex = $facultyModel->getTopByHIndex(5);
            $inactiveFaculty = $facultyModel->getInactiveFaculty([2024, 2025]);

            $payload = [
                'status' => 'success',
                'data' => [
                    'metrics' => $metrics,
                    'top_by_citations' => $topCitations,
                    'top_by_h_index' => $topHIndex,
                    'inactive_faculty_count' => count($inactiveFaculty),
                    'inactive_faculty' => array_slice($inactiveFaculty, 0, 10)
                ]
            ];

            if ($cache) {
                try {
                    $cache->save($cacheKey, $payload, 60);
                } catch (\Throwable $e) {
                    // Ignore cache errors
                }
            }

            return $this->respond($payload);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity statistics
     * GET /api/dashboard/activity
     */
    public function activity()
    {
        try {
            $days = $this->request->getGet('days') ?? 30;
            $cacheKey = 'dashboard:activity:' . $days;
            $cache = null;
            try {
                $cache = \Config\Services::cache();
                $cached = $cache->get($cacheKey);
                if ($cached) {
                    return $this->respond($cached);
                }
            } catch (\Throwable $e) {
                $cache = null;
            }

            $trackingModel = new TrackingLogModel();
            
            $stats = $trackingModel->getActivityStats($days);
            $recentLogs = $trackingModel->getRecent(20);

            $payload = [
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'recent_logs' => $recentLogs,
                    'period_days' => $days
                ]
            ];

            if ($cache) {
                try {
                    $cache->save($cacheKey, $payload, 60);
                } catch (\Throwable $e) {
                    // Ignore cache errors
                }
            }

            return $this->respond($payload);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
