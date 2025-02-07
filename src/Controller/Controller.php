<?php

namespace Zerlix\KvmDash\Api\Controller;

use Zerlix\KvmDash\Api\Controller\Host\HostController;
use Zerlix\KvmDash\Api\Controller\Qemu\QemuController;
use Zerlix\KvmDash\Api\Controller\AuthController;

class Controller
{
    private HostController $hostController;
    private QemuController $qemuController;
    private AuthController $authController;

    public function __construct()
    {
        $this->qemuController = new QemuController();
        $this->hostController = new HostController();
        $this->authController = new AuthController();
    }


    /**
     * Handle the API requests
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        // remove the /api/ prefix from the route
        $route = str_replace('/api/', '', $route);


        // validate method
        if (!in_array($method, ['GET', 'POST', 'PUT', 'OPTIONS', 'DELETE'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid HTTP method'
            ];
        }

        // Handle login no auth required
        if ($route === 'login' && $method === 'POST') {
            return $this->authController->login();
        }


        // read the Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        
        // check if the token is valid        
        if (!$token || !$this->authController->verifyToken($token)) {
            http_response_code(401);
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        
        /* 
            handle the host routes
        */
        if ($route === 'logout' && $method === 'POST') {
            $this->authController->logout($token);
            return ['status' => 'success', 'message' => 'Token deleted'];
        }


        if (str_starts_with($route, 'host')) {
            return $this->hostController->handle($route, $method);
        }

        if (str_starts_with($route, 'qemu')) {
            return $this->qemuController->handle($route, $method);
        }



        // return an error if the route is not found
        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'Controller: Route not found'
        ];
    }
}
