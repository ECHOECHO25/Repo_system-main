<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UsersController extends ResourceController
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

    public function create()
    {
        try {
            $session = session();
            $role = $session->get('role');
            if ($role !== 'admin') {
                return $this->fail(['status' => 'error', 'message' => 'Forbidden'], 403);
            }

            $data = $this->request->getJSON(true);
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');
            $userRole = trim((string)($data['role'] ?? 'viewer'));
            $status = trim((string)($data['status'] ?? 'active'));

            if ($username === '' || $password === '') {
                return $this->fail(['status' => 'error', 'message' => 'Username and password are required'], 400);
            }

            if (!in_array($userRole, ['admin', 'editor'], true)) {
                return $this->fail(['status' => 'error', 'message' => 'Invalid role'], 400);
            }

            if (!in_array($status, ['active', 'inactive'], true)) {
                return $this->fail(['status' => 'error', 'message' => 'Invalid status'], 400);
            }

            $model = new UserModel();
            $existing = $model->where('username', $username)->first();
            if ($existing) {
                return $this->fail(['status' => 'error', 'message' => 'Username already exists'], 409);
            }

            $payload = [
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $userRole,
                'status' => $status
            ];

            if (!$model->insert($payload)) {
                return $this->fail(['status' => 'error', 'message' => 'Failed to create user', 'errors' => $model->errors()], 400);
            }

            \App\Libraries\AuditLogger::log('user.create', 'user', null, 'User created', [
                'username' => $username,
                'role' => $userRole,
                'status' => $status
            ]);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'User created'
            ]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
