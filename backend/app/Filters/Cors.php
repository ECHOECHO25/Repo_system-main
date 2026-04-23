<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
    private function resolveAllowedOrigin(RequestInterface $request): string
    {
        $requestOrigin = trim((string)$request->getHeaderLine('Origin'));
        $configured = trim((string)(getenv('FRONTEND_ORIGIN') ?: 'http://localhost:5173'));

        // Wildcard is invalid when credentials are enabled.
        if ($configured === '' || $configured === '*') {
            $configured = 'http://localhost:5173';
        }

        if ($requestOrigin === '') {
            return $configured;
        }

        if ($requestOrigin === $configured || preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#i', $requestOrigin)) {
            return $requestOrigin;
        }

        return $configured;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $this->resolveAllowedOrigin($request);
        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Vary: Origin");

        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header("HTTP/1.1 200 OK");
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $this->resolveAllowedOrigin($request);
        $response->setHeader('Access-Control-Allow-Origin', $origin);
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Vary', 'Origin');
        return $response;
    }
}
