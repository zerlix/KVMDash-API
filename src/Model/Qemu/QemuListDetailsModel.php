<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuListDetailsModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Handle the QEMU list request
     * 
     * @param string $route
     * @param string $method
     * @param string|null $domain
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?string $domain = null): array
    {
        // Prüfe, ob Domain existiert
        if ($domain === null) {
            return ['status' => 'error', 'message' => 'Keine Domain angegeben'];
        }

        // XML Details abrufen
        $response = $this->executeCommand(['virsh', '-c', $this->uri, 'dumpxml', $domain]);
        if ($response['status'] !== 'success') {
            return ['status' => 'error', 'message' => "Domain $domain nicht gefunden"];
        }

        // XML parsen
        $xml = @simplexml_load_string($response['output']);
        if ($xml === false) {
            return ['status' => 'error', 'message' => 'Ungültiges XML Format'];
        }

        // Basis-VM-Details
        $vmDetails = [
            'name' => (string)$xml->name,
            'memory' => (string)$xml->memory,
            'vcpu' => (string)$xml->vcpu,
            'os' => [
                'type' => (string)$xml->os->type,
                'arch' => (string)$xml->os->type['arch']
            ],
            'spice' => [
                'port' => (string)$xml->devices->graphics['port'],
                'type' => (string)$xml->devices->graphics['type'],
                'listen' => (string)$xml->devices->graphics['listen']
            ],
            'network' => []
        ];

        // Netzwerkdaten über QEMU Guest Agent abrufen
        $json = shell_exec("virsh -c qemu:///system qemu-agent-command $domain '{\"execute\":\"guest-network-get-interfaces\"}' | jq .");
        $data = json_decode($json, true);

        if ($data && isset($data['return']) && is_array($data['return'])) {
            foreach ($data['return'] as $interface) {
                $interfaceData = [
                    'name' => $interface['name'] ?? 'unknown',
                    'hardware_address' => $interface['hardware-address'] ?? 'unknown',
                    'ip_addresses' => []
                ];

                // Prüfe, ob IP-Adressen existieren
                if (isset($interface['ip-addresses']) && is_array($interface['ip-addresses'])) {
                    foreach ($interface['ip-addresses'] as $ip) {
                        if (isset($ip['ip-address']) && isset($ip['ip-address-type'])) {
                            $interfaceData['ip_addresses'][] = [
                                'type' => $ip['ip-address-type'],
                                'address' => $ip['ip-address']
                            ];
                        }
                    }
                }

                $vmDetails['network'][] = $interfaceData;
            }
        }

        return ['status' => 'success', 'data' => $vmDetails];
    }
}
