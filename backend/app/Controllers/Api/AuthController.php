<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Libraries\AuditLogger;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
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

    public function login()
    {
        try {
            $data = $this->request->getJSON(true);
            $username = trim((string)($data['username'] ?? ''));
            $password = (string)($data['password'] ?? '');

            if ($username === '' || $password === '') {
                return $this->fail(['status' => 'error', 'message' => 'Username and password are required'], 400);
            }

            $model = new UserModel();
            $user = $model->where('username', $username)->where('status', 'active')->first();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return $this->fail(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }

            $session = session();
            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]);

            AuditLogger::log('login', 'user', (int)$user['id'], 'User logged in');

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->fail(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function me()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return $this->fail(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $this->respond([
            'status' => 'success',
            'data' => [
                'id' => $session->get('user_id'),
                'username' => $session->get('username'),
                'role' => $session->get('role')
            ]
        ]);
    }

    public function logout()
    {
        $session = session();
        $userId = $session->get('user_id');
        $session->destroy();

        if ($userId) {
            AuditLogger::log('logout', 'user', (int)$userId, 'User logged out');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Logged out'
        ]);
    }
}
