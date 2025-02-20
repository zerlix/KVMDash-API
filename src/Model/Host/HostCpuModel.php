<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostCpuModel extends CommandModel
{
    /**
     * Get CPU times from /proc/stat
     * 
     * @return array<int, array<string, int|string>>
     */
    private function getCpuTimes(): array
    {
        $statContent = @file_get_contents('/proc/stat');
        if ($statContent === false) {
            error_log('Failed to read /proc/stat');
            return [];
        }

        $lines = explode("\n", $statContent);
        $cpuTimes = [];

        foreach ($lines as $line) {
            if (preg_match('/^cpu/', $line)) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 8) {
                    $cpuTimes[] = [
                        'cpu' => $parts[0],
                        'user' => (int)$parts[1],
                        'nice' => (int)$parts[2],
                        'system' => (int)$parts[3],
                        'idle' => (int)$parts[4],
                        'iowait' => (int)$parts[5],
                        'irq' => (int)$parts[6],
                        'softirq' => (int)$parts[7],
                    ];
                }
            }
        }

        return $cpuTimes;
    }

    /**
     * Handle the CPU statistics request
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
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
            $total1 = (int)array_sum(array_slice($cpu1, 1));
            $total2 = (int)array_sum(array_slice($cpu2, 1));
            $totalDiff = $total2 - $total1;
            $idleDiff = (int)$cpu2['idle'] - (int)$cpu1['idle'];
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
