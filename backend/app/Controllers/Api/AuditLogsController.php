<?php

namespace App\Controllers\Api;

use App\Models\AuditLogModel;
use App\Libraries\AuditLogger;
use CodeIgniter\RESTful\ResourceController;

class AuditLogsController extends ResourceController
{
    protected $format = 'json';
    private array $allowedActions = [
        'login',
        'logout',
        'faculty.create',
        'faculty.update',
        'faculty.delete',
        'publication.create',
        'publication.update',
        'publication.delete',
        'faculty.import',
        'faculty.export',
        'publication.import',
        'publication.export'
    ];

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

    public function index()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;
            $search = $this->request->getGet('search');

            $model = new AuditLogModel();
            $builder = $model->builder();

            $builder->whereIn('action', $this->allowedActions);

            if ($search) {
                $builder->groupStart()
                    ->like('action', $search)
                    ->orLike('entity_type', $search)
                    ->orLike('username', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
            }

            $total = $builder->countAllResults(false);

            $logs = $builder->orderBy('created_at', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function record()
    {
        try {
            $data = $this->request->getJSON(true);
            $action = $data['action'] ?? '';
            $metadata = $data['metadata'] ?? null;

            if (!in_array($action, $this->allowedActions, true)) {
                return $this->fail(['status' => 'error', 'message' => 'Invalid action'], 400);
            }

            $entityType = null;
            if (str_starts_with($action, 'faculty.')) {
                $entityType = 'faculty';
            } elseif (str_starts_with($action, 'publication.')) {
                $entityType = 'publication';
            } elseif ($action === 'login' || $action === 'logout') {
                $entityType = 'user';
            }

            AuditLogger::log($action, $entityType, null, $data['description'] ?? null, is_array($metadata) ? $metadata : null);

            return $this->respond([
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
