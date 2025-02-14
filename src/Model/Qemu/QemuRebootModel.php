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
            $output = is_string($response['output']) ? trim($response['output']) : '';
            return [
                'status' => 'success',
                'data' => $output
            ];
        }

        else {
            return [
                'status' => 'error',
                'message' => 'Fehler beim Neustarten der Domain',
                'error' => $response
            ];
        }

    }
}
