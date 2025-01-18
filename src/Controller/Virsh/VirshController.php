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

            if ($response['status'] === 'success') {
                $response['output'] = $this->parseVirshDomstatsOutput($response['output']);
            }
            return $response;
        }

        // api/virsh/start/{name_or_uuid}
        if (preg_match('/^(start|shutdown)\/(.+)$/', $route, $matches)) {
            $action = $matches[1];
            $domainIdentifier = $matches[2];
            return $this->handleDomainAction($action, $domainIdentifier);
        }

        // return an error if the route is not found
        return [
            'status' => 'error',
            'message' => 'Route not found'
        ];
    }


    // parse the output of the virsh domstats action start/stop
    private function handleDomainAction(string $action, string $domainIdentifier): array
    {
        $domains = $this->executeCommand(['virsh', '-c', $this->uri, 'list', '--all', '--name', '--uuid']);
        if ($domains['status'] === 'success') {
            $lines = explode("\n", $domains['output']);
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) == 2) {
                    $uuid = $parts[0];
                    $name = $parts[1];
                    if (strcasecmp($name, $domainIdentifier) == 0 || $uuid === $domainIdentifier) {
                        return $this->executeCommand(['virsh', '-c', $this->uri, $action, $name]);
                    }
                }
            }
            return ['status' => 'error', 'message' => "Domain '$domainIdentifier' not found"];
        } else {
            return ['status' => 'error', 'message' => 'Unable to list domains'];
        }
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
