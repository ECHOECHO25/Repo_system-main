<?php

namespace App\Controllers\Api;

use App\Models\PublicationModel;
use App\Models\PublicationAuthorLinkModel;
use App\Models\FacultyModel;
use App\Libraries\AuditLogger;
use CodeIgniter\RESTful\ResourceController;

class PublicationsController extends ResourceController
{
    protected $modelName = 'App\Models\PublicationModel';
    protected $format    = 'json';

    public function __construct()
    {
                $requestOrigin = trim((string) service('request')->getHeaderLine('Origin'));
        $configured = rtrim(trim((string) (getenv('FRONTEND_ORIGIN') ?: 'http://localhost:5173')), '/');

        if ($requestOrigin !== '' && preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#i', $requestOrigin)) {
            $origin = rtrim($requestOrigin, '/');
        } elseif ($configured !== '' && $configured !== '*') {
            $origin = $configured;
        } else {
            $origin = 'http://localhost:5173';
        }
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
            $reviewStatus = trim((string)($this->request->getGet('review_status') ?? ''));
            $role = (string)(session()->get('role') ?? '');
            $userId = (int)(session()->get('user_id') ?? 0);
            $isReviewer = in_array($role, ['admin', 'editor'], true);
            $isResearcher = $role === 'researcher' && $userId > 0;
            // For admin/editor, default is review mode (show all).
            // For researcher, show own submissions (including rejected/pending) so they can resubmit.
            // Public/others default to matched-only approved.
            $defaultMatchedOnly = ($isReviewer || $isResearcher) ? 0 : 1;
            $matchedOnly = (int)($this->request->getGet('matched_only') ?? $defaultMatchedOnly) === 1;

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
            if ($isReviewer && in_array($reviewStatus, ['pending_review', 'approved', 'rejected'], true)) {
                $builder->where('review_status', $reviewStatus);
            }
            if ($isResearcher) {
                $builder->where('submitted_by_user_id', $userId);
                if (!$reviewStatus) {
                    $builder->whereIn('review_status', ['pending_review', 'approved', 'rejected']);
                }
            } elseif (!$isReviewer) {
                $builder->where('review_status', 'approved');
            }
            if ($matchedOnly) {
                $builder->where(
                    "NOT EXISTS (
                        SELECT 1
                        FROM publication_author_links pal
                        WHERE pal.publication_id = publications.id
                          AND pal.status = 'pending'
                    )",
                    null,
                    false
                );
                $builder->where(
                    "EXISTS (
                        SELECT 1
                        FROM publication_author_links pal2
                        WHERE pal2.publication_id = publications.id
                          AND pal2.status = 'confirmed'
                    )",
                    null,
                    false
                );
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
            if (!$this->canCreatePublication()) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

            $data = $this->request->getJSON(true);
            $data['authors'] = $this->normalizeAuthors($data['authors'] ?? null);
            $role = (string)(session()->get('role') ?? '');
            $userId = (int)(session()->get('user_id') ?? 0);
            $isReviewer = in_array($role, ['admin', 'editor'], true);
            $now = date('Y-m-d H:i:s');
            $data['review_status'] = $isReviewer ? 'approved' : 'pending_review';
            $data['submitted_by_user_id'] = $userId > 0 ? $userId : null;
            $data['reviewed_by_user_id'] = $isReviewer && $userId > 0 ? $userId : null;
            $data['reviewed_at'] = $isReviewer ? $now : null;
            $data['review_remarks'] = null;

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
            $facultyLookup = $this->buildFacultyLookup();
            $linkModel = new PublicationAuthorLinkModel();
            $this->matchPublicationAuthors(
                $linkModel,
                $facultyLookup,
                (int)$id,
                $publication['authors'] ?? json_encode([])
            );

            AuditLogger::log('publication.create', 'publication', (int)$id, 'Publication created', [
                'title' => $publication['title'] ?? null,
                'year' => $publication['year'] ?? null
            ]);

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
            $publication = $this->model->find($id);
            if (!$publication) {
                return $this->failNotFound('Publication not found');
            }

