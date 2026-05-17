<?php

namespace App\Filters;

use App\Libraries\PermissionManager;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = session('user_role');

        // Admin always passes
        if ($role === 'admin') return;

        // Only apply to assistant_admin
        if ($role !== 'assistant_admin') return;

        $uri = '/' . ltrim($request->getUri()->getPath(), '/');
        // Strip base URL prefix if needed
        $baseUrl = rtrim(config('App')->baseURL, '/');
        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        // Check if this URI requires a permission
        foreach (PermissionManager::$definitions as $code => $def) {
            foreach ($def['routes'] as $route) {
                if (str_starts_with($uri, $route)) {
                    if (! PermissionManager::can($code)) {
                        return redirect()->to('/dashboard')->with('error', 'Access Denied. You do not have permission to access this module.');
                    }
                    return;
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
