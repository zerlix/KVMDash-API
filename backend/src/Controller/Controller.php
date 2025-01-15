<?php

namespace Zerlix\Backend\Controller;

use Zerlix\Backend\Controller\Disk\DiskController;
use Zerlix\Backend\Controller\System\SystemController;

class Controller 
{
    private $diskController;
    private $systemController;

    public function __construct()
    {
        $this->diskController = new DiskController();
        $this->systemController = new SystemController();
    }

    public function handle(string $route, string $method): array 
    {
        // remove the /api/ prefix from the route
        $route = str_replace('/api/', '', $route);
        
        // handle the disk routes
        if (str_starts_with($route, 'disk')) {
            return $this->diskController->handle($route, $method);
        }
        
        // handle the system routes
        if (str_starts_with($route, 'system')) {
            return $this->systemController->handle($route, $method);
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }
}
