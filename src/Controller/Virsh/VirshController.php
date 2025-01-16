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
            $response =  $this->executeCommand(['virsh', '-c', $this->uri, 'list', '--all']);
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

        return ['error' => 'Route not found'];
    }
}