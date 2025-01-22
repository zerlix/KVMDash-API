<?php

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuStopModel extends CommandModel
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method, string $domain): array
    {
        // execute the virsh shutdown command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'shutdown', $domain]);
        if ($response['status'] === 'success') {
            // $formattedOutput = $this->formatOutput($response['output']);
            $formattedOutput = $response['output'];
        }
        return ['status' => 'success', 'data' => $formattedOutput];
    }
}
