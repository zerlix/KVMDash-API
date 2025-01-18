<?php

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Controller\CommandController;

class QemuListController extends CommandController
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method): array
    {
        // execute the virsh domstats command and return the formated output
        $response =  $this->executeCommand(['virsh', '-c', $this->uri, 'domstats']);
        if ($response['status'] === 'success') {
            $response['output'] = $this->parseVirshDomstatsOutput($response['output']);
        }
        return $response;


        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }

    // parse the output of the virsh domstats command
    private function parseVirshDomstatsOutput(string $output): array
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
