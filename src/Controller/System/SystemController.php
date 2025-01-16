<?php

namespace Zerlix\KvmDash\Api\Controller\System;

use Zerlix\KvmDash\Api\Controller\CommandController;

class SystemController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        // remove the /system/ prefix from the route 
        $route = str_replace('system/', '', $route);

        // api/system/uptime
        if ($route === 'uptime' && $method === 'GET') {
            return $this->executeCommand(['uptime']);
        }
        // api/system/load
        if ($route === 'load' && $method === 'GET') {
            return $this->executeCommand(['cat', '/proc/loadavg']);
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }
}
