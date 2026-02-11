<?php

namespace App\Controllers\Api;

use App\Models\FacultyModel;
use App\Libraries\AuditLogger;
use CodeIgniter\RESTful\ResourceController;

class FacultyController extends ResourceController
{
    protected $modelName = 'App\Models\FacultyModel';
    protected $format    = 'json';

    public function __construct()
    {
        // Enable CORS
        $origin = getenv('FRONTEND_ORIGIN') ?: 'http://localhost:5173';
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Get all faculty members
     * GET /api/faculty
     */
    public function index()
    {
        try {
            $search = $this->request->getGet('search');
            $college = $this->request->getGet('college');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;
            $sort = $this->request->getGet('sort');
            $order = strtolower($this->request->getGet('order') ?? 'desc');

            $builder = $this->model->builder();
            $builder->where('status', 'active');
            $builder->where('deleted_at', null);

            // Apply filters
            if ($search) {
                $builder->groupStart()
                        ->like('name', $search)
                        ->orLike('email', $search)
                        ->groupEnd();
            }
            if ($college) {
                $builder->where('college_institute', $college);
            }

            // Get total count
            $total = $builder->countAllResults(false);

            // Sorting
            $allowedSorts = [
                'name' => 'name',
                'citations' => 'google_scholar_citations',
                'h_index' => 'h_index',
                'i10_index' => 'i10_index'
            ];
            $sortColumn = $allowedSorts[$sort] ?? 'name';
            $sortOrder = $order === 'asc' ? 'ASC' : 'DESC';

            // Get paginated data
            $faculty = $builder->orderBy($sortColumn, $sortOrder)
                              ->limit($perPage, ($page - 1) * $perPage)
                              ->get()
                              ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $faculty,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single faculty member
     * GET /api/faculty/:id
     */
    public function show($id = null)
    {
        try {
            $faculty = $this->model->find($id);
            
            if (!$faculty) {
                return $this->failNotFound('Faculty member not found');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new faculty member
     * POST /api/faculty
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!empty($data['name'])) {
                $name = preg_replace('/\s+/', ' ', trim($data['name']));
                $data['name'] = $name;
                $builder = $this->model->builder();
                $db = \Config\Database::connect();
                $escapedName = $db->escape(strtolower($name));
                $builder->where('deleted_at', null);
                $builder->where("LOWER(TRIM(name)) = {$escapedName}", null, false);
                $existing = $builder->get()->getRowArray();
                if ($existing) {
                    return $this->fail([
                        'status' => 'error',
                        'message' => 'Faculty name already exists',
                        'code' => 'DUPLICATE_NAME'
                    ], 409);
                }
            }
            
            if (!$this->model->insert($data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to create faculty member',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $id = $this->model->getInsertID();
            $faculty = $this->model->find($id);

            AuditLogger::log('faculty.create', 'faculty', (int)$id, 'Faculty created', [
                'name' => $faculty['name'] ?? null
            ]);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Faculty member created successfully',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update faculty member
     * PUT /api/faculty/:id
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            
            if (!$this->model->find($id)) {
                return $this->failNotFound('Faculty member not found');
            }

            if (!empty($data['name'])) {
                $name = preg_replace('/\s+/', ' ', trim($data['name']));
                $data['name'] = $name;
                $builder = $this->model->builder();
                $db = \Config\Database::connect();
                $escapedName = $db->escape(strtolower($name));
                $builder->where('deleted_at', null);
                $builder->where('id !=', $id);
                $builder->where("LOWER(TRIM(name)) = {$escapedName}", null, false);
                $existing = $builder->get()->getRowArray();
                if ($existing) {
                    return $this->fail([
                        'status' => 'error',
                        'message' => 'Faculty name already exists',
                        'code' => 'DUPLICATE_NAME'
                    ], 409);
                }
            }

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update faculty member',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $faculty = $this->model->find($id);

            AuditLogger::log('faculty.update', 'faculty', (int)$id, 'Faculty updated', [
                'name' => $faculty['name'] ?? null
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Faculty member updated successfully',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete faculty member
     * DELETE /api/faculty/:id
     */
    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) {
                return $this->failNotFound('Faculty member not found');
            }

            if (!$this->model->delete($id)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to delete faculty member'
                ], 400);
            }

            AuditLogger::log('faculty.delete', 'faculty', (int)$id, 'Faculty deleted');

            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Faculty member deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top faculty by citations
     * GET /api/faculty/top-citations
     */
    public function topByCitations()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 10;
            $faculty = $this->model->getTopByCitations($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top faculty by H-index
     * GET /api/faculty/top-hindex
     */
    public function topByHIndex()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 10;
            $faculty = $this->model->getTopByHIndex($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
