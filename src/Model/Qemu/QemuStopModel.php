<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuStopModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Handle the QEMU stop command
     * 
     * @param string $route
     * @param string $method
     * @param string|null $domain
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?string $domain = null): array
    {
        if ($domain === null) {
            return ['status' => 'error', 'message' => 'Domain nicht angegeben'];
        }

        // execute the virsh stop command and return the output
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'shutdown', $domain]);
        
        if ($response['status'] === 'success') {
            $output = is_string($response['output']) ? trim($response['output']) : '';
            return [
                'status' => 'success',
                'data' => $output
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Fehler beim Stoppen der Domain',
                'error' => $response
            ];
        }

        return [
            'status' => 'error',
            'message' => $response['message'] ?? 'Unbekannter Fehler'
        ];
    }
}