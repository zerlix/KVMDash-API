<?php

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuStartModel extends CommandModel
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method, string $domain): array
    {
        // execute the virsh start command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'start', $domain]);
        if ($response['status'] === 'success') {
            // $formattedOutput = $this->formatOutput($response['output']);
            $formattedOutput = $response['output'];
        }
        return ['status' => 'success', 'data' => $formattedOutput];
    }
}
