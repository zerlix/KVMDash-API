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
        // Route bereinigen
        $route = str_replace('/api/', '', $route);
        
        // An spezifische Controller weiterleiten
        if (str_starts_with($route, 'disk')) {
            return $this->diskController->handle($route, $method);
        }
        
        if (str_starts_with($route, 'system')) {
            return $this->systemController->handle($route, $method);
        }

        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }
}
