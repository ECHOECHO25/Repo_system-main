<?php

namespace App\Controllers\Api;

use App\Models\FacultyMasterlistModel;
use App\Libraries\AuditLogger;
use CodeIgniter\RESTful\ResourceController;

class FacultyMasterlistController extends ResourceController
{
    protected $modelName = 'App\Models\FacultyMasterlistModel';
    protected $format = 'json';

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
            $search = $this->request->getGet('search');
            $campus = $this->normalizeCampus($this->request->getGet('campus'));
            $teachingStatus = $this->normalizeTeachingStatus($this->request->getGet('teaching_status'));
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 20);
            $page = $page > 0 ? $page : 1;
            $perPage = $perPage > 0 ? min($perPage, 5000) : 20;

            $builder = $this->model->builder();

            if ($search) {
                $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('position', $search)
                    ->orLike('college_division', $search)
                    ->orLike('department_office_unit', $search)
                    ->orLike('teaching_status', $search)
                    ->groupEnd();
            }

            if ($campus !== null) {
                $builder->where('campus', $campus);
            }
            if ($teachingStatus !== null) {
                $builder->where('teaching_status', $teachingStatus);
            }

            $total = $builder->countAllResults(false);
            $rows = $builder->orderBy('name', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $rows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
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

    public function create()
    {
        try {
            $payload = $this->request->getJSON(true);
            $data = $this->normalizeRow($payload);

            if (empty($data['name'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Name is required'
                ], 400);
            }

            if ($this->isDuplicate($data['name'], $data['campus'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Record already exists',
                    'code' => 'DUPLICATE_RECORD'
                ], 409);
            }

            if (!$this->model->insert($data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to create record',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $id = $this->model->getInsertID();
            $row = $this->model->find($id);

            AuditLogger::log('masterlist.create', 'faculty_masterlist', (int)$id, 'Masterlist record created', [
                'name' => $row['name'] ?? null
            ]);

            return $this->respondCreated([
                'status' => 'success',
                'data' => $row
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
            $existing = $this->model->find($id);
            if (!$existing) {
                return $this->failNotFound('Record not found');
            }

            $payload = $this->request->getJSON(true);
            $data = $this->normalizeRow($payload);

            if (empty($data['name'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Name is required'
                ], 400);
            }

            if ($this->isDuplicate($data['name'], $data['campus'], $id)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Record already exists',
                    'code' => 'DUPLICATE_RECORD'
                ], 409);
            }

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to update record',
                    'errors' => $this->model->errors()
                ], 400);
            }

            $row = $this->model->find($id);
            AuditLogger::log('masterlist.update', 'faculty_masterlist', (int)$id, 'Masterlist record updated', [
                'name' => $row['name'] ?? null
            ]);

            return $this->respond([
                'status' => 'success',
                'data' => $row
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id = null)
    {
        try {
            $row = $this->model->find($id);
            if (!$row) {
                return $this->failNotFound('Record not found');
            }

            if (!$this->model->delete($id)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Failed to delete record'
                ], 400);
            }

            AuditLogger::log('masterlist.delete', 'faculty_masterlist', (int)$id, 'Masterlist record deleted', [
                'name' => $row['name'] ?? null
            ]);

            return $this->respondDeleted([
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkImport()
    {
        try {
            $payload = $this->request->getJSON(true);
            $rows = $payload['rows'] ?? [];

            if (!is_array($rows) || count($rows) === 0) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'No rows provided'
                ], 400);
            }

            $inserted = 0;
            $skipped = [];
            $seen = [];

            foreach ($rows as $index => $row) {
                $data = $this->normalizeRow($row);
                $rowNumber = $index + 1;

                if (empty($data['name'])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Name is required'];
                    continue;
                }

                $key = strtolower($data['name']) . '|' . strtolower($data['campus'] ?? '');
                if (isset($seen[$key]) || $this->isDuplicate($data['name'], $data['campus'])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Duplicate record'];
                    $seen[$key] = true;
                    continue;
                }

                if (!$this->model->insert($data)) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Failed to save record'];
                    continue;
                }

                $inserted++;
                $seen[$key] = true;
            }

            AuditLogger::log('masterlist.import', 'faculty_masterlist', null, 'Masterlist import completed', [
                'inserted' => $inserted,
                'skipped' => count($skipped)
            ]);

            return $this->respond([
                'status' => 'success',
                'inserted' => $inserted,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            $search = $this->request->getGet('search');
            $campus = $this->normalizeCampus($this->request->getGet('campus'));

            $builder = $this->model->builder();
            if ($search) {
                $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('position', $search)
                    ->orLike('college_division', $search)
                    ->orLike('department_office_unit', $search)
                    ->orLike('teaching_status', $search)
                    ->groupEnd();
            }
            if ($campus !== null) {
                $builder->where('campus', $campus);
            }

            $rows = $builder->orderBy('name', 'ASC')->get()->getResultArray();

            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, ['Campus', 'Name', 'Position', 'College/Division', 'Department/Office/Unit', 'Sex', 'Teaching Status']);
            foreach ($rows as $row) {
                fputcsv($csv, [
                    $row['campus'] ?? '',
                    $row['name'] ?? '',
                    $row['position'] ?? '',
                    $row['college_division'] ?? '',
                    $row['department_office_unit'] ?? '',
                    $row['sex'] ?? '',
                    $row['teaching_status'] ?? ''
                ]);
            }
            rewind($csv);
            $output = stream_get_contents($csv);
            fclose($csv);

            AuditLogger::log('masterlist.export', 'faculty_masterlist', null, 'Masterlist export completed', [
                'count' => count($rows)
            ]);

            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="faculty-masterlist.csv"')
                ->setBody($output);
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizeCampus($value): ?string
    {
        $campus = is_string($value) ? trim($value) : '';
        return $campus === '' ? null : $campus;
    }

    private function normalizeRow(array $row): array
    {
        $campus = $this->normalizeCampus($row['campus'] ?? null);
        $name = isset($row['name']) ? preg_replace('/\s+/', ' ', trim((string)$row['name'])) : '';
        $position = isset($row['position']) ? trim((string)$row['position']) : null;
        $college = isset($row['college_division']) ? trim((string)$row['college_division']) : null;
        $department = isset($row['department_office_unit']) ? trim((string)$row['department_office_unit']) : null;
        $sex = isset($row['sex']) ? strtoupper(trim((string)$row['sex'])) : null;
        $teachingStatus = isset($row['teaching_status']) ? trim((string)$row['teaching_status']) : null;
        if ($sex !== null && $sex !== '') {
            $sex = $sex[0] === 'F' ? 'F' : 'M';
        } else {
            $sex = null;
        }
        $teachingStatus = $this->normalizeTeachingStatus($teachingStatus);

        return [
            'campus' => $campus,
            'name' => $name,
            'position' => $position ?: null,
            'college_division' => $college ?: null,
            'department_office_unit' => $department ?: null,
            'sex' => $sex,
            'teaching_status' => $teachingStatus
        ];
    }

    private function normalizeTeachingStatus(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $clean = strtoupper(trim($value));
        if ($clean === '') {
            return null;
        }
        if (str_contains($clean, 'NON')) {
            return 'Non-Teaching';
        }
        return 'Teaching';
    }

    private function isDuplicate(string $name, ?string $campus, ?int $excludeId = null): bool
    {
        $builder = $this->model->builder();
        $db = \Config\Database::connect();
        $escapedName = $db->escape(strtolower($name));
        $builder->where("LOWER(TRIM(name)) = {$escapedName}", null, false);

        if ($campus === null) {
            $builder->where('campus', null);
        } else {
            $escapedCampus = $db->escape(strtolower($campus));
            $builder->where("LOWER(TRIM(campus)) = {$escapedCampus}", null, false);
        }

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return (bool)$builder->get()->getRowArray();
    }
}
