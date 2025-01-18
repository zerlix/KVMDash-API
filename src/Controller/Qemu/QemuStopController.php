<?php

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Controller\CommandController;

class QemuStopController extends CommandController
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method, string $domain): array
    {
        // execute the virsh shutdown command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'shutdown', $domain]);
        return $response;
    }
}
