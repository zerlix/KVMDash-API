<?php

namespace Zerlix\Backend\Controller\System;

use Symfony\Component\Process\Process;
use Exception;

class SystemController 
{
    public function handle(string $route, string $method): array 
    {
        $route = str_replace('system/', '', $route);
        
        if ($route === 'uptime' && $method === 'GET') {
            return $this->getUptime();
        } elseif ($route === 'load' && $method === 'GET') {
            return $this->getLoad();
        }
        
        return ['error' => 'Route not found'];
    }



    public function getUptime(): array 
    {
        $process = new Process(['uptime']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }

        return [
            'status' => 'success',
            'data' => trim($process->getOutput())
        ];
    }


    public function getLoad(): array 
    {
        $process = new Process(['cat', '/proc/loadavg']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }

        return [
            'status' => 'success',
            'data' => trim($process->getOutput())
        ];
    }
}