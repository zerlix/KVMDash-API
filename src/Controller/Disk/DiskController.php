<?php

namespace Zerlix\KvmDash\Api\Controller\Disk;

use Zerlix\KvmDash\Api\Controller\CommandController;

class DiskController extends CommandController
{
    public function handle(string $route, string $method): array 
    {
        // handle the disk routes
        if ($route === 'disk' && $method === 'GET') {
            return $this->executeCommand(['df', '-h']);
        }
        
        // return an error if the route is not found
        return ['error' => 'Route not found'];
    }
}