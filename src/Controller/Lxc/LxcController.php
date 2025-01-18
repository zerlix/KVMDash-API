<?php

namespace Zerlix\KvmDash\Api\Controller\Lxc;

use Zerlix\KvmDash\Api\Controller\CommandController;
use Zerlix\KvmDash\Api\Controller\Lxc\LxcListController;

class LxcController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        // remove the qemu prefix from the route
         $route = str_replace('lxc/', '', $route);

        if ($route === 'list' && $method === 'GET') {
            $lxcListController = new LxcListController();
            return $lxcListController->handle($route, $method);
        }

        return [
            'status' => 'error',
            'message' => 'LxcController: Route not found'
        ];
    }
}