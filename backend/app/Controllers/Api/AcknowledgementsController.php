<?php

namespace App\Controllers\Api;

use App\Models\AcknowledgementModel;
use App\Models\AcknowledgementItemModel;
use CodeIgniter\RESTful\ResourceController;

class AcknowledgementsController extends ResourceController
{
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
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;
            $search = $this->request->getGet('search');

            $model = new AcknowledgementModel();
            $builder = $model->builder();

            if ($search) {
                $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('position', $search)
                    ->orLike('affiliation', $search)
                    ->orLike('issued_by', $search)
                    ->orLike('received_by', $search)
                    ->groupEnd();
            }

            $total = $builder->countAllResults(false);

            $rows = $builder->orderBy('date_issued', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            $itemModel = new AcknowledgementItemModel();
            foreach ($rows as &$row) {
                $row['items'] = $itemModel->where('acknowledgement_id', $row['id'])->findAll();
            }

            return $this->respond([
                'status' => 'success',
                'data' => $rows,
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

    public function show($id = null)
    {
        try {
            $model = new AcknowledgementModel();
            $row = $model->find($id);
            if (!$row) {
                return $this->failNotFound('Record not found');
            }
            $itemModel = new AcknowledgementItemModel();
            $row['items'] = $itemModel->where('acknowledgement_id', $row['id'])->findAll();

            return $this->respond(['status' => 'success', 'data' => $row]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            $items = $data['items'] ?? [];
            unset($data['items']);

            $model = new AcknowledgementModel();
            if (!$model->insert($data)) {
                return $this->fail(['status' => 'error', 'message' => 'Failed to create record', 'errors' => $model->errors()], 400);
            }

            $ackId = $model->getInsertID();
            $this->saveItems($ackId, $items);

            $row = $model->find($ackId);
            $row['items'] = (new AcknowledgementItemModel())->where('acknowledgement_id', $ackId)->findAll();

            return $this->respondCreated(['status' => 'success', 'data' => $row]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id = null)
    {
        try {
            $model = new AcknowledgementModel();
            if (!$model->find($id)) {
                return $this->failNotFound('Record not found');
            }

            $data = $this->request->getJSON(true);
            $items = $data['items'] ?? [];
            unset($data['items']);

            if (!$model->update($id, $data)) {
                return $this->fail(['status' => 'error', 'message' => 'Failed to update record', 'errors' => $model->errors()], 400);
            }

            $itemModel = new AcknowledgementItemModel();
            $itemModel->where('acknowledgement_id', $id)->delete();
            $this->saveItems($id, $items);

            $row = $model->find($id);
            $row['items'] = $itemModel->where('acknowledgement_id', $id)->findAll();

            return $this->respond(['status' => 'success', 'data' => $row]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id = null)
    {
        try {
            $model = new AcknowledgementModel();
            if (!$model->find($id)) {
                return $this->failNotFound('Record not found');
            }

            $model->delete($id);
            return $this->respondDeleted(['status' => 'success', 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function saveItems($ackId, $items)
    {
        if (!is_array($items)) {
            return;
        }
        $itemModel = new AcknowledgementItemModel();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $payload = [
                'acknowledgement_id' => $ackId,
                'volume' => $item['volume'] ?? null,
                'copies' => $item['copies'] ?? null,
                'remark' => $item['remark'] ?? null
            ];
            $itemModel->insert($payload);
        }
    }
}
