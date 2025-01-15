<?php

namespace Zerlix\Backend\Controller\Disk;

use Zerlix\Backend\Controller\CommandController;

class DiskController extends CommandController
{
    public function handle(string $route, string $method): array 
    {
        if ($route === 'disk' && $method === 'GET') {
            return $this->executeCommand(['df', '-h']);
        }
        return ['error' => 'Route not found'];
    }
}