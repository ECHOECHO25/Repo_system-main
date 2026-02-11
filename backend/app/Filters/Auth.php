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
        if ($path === 'api/auth' || str_starts_with($path, 'api/auth/')) {
            return;
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
