<?php

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostCpuModel extends CommandModel
{
    public function handle(string $route, string $method): array
    {
        $cmd = [
            'cat /proc/stat | grep "^cpu" | awk \'{
                total=$2+$3+$4+$5+$6+$7+$8;
                idle=$5;
                used=total-idle;
                printf("{\\"cpu\\":\\"%s\\",\\"total\\":%d,\\"idle\\":%d,\\"used\\":%d}\n", 
                    $1, total, idle, used)
            }\''
        ];
    
        $output = $this->executeCommand($cmd);
        
        if ($output['status'] === 'success') {
            // JSON-Strings in Array konvertieren
            $lines = explode("\n", trim($output['output']));
            $cpus = array_map(function($line) {
                return json_decode($line, true);
            }, $lines);

            // Prozentuale Auslastung berechnen
            $cpuData = array_map(function($cpu) {
                $cpu['usage'] = round(($cpu['used'] / $cpu['total']) * 100, 2);
                return $cpu;
            }, $cpus);

            return ['status' => 'success', 'data' => $cpuData];
        }
        
        return ['status' => 'error', 'message' => $output['message']];
    }
}

