<?php

namespace Zerlix\KvmDash\Api\Model\Host;
use Zerlix\KvmDash\Api\Model\CommandModel;

class HostMemModel extends CommandModel
{
    public function handle(string $route, string $method): array
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

}