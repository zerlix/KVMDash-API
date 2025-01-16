<?php

namespace Zerlix\KvmDash\Api\Controller\Virsh;

use Zerlix\KvmDash\Api\Controller\CommandController;

class VirshController extends CommandController
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method): array
    {
        $route = str_replace('virsh/', '', $route);

        // api/virsh/list
        if ($route === 'list' && $method === 'GET') {
            $response =  $this->executeCommand(['virsh', '-c', $this->uri, 'domstats']);
            
            if($response['status'] === 'success') {
                $response['output'] = $this->parseVirshDomstatsOutput($response['output']);
            }
          
            return $response;
        }

        // api/virsh/start/{name}
        if (preg_match('/^start\/(.+)$/', $route, $matches)) {
            return $this->executeCommand(['virsh', '-c', $this->uri, 'start', $matches[1]]);
        }

        // api/virsh/shutdown/{name}
        if (preg_match('/^shutdown\/(.+)$/', $route, $matches)) {
            return $this->executeCommand(['virsh', '-c', $this->uri, 'shutdown', $matches[1]]);
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }

    private function parseVirshDomstatsOutput(string $output): array
    {
        $result = [];
        $currentDomain = null;

        // split the output into lines
        $lines = explode("\n", trim($output));

         
        foreach ($lines as $line) {
            if (preg_match('/^Domain:\s+\'([^\']+)\'/', $line, $matches)) {
                $currentDomain = $matches[1];
                $result[$currentDomain] = [];
            } elseif ($currentDomain && preg_match('/^\s*(\S+)\s*=\s*(\S+)/', $line, $matches)) {
                $result[$currentDomain][$matches[1]] = $matches[2];
            }
        }

        return $result;
    }

}
