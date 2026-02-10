<?php

namespace App\Controllers\Api;

use App\Models\FacultyModel;
use CodeIgniter\RESTful\ResourceController;

class FacultyController extends ResourceController
{
    protected $modelName = 'App\Models\FacultyModel';
    protected $format    = 'json';

    public function __construct()
    {
        // Enable CORS
        header('Access-Control-Allow-Origin: *');
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

            // Get paginated data
            $faculty = $builder->orderBy('name', 'ASC')
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
            
            if (!$this->model->insert($data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to create faculty member',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $id = $this->model->getInsertID();
            $faculty = $this->model->find($id);

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

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update faculty member',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $faculty = $this->model->find($id);

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
