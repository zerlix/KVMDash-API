<?php

namespace Zerlix\KvmDash\Api\Controller\Lxc;

use Zerlix\KvmDash\Api\Controller\CommandController;

class LxcController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        return [
            'status' => 'error',
            'message' => 'LxcController: Route not found'
        ];
    }
}