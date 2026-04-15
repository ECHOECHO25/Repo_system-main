<?php

namespace App\Controllers\Api;

use App\Models\PublicationAuthorLinkModel;
use CodeIgniter\RESTful\ResourceController;

class PublicationAuthorLinksController extends ResourceController
{
    protected $modelName = 'App\Models\PublicationAuthorLinkModel';
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

    public function pending()
    {
        try {
            $search = $this->request->getGet('search');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            $builder = $this->model->builder();
            $builder->select('publication_author_links.*');
            $builder->select('publications.title as publication_title');
            $builder->select('publications.year as publication_year');
            $builder->join('publications', 'publications.id = publication_author_links.publication_id', 'left');
            $builder->where('publication_author_links.status', 'pending');

            if ($search) {
                $builder->groupStart()
                        ->like('publication_author_links.author_name', $search)
                        ->orLike('publications.title', $search)
                        ->groupEnd();
            }

            $total = $builder->countAllResults(false);

            $rows = $builder
                ->orderBy('publication_author_links.id', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $rows,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => (int)$total,
                    'total_pages' => (int)ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function byPublication($publicationId = null)
    {
        try {
            if (!$publicationId) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'publication_id is required'
                ], 400);
            }

            $status = $this->request->getGet('status');

            $builder = $this->model->builder();
            $builder->select('publication_author_links.*');
            $builder->select('faculty.name as faculty_name');
            $builder->join('faculty', 'faculty.id = publication_author_links.faculty_id', 'left');
            $builder->where('publication_author_links.publication_id', $publicationId);

            if ($status) {
                $builder->where('publication_author_links.status', $status);
            }

            $rows = $builder
                ->orderBy('publication_author_links.id', 'ASC')
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $rows
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            $publicationId = $data['publication_id'] ?? null;
            $authorName = $data['author_name'] ?? null;
            $facultyId = $data['faculty_id'] ?? null;
            $status = $data['status'] ?? null;

            if (!$publicationId || !$authorName) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'publication_id and author_name are required'
                ], 400);
            }

            $existing = $this->model->where('publication_id', $publicationId)
                ->where('author_name', $authorName)
                ->first();
            if ($existing) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Match already exists',
                    'data' => $existing
                ]);
            }

            $payload = [
                'publication_id' => $publicationId,
                'author_name' => $authorName,
                'faculty_id' => $facultyId,
                'status' => $status ?? ($facultyId ? 'confirmed' : 'pending'),
                'match_type' => $facultyId ? 'manual' : 'auto'
            ];

            if (!$this->model->insert($payload)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to create match',
                    'errors' => $this->model->errors()
                ], 400);
            }

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Match created'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->model->find($id)) {
                return $this->failNotFound('Match not found');
            }

            $status = $data['status'] ?? null;
            $facultyId = $data['faculty_id'] ?? null;

            if ($status === 'confirmed' && !$facultyId) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'faculty_id is required to confirm a match'
                ], 400);
            }

            $payload = [
                'status' => $status ?? 'confirmed',
                'faculty_id' => $facultyId,
                'match_type' => 'manual'
            ];

            if (!$this->model->update($id, $payload)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update match'
                ], 400);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Match updated'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
