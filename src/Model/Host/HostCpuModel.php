<?php

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostCpuModel extends CommandModel
{
    private function getCpuTimes(): array
    {
        $cmd = 'cat /proc/stat | grep "^cpu" | awk \'{print $1, $2, $3, $4, $5, $6, $7, $8}\'';
        $output = $this->executeCommand([$cmd]);
        
        if ($output['status'] === 'success') {
            $lines = explode("\n", trim($output['output']));
            $cpuTimes = array_map(function($line) {
                $parts = explode(' ', $line);
                return [
                    'cpu' => $parts[0],
                    'user' => (int)$parts[1],
                    'nice' => (int)$parts[2],
                    'system' => (int)$parts[3],
                    'idle' => (int)$parts[4],
                    'iowait' => (int)$parts[5],
                    'irq' => (int)$parts[6],
                    'softirq' => (int)$parts[7],
                ];
            }, $lines);
            return $cpuTimes;
        }
        
        return [];
    }

    public function handle(string $route, string $method): array
    {
        $cpuTimes1 = $this->getCpuTimes();
        usleep(100000); // 100ms warten
        $cpuTimes2 = $this->getCpuTimes();

        if (empty($cpuTimes1) || empty($cpuTimes2)) {
            return ['status' => 'error', 'message' => 'Failed to retrieve CPU times'];
        }

        $cpuData = [];
        foreach ($cpuTimes1 as $index => $cpu1) {
            $cpu2 = $cpuTimes2[$index];
            $total1 = array_sum(array_slice($cpu1, 1));
            $total2 = array_sum(array_slice($cpu2, 1));
            $totalDiff = $total2 - $total1;
            $idleDiff = $cpu2['idle'] - $cpu1['idle'];
            $usage = 100 * ($totalDiff - $idleDiff) / $totalDiff;

            $cpuData[] = [
                'cpu' => $cpu1['cpu'],
                'total' => $totalDiff,
                'idle' => $idleDiff,
                'used' => $totalDiff - $idleDiff,
                'usage' => round($usage, 2),
            ];
        }

        return ['status' => 'success', 'data' => $cpuData];
    }
}