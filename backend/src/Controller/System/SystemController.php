<?php

namespace Zerlix\Backend\Controller\System;

use Zerlix\Backend\Controller\CommandController;

class SystemController extends CommandController
{
    public function handle(string $route, string $method): array 
    {
        $route = str_replace('system/', '', $route);
        
        
        if ($route === 'uptime' && $method === 'GET') {
            return $this->executeCommand(['uptime']);
        }
        
        if ($route === 'load' && $method === 'GET') {
            return $this->executeCommand(['cat', '/proc/loadavg']);
        }
        
        return ['error' => 'Route not found'];
    }
}