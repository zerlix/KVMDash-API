<?php

namespace Zerlix\Backend\Controller\Disk;

use Symfony\Component\Process\Process;
use Exception;

class DiskController 
{
    
    public function handle(string $route, string $method): array  
    {
        if ($route === 'disk' && $method === 'GET') {
            return $this->getDiskUsage();
        }
        return ['error' => 'Route not found'];
    }


    private function getDiskUsage(): array 
    {
        $process = new Process(['df', '-h']);
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