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
            $campus = $this->request->getGet('campus');
            $teachingStatus = $this->request->getGet('teaching_status');
            $sex = $this->request->getGet('sex');
            $position = $this->request->getGet('position');
            $collegeDivision = $this->request->getGet('college_division');
            $department = $this->request->getGet('department_office_unit');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;
            $sort = $this->request->getGet('sort');
            $order = strtolower($this->request->getGet('order') ?? 'desc');

            $baseBuilder = $this->model->builder();
            $baseBuilder->where('faculty.status', 'active');
            $baseBuilder->where('faculty.deleted_at', null);
            $needsMasterlist = $search || $campus || $teachingStatus || $sex || $position || $collegeDivision || $department;
            $joinClause = "fm.id = (SELECT fm2.id FROM faculty_masterlist fm2 WHERE LOWER(TRIM(fm2.name)) = LOWER(TRIM(faculty.name)) ORDER BY fm2.id ASC LIMIT 1)";
            if ($needsMasterlist) {
                $baseBuilder->join('faculty_masterlist fm', $joinClause, 'left', false);
            }

            // Apply filters
            if ($search) {
                $baseBuilder->groupStart()
                        ->like('faculty.name', $search)
                        ->orLike('faculty.email', $search)
                        ->orLike('fm.campus', $search)
                        ->orLike('fm.teaching_status', $search)
                        ->orLike('fm.sex', $search)
                        ->orLike('fm.position', $search)
                        ->orLike('fm.college_division', $search)
                        ->orLike('fm.department_office_unit', $search)
                        ->groupEnd();
            }
            if ($college) {
                $baseBuilder->where('faculty.college_institute', $college);
            }
            if ($campus) {
                $baseBuilder->where('fm.campus', $campus);
            }
            if ($teachingStatus) {
                $baseBuilder->where('fm.teaching_status', $teachingStatus);
            }
            if ($sex) {
                $baseBuilder->where('fm.sex', $sex);
            }
            if ($position) {
                $baseBuilder->like('fm.position', $position);
            }
            if ($collegeDivision) {
                $baseBuilder->like('fm.college_division', $collegeDivision);
            }
            if ($department) {
                $baseBuilder->like('fm.department_office_unit', $department);
            }

            // Get total count
            $totalRow = $baseBuilder
                ->select('COUNT(DISTINCT faculty.id) as total', false)
                ->get()
                ->getRow();
            $total = (int)($totalRow->total ?? 0);

            // Sorting
            $allowedSorts = [
                'name' => 'name',
                'citations' => 'google_scholar_citations',
                'h_index' => 'h_index',
                'i10_index' => 'i10_index',
                'publications' => 'publication_count'
            ];
            $sortColumn = $allowedSorts[$sort] ?? 'name';
            $sortOrder = $order === 'asc' ? 'ASC' : 'DESC';

            // Get paginated data
            $builder = $this->model->builder();
            $builder->select('faculty.*');
            $builder->select('fm.campus as masterlist_campus');
            $builder->select('fm.teaching_status as masterlist_teaching_status');
            $builder->select('fm.position as masterlist_position');
            $builder->select('fm.college_division as masterlist_college_division');
            $builder->select('fm.department_office_unit as masterlist_department_office_unit');
            $builder->select('fm.sex as masterlist_sex');
            $builder->select('COUNT(pal.id) as publication_count', false);
            $builder->join('faculty_masterlist fm', $joinClause, 'left', false);
            $builder->join('publication_author_links pal', "pal.faculty_id = faculty.id AND pal.status = 'confirmed'", 'left');
            $builder->where('faculty.status', 'active');
            $builder->where('faculty.deleted_at', null);
            if ($search) {
                $builder->groupStart()
                        ->like('faculty.name', $search)
                        ->orLike('faculty.email', $search)
                        ->orLike('fm.campus', $search)
                        ->orLike('fm.teaching_status', $search)
                        ->orLike('fm.sex', $search)
                        ->orLike('fm.position', $search)
                        ->orLike('fm.college_division', $search)
                        ->orLike('fm.department_office_unit', $search)
                        ->groupEnd();
            }
            if ($college) {
                $builder->where('faculty.college_institute', $college);
            }
            if ($campus) {
                $builder->where('fm.campus', $campus);
            }
            if ($teachingStatus) {
                $builder->where('fm.teaching_status', $teachingStatus);
            }
            if ($sex) {
                $builder->where('fm.sex', $sex);
            }
            if ($position) {
                $builder->like('fm.position', $position);
            }
            if ($collegeDivision) {
                $builder->like('fm.college_division', $collegeDivision);
            }
            if ($department) {
                $builder->like('fm.department_office_unit', $department);
            }
            $builder->groupBy('faculty.id');

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
