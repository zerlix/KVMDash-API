<?php

namespace Zerlix\KvmDash\Api\Controller\Host;

use Zerlix\KvmDash\Api\Controller\CommandController;

class HostController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }
}