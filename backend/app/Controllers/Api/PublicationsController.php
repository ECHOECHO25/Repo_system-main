<?php

namespace App\Controllers\Api;

use App\Models\PublicationModel;
use CodeIgniter\RESTful\ResourceController;

class PublicationsController extends ResourceController
{
    protected $modelName = 'App\Models\PublicationModel';
    protected $format    = 'json';

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Get all publications with filters
     * GET /api/publications
     */
    public function index()
    {
        try {
            $publicationModel = new PublicationModel();
            
            // Get query parameters
            $year = $this->request->getGet('year');
            $college = $this->request->getGet('college');
            $type = $this->request->getGet('type');
            $search = $this->request->getGet('search');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            $builder = $publicationModel->builder();

            // Apply filters
            if ($year) {
                $builder->where('year', $year);
            }
            if ($college) {
                $builder->like('college_institute', $college);
            }
            if ($type) {
                $builder->where('publication_type', $type);
            }
            if ($search) {
                $builder->groupStart()
                        ->like('title', $search)
                        ->orLike('authors', $search)
                        ->orLike('keywords', $search)
                        ->groupEnd();
            }

            // Get total count
            $total = $builder->countAllResults(false);

            // Get paginated data
            $publications = $builder->orderBy('year', 'DESC')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit($perPage, ($page - 1) * $perPage)
                                   ->get()
                                   ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $publications,
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
     * Get single publication
     * GET /api/publications/:id
     */
    public function show($id = null)
    {
        try {
            $publication = $this->model->find($id);
            
            if (!$publication) {
                return $this->failNotFound('Publication not found');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $publication
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new publication
     * POST /api/publications
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            $data['authors'] = $this->normalizeAuthors($data['authors'] ?? null);

            if (!empty($data['year']) && !empty($data['title'])) {
                $duplicate = $this->model
                    ->where('year', $data['year'])
                    ->where('title', $data['title'])
                    ->first();
                if ($duplicate) {
                    return $this->fail([
                        'status' => 'error',
                        'message' => 'Duplicate publication (same year and title)'
                    ], 409);
                }
            }
            
            if (!$this->model->insert($data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to create publication',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $id = $this->model->getInsertID();
            $publication = $this->model->find($id);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Publication created successfully',
                'data' => $publication
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update publication
     * PUT /api/publications/:id
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);
            if (array_key_exists('authors', $data)) {
                $data['authors'] = $this->normalizeAuthors($data['authors']);
            }

            if (!empty($data['year']) && !empty($data['title'])) {
                $duplicate = $this->model
                    ->where('year', $data['year'])
                    ->where('title', $data['title'])
                    ->where('id !=', $id)
                    ->first();
                if ($duplicate) {
                    return $this->fail([
                        'status' => 'error',
                        'message' => 'Duplicate publication (same year and title)'
                    ], 409);
                }
            }
            
            if (!$this->model->find($id)) {
                return $this->failNotFound('Publication not found');
            }

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update publication',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $publication = $this->model->find($id);

            return $this->respond([
                'status' => 'success',
                'message' => 'Publication updated successfully',
                'data' => $publication
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete publication
     * DELETE /api/publications/:id
     */
    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) {
                return $this->failNotFound('Publication not found');
            }

            if (!$this->model->delete($id)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to delete publication'
                ], 400);
            }

            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Publication deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get publications by year
     * GET /api/publications/by-year/:year
     */
    public function byYear($year = null)
    {
        try {
            $publications = $this->model->getByYear($year);

            return $this->respond([
                'status' => 'success',
                'data' => $publications,
                'year' => $year
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent publications
     * GET /api/publications/recent
     */
    public function recent()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 10;
            $publications = $this->model->getRecent($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $publications
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import publications
     * POST /api/publications/bulk-import
     */
    public function bulkImport()
    {
        try {
            $publications = $this->request->getJSON(true);
            
            if (!is_array($publications) || empty($publications)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Invalid data format'
                ], 400);
            }

            $inserted = 0;
            $failed = 0;
            $duplicates = 0;
            $errors = [];

            foreach ($publications as $index => $pub) {
                $pub['authors'] = $this->normalizeAuthors($pub['authors'] ?? null);
                if (!empty($pub['year']) && !empty($pub['title'])) {
                    $exists = $this->model
                        ->where('year', $pub['year'])
                        ->where('title', $pub['title'])
                        ->first();
                    if ($exists) {
                        $duplicates++;
                        continue;
                    }
                }
                if ($this->model->insert($pub)) {
                    $inserted++;
                } else {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'data' => $pub,
                        'errors' => $this->model->errors()
                    ];
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => "Bulk import completed",
                'summary' => [
                    'total' => count($publications),
                    'inserted' => $inserted,
                    'failed' => $failed,
                    'duplicates' => $duplicates
                ],
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizeAuthors($value): string
    {
        if (is_array($value)) {
            $authors = $value;
        } elseif (is_string($value)) {
            $normalized = str_ireplace([' and ', '&'], ',', $value);
            $authors = preg_split('/\s*[,;\/]\s*/', $normalized);
        } else {
            return json_encode([]);
        }

        $cleaned = [];
        foreach ($authors as $author) {
            $author = trim((string)$author);
            if ($author !== '') {
                $cleaned[] = $author;
            }
        }

        return json_encode($cleaned);
    }
}
