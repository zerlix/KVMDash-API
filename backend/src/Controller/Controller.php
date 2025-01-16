<?php

namespace Zerlix\Backend\Controller;

use Zerlix\Backend\Controller\Disk\DiskController;
use Zerlix\Backend\Controller\System\SystemController;
use Zerlix\Backend\Controller\Virsh\VirshController;

class Controller
{
    private $diskController;
    private $systemController;
    private $virshController;

    public function __construct()
    {
        $this->diskController = new DiskController();
        $this->systemController = new SystemController();
        $this->virshController = new VirshController();
    }

    public function handle(string $route, string $method): array
    {
        // remove the /api/ prefix from the route
        $route = str_replace('/api/', '', $route);

        
        // validate method
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid HTTP method'
            ];
        }

        // handle the disk routes
        if (str_starts_with($route, 'disk')) {
            return $this->diskController->handle($route, $method);
        }

        // handle the system routes
        if (str_starts_with($route, 'system')) {
            return $this->systemController->handle($route, $method);
        }

        // handle the virsh routes
        if (str_starts_with($route, 'virsh')) {
            return $this->virshController->handle($route, $method);
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }
}
