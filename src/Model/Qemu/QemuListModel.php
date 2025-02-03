<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuListModel extends CommandModel
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method): array
    {
        $formattedOutput = [];

        // execute the virsh domstats command and return the formated output
        $response =  $this->executeCommand(['virsh', '-c', $this->uri, 'domstats']);
        if ($response['status'] === 'success') {
            $formattedOutput = $this->formatOutput($response['output']);
        }
        return ['status' => 'success', 'data' => $formattedOutput];

    }

    // parse the output of the virsh domstats command
    private function formatOutput(string $output): array
    {
        $result = [];
        $currentDomain = null;

        // split the output into lines
        $lines = explode("\n", trim($output));

        // iterate over the lines and parse the output         
        foreach ($lines as $line) {
            if (preg_match('/^Domain:\s+\'([^\']+)\'/', $line, $matches)) {
                $currentDomain = $matches[1];
                $result[$currentDomain] = [];
            } elseif ($currentDomain && preg_match('/^\s*(\S+)\s*=\s*(\S+)/', $line, $matches)) {
                $result[$currentDomain][$matches[1]] = $matches[2];
            }
        }

        // return the result
        return $result;
    }
}
