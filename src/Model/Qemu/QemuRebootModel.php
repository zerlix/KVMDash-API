<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuRebootModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Handle the QEMU start command
     * 
     * @param string $route
     * @param string $method
     * @param string|null $domain
     * @return array<string, mixed>
     */

    public function handle(string $route, string $method, ?string $domain = null): array
    {
        if (!$domain) {
            return ['status' => 'error', 'message' => 'Domain nicht angegeben'];
        }

        // execute the virsh start command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'reboot', $domain]);
        
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
