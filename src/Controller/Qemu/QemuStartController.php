<?php

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Controller\CommandController;

class QemuStartController extends CommandController
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method, string $domain): array
    {
        // execute the virsh start command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'start', $domain]);
        return $response;
    }
}
