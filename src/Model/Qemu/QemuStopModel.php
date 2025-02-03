<?php

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuStopModel extends CommandModel
{
    private $uri = 'qemu:///system';

    public function handle(string $route, string $method, string $domain): array
    {
        if (!$domain) {
            return ['status' => 'error', 'message' => 'Domain nicht angegeben'];
        }
        // execute the virsh shutdown command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'shutdown', $domain]);
        
        if ($response['status'] === 'success') {
            return [
                'status' => 'success',
                'data' => trim($response['output'])
            ];
        }

        return [
            'status' => 'error',
            'message' => $response['message'] ?? 'Unbekannter Fehler'
        ];
    }
}
