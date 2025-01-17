<?php

namespace Zerlix\KvmDash\Api\Controller\Disk;

use Zerlix\KvmDash\Api\Controller\CommandController;

class DiskController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        // handle the disk routes
        if ($route === 'disk' && $method === 'GET') {
            $response = $this->executeCommand(['df', '-h', '-x', 'devtmpfs', '-x', 'tmpfs']);
            //var_dump($response);
            if ($response['status'] === 'success') {
                $response['output'] = $this->parseDfOutput($response['output']);
            }
            return $response;
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }


    private function parseDfOutput(string $output): array
    {
        $lines = explode("\n", trim($output));
        $headers = preg_split('/\s+/', array_shift($lines));
        $result = [];
    
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            // Split the line into values, but keep the last column (mount point) intact
            $values = preg_split('/\s+/', $line, count($headers) - 1);
            if (count($values) === count($headers) - 1) {
                // Add the mount point as the last value
                $values[] = substr($line, strrpos($line, ' ') + 1);
                $result[] = array_combine($headers, $values);
            } 
        }
    
        return $result;
    }
}
