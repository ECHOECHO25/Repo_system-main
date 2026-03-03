<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getPath(), '/');
        $method = strtolower($request->getMethod());

        if ($method === 'options') {
            return;
        }
        if ($path === 'api/auth' || str_starts_with($path, 'api/auth/')) {
            return;
        }
        if ($method === 'get') {
            if (str_starts_with($path, 'api/dashboard')) {
                return;
            }
            if (str_starts_with($path, 'api/publications')) {
                return;
            }
            if (str_starts_with($path, 'api/faculty')) {
                return;
            }
            if (str_starts_with($path, 'api/acknowledgements')) {
                return;
            }
        }

        $session = session();
        $userId = $session->get('user_id');
        if ($userId) {
            $now = time();
            $role = (string)($session->get('role') ?? '');
            $idleLimit = $role === 'admin' ? 600 : 1800; // 10 min admin, 30 min others
            $absoluteLimit = 28800; // 8 hours

            $loginTime = (int)($session->get('login_time') ?? 0);
            if ($loginTime && ($now - $loginTime) > $absoluteLimit) {
                $session->destroy();
                return service('response')->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => 'Session expired. Please log in again.'
                ]);
            }

            $lastActivity = (int)($session->get('last_activity') ?? 0);
            if ($lastActivity && ($now - $lastActivity) > $idleLimit) {
                $session->destroy();
                return service('response')->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => 'Session timed out due to inactivity.'
                ]);
            }

            $session->set('last_activity', $now);
        }

        if (!$userId) {
            return service('response')->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
