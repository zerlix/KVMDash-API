<?php

namespace Zerlix\KvmDash\Api\Controller\System;

use Zerlix\KvmDash\Api\Controller\CommandController;

class SystemController extends CommandController
{
    public function handle(string $route, string $method): array
    {
        // remove the /system/ prefix from the route 
        $route = str_replace('system/', '', $route);

        // api/system/uptime
        if ($route === 'uptime' && $method === 'GET') {
            return $this->handleUptime();
        }

        // api/system/memory
        if ($route === 'memory' && $method === 'GET') {
            return $this->handleMemory();
        }

        // api/system/cpu
        if ($route === 'cpu' && $method === 'GET') {
            return $this->handleCpu();
        }

        // api/system/cpudetails
        if ($route === 'cpudetails' && $method === 'GET') {
            return $this->handleCpuDetails();
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }

    private function handleUptime(): array
    {
        $output = $this->executeCommand(['uptime']);
        $outputString = implode("\n", array_map('trim', $output));

        // format the output
        if (preg_match('/up\s+(.*?),\s+\d+\s+user.*load\s+average:\s+([\d.]+),\s+([\d.]+),\s+([\d.]+)/', $outputString, $matches)) {
            $formattedOutput = [
                'uptime' => $matches[1],
                'load_average' => [
                    '1min' => $matches[2],
                    '5min' => $matches[3],
                    '15min' => $matches[4]
                ]
            ];
            return ['status' => 'success', 'data' => $formattedOutput];
        } else {
            return ['status' => 'error', 'message' => 'Unable to parse uptime output'];
        }
    }

    private function handleMemory(): array
    {
        $output = $this->executeCommand(['free', '-h', '-t', '-w']);
        $outputString = implode("\n", array_map('trim', $output));

        // format the output
        if (preg_match('/Mem:\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $outputString, $matches)) {
            $formattedOutput = [
                'total' => $matches[1],
                'used' => $matches[2],
                'free' => $matches[3],
                'shared' => $matches[4],
                'buff_cache' => $matches[5],
                'available' => $matches[6]
            ];
            return ['status' => 'success', 'data' => $formattedOutput];
        } else {
            return ['status' => 'error', 'message' => 'Unable to parse memory output'];
        }
    }

    private function handleCpu(): array
    {
        $output = $this->executeCommand(['lscpu']);
        $outputString = implode("\n", array_map('trim', $output));

        // format the output
        if (preg_match('/Model name:\s+(.*)/', $outputString, $matches)) {
            $formattedOutput = [
                'model' => $matches[1]
            ];
            return ['status' => 'success', 'data' => $formattedOutput];
        } else {
            return ['status' => 'error', 'message' => 'Unable to parse cpu output'];
        }
    }

    private function handleCpuDetails(): array
    {
        $output = $this->executeCommand(['lscpu']);
        $outputString = implode("\n", array_map('trim', $output));

        // format the output
        $formattedOutput = [];
        if (preg_match_all('/(.+):\s+(.*)/', $outputString, $matches)) {
            foreach ($matches[1] as $index => $key) {
                $formattedOutput[$key] = $matches[2][$index];
            }
            return ['status' => 'success', 'data' => $formattedOutput];
        } else {
            return ['status' => 'error', 'message' => 'Unable to parse cpu details output'];
        }
    }
}
