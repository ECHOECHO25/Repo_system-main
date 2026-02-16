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
        }

        $session = session();
        if (!$session->get('user_id')) {
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
