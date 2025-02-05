<?php

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostDiskModel extends CommandModel
{
    /**
     * Handle the disk routes
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        if ($route === 'disk' && $method === 'GET') {
            $response = $this->executeCommand(['df', '-h', '-x', 'devtmpfs', '-x', 'tmpfs']);
            if ($response['status'] === 'success') {
                $formattedOutput = $this->formatOutput($response['output']);
                return ['status' => 'success', 'data' => $formattedOutput];
            }
            return $response;
        }

        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }

    /**
     * Parse the output of the df command
     * 
     * @param string $output
     * @return array<int, array<string, string>>
     */
    private function formatOutput(string $output): array
    {
        $lines = explode("\n", trim($output));
        $headers = preg_split('/\s+/', array_shift($lines));
        if ($headers === false) {
            throw new \RuntimeException('Failed to parse headers');
        }
        $result = [];
    
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            // Split the line into values, but keep the last column (mount point) intact
            $values = preg_split('/\s+/', $line, count($headers) - 1);
            if ($values === false) {
                throw new \RuntimeException('Failed to parse line: ' . $line);
            }
            if (count($values) === count($headers) - 1) {
                
                $values[] = substr($line, strrpos($line, ' ') + 1);
                
                $combined = array_combine($headers, $values);
                if ($combined === false) {
                    throw new \RuntimeException('Failed to combine headers and values');
                }
                
                $result[] = $combined;} 
        }
    
        return $result;
    }
}