            $role = (string)(session()->get('role') ?? '');
            $userId = (int)(session()->get('user_id') ?? 0);
            $canManage = $this->canFullyManagePublications();
            $canResearcherResubmit = (
                $role === 'researcher'
                && $userId > 0
                && (int)($publication['submitted_by_user_id'] ?? 0) === $userId
                && (string)($publication['review_status'] ?? '') === 'rejected'
            );

            if (!$canManage && !$canResearcherResubmit) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

            $data = $this->request->getJSON(true);
            if (array_key_exists('authors', $data)) {
                $data['authors'] = $this->normalizeAuthors($data['authors']);
            }
            unset($data['review_status'], $data['submitted_by_user_id'], $data['reviewed_by_user_id'], $data['reviewed_at'], $data['review_remarks']);

            if ($canResearcherResubmit) {
                $data['review_status'] = 'pending_review';
                $data['reviewed_by_user_id'] = null;
                $data['reviewed_at'] = null;
                $data['review_remarks'] = null;
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
            
            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update publication',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $publication = $this->model->find($id);

            AuditLogger::log('publication.update', 'publication', (int)$id, 'Publication updated', [
                'title' => $publication['title'] ?? null,
                'year' => $publication['year'] ?? null,
                'resubmitted' => $canResearcherResubmit
            ]);

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
            if (!$this->canFullyManagePublications()) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

            if (!$this->model->find($id)) {
                return $this->failNotFound('Publication not found');
            }

            if (!$this->model->delete($id)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to delete publication'
                ], 400);
            }

            AuditLogger::log('publication.delete', 'publication', (int)$id, 'Publication deleted');

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

    public function reviewQueue()
    {
        try {
            if (!$this->canReviewPublications()) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

            $year = $this->request->getGet('year');
            $college = $this->request->getGet('college');
            $type = $this->request->getGet('type');
            $search = $this->request->getGet('search');
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 20);
            $page = $page > 0 ? $page : 1;
            $perPage = $perPage > 0 ? min($perPage, 200) : 20;

            $builder = $this->model->builder();
            $builder->where('review_status', 'pending_review');

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

            $total = $builder->countAllResults(false);
            $rows = $builder->orderBy('created_at', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $rows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
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

    public function review($id = null)
    {
        try {
            if (!$this->canReviewPublications()) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

            $publication = $this->model->find($id);
            if (!$publication) {
                return $this->failNotFound('Publication not found');
            }

            $payload = $this->request->getJSON(true);
            $status = trim((string)($payload['status'] ?? ''));
            if (!in_array($status, ['approved', 'rejected'], true)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Invalid review status'
                ], 400);
            }

            $userId = (int)(session()->get('user_id') ?? 0);
            $data = [
                'review_status' => $status,
                'reviewed_by_user_id' => $userId > 0 ? $userId : null,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'review_remarks' => trim((string)($payload['remarks'] ?? '')) ?: null,
            ];

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update review status',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $updated = $this->model->find($id);

            if ($status === 'approved' && $updated) {
                $facultyLookup = $this->buildFacultyLookup();
                $linkModel = new PublicationAuthorLinkModel();
                $this->matchPublicationAuthors(
                    $linkModel,
                    $facultyLookup,
                    (int)$id,
                    (string)($updated['authors'] ?? json_encode([]))
                );
            }

            $updated = $this->model->find($id);
            AuditLogger::log(
                $status === 'approved' ? 'publication.approve' : 'publication.reject',
                'publication',
                (int)$id,
                $status === 'approved' ? 'Publication approved' : 'Publication rejected',
                [
                    'title' => $updated['title'] ?? null,
                    'review_remarks' => $updated['review_remarks'] ?? null,
                ]
            );

            return $this->respond([
                'status' => 'success',
                'message' => $status === 'approved' ? 'Publication approved' : 'Publication rejected',
                'data' => $updated
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
            if (!$this->canFullyManagePublications()) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Forbidden'
                ], 403);
            }

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

            $facultyLookup = $this->buildFacultyLookup();
            $linkModel = new PublicationAuthorLinkModel();

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
                    $publicationId = $this->model->getInsertID();
                    $this->matchPublicationAuthors($linkModel, $facultyLookup, $publicationId, $pub['authors']);
                } else {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'data' => $pub,
                        'errors' => $this->model->errors()
                    ];
                }
            }

            AuditLogger::log('publication.import', 'publication', null, 'Publications imported', [
                'total' => count($publications),
                'inserted' => $inserted,
                'failed' => $failed,
                'duplicates' => $duplicates
            ]);

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

    private function canCreatePublication(): bool
    {
        $role = (string)(session()->get('role') ?? '');
        return in_array($role, ['admin', 'editor', 'researcher'], true);
    }

    private function canFullyManagePublications(): bool
    {
        $role = (string)(session()->get('role') ?? '');
        return in_array($role, ['admin', 'editor'], true);
    }

    private function canReviewPublications(): bool
    {
        return $this->canFullyManagePublications();
    }

    private function buildFacultyLookup(): array
    {
        $facultyModel = new FacultyModel();
        $rows = $facultyModel->select('id, name')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->findAll();

        $lookup = [];
        foreach ($rows as $row) {
            $normalized = $this->normalizePersonName($row['name'] ?? '');
            if ($normalized === '') {
                continue;
            }
            if (!isset($lookup[$normalized])) {
                $lookup[$normalized] = [];
            }
            $lookup[$normalized][] = (int)$row['id'];
        }

        return $lookup;
    }

    private function matchPublicationAuthors(
        PublicationAuthorLinkModel $linkModel,
        array $facultyLookup,
        int $publicationId,
        string $authorsJson
    ): void {
        $authors = json_decode($authorsJson, true);
        if (!is_array($authors)) {
            return;
        }

        foreach ($authors as $author) {
            $authorName = trim((string)$author);
            if ($authorName === '') {
                continue;
            }

            $normalized = $this->normalizePersonName($authorName);
            $matches = $normalized !== '' && isset($facultyLookup[$normalized])
                ? $facultyLookup[$normalized]
                : [];
            $existingByAuthor = $this->findLinkByNormalizedAuthor($linkModel, $publicationId, $normalized);

            if (count($matches) === 1) {
                $facultyId = $matches[0];
                $existing = $linkModel->where('publication_id', $publicationId)
                    ->where('faculty_id', $facultyId)
                    ->first();
                if ($existing) {
                    continue;
                }

                if ($existingByAuthor) {
                    $linkModel->update((int)$existingByAuthor['id'], [
                        'faculty_id' => $facultyId,
                        'author_name' => $authorName,
                        'match_type' => 'auto',
                        'status' => 'confirmed',
                    ]);
                } else {
                    $linkModel->insert([
                        'publication_id' => $publicationId,
                        'faculty_id' => $facultyId,
                        'author_name' => $authorName,
                        'match_type' => 'auto',
                        'status' => 'confirmed',
                    ]);
                }
                continue;
            }

            if ($existingByAuthor) {
                continue;
            }
            $linkModel->insert([
                'publication_id' => $publicationId,
                'faculty_id' => null,
                'author_name' => $authorName,
                'match_type' => 'auto',
                'status' => 'pending',
            ]);
        }
    }

    private function findLinkByNormalizedAuthor(
        PublicationAuthorLinkModel $linkModel,
        int $publicationId,
        string $normalizedAuthor
    ): ?array {
        if ($normalizedAuthor === '') {
            return null;
        }

        $db = \Config\Database::connect();
        $escaped = $db->escape($normalizedAuthor);

        $row = $linkModel->builder()
            ->where('publication_id', $publicationId)
            ->where("LOWER(TRIM(author_name)) = {$escaped}", null, false)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function normalizePersonName(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }
}
