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
        $xmlOutput = $response['output'] ?? '';
        if (!is_string($xmlOutput)) {
            return ['status' => 'error', 'message' => 'Ungültiges XML Format'];
        }

        $xml = @simplexml_load_string($xmlOutput);
        if ($xml === false) {
            return ['status' => 'error', 'message' => 'Ungültiges XML Format'];
        }

        // Basis-VM-Details extrahieren
        $vmDetails = [
            'name'    => (string)$xml->name,
            'memory'  => (string)$xml->memory,
            'vcpu'    => (string)$xml->vcpu,
            'os'      => [
                'type' => (string)$xml->os->type,
                'arch' => (string)$xml->os->type['arch']
            ],
            'spice'   => [
                'port'   => (string)$xml->devices->graphics['port'],
                'type'   => (string)$xml->devices->graphics['type'],
                'listen' => (string)$xml->devices->graphics['listen']
            ],
            'network' => []
        ];

        // Agent-Details abrufen
        $env = ['LANG' => 'C'];
        $agentCommand = [
            'virsh',
            '--connect',
            $this->uri,
            'qemu-agent-command',
            $domain,
            '{"execute":"guest-network-get-interfaces"}'
        ];
        
        $agentResponse = $this->executeCommand($agentCommand, $env);
        error_log("Agent response: " . print_r($agentResponse, true));

        // Wenn der Befehl erfolgreich war, Output decodieren
        $agentOutput = $agentResponse['output'] ?? '';
        if ($agentResponse['status'] === 'success' && is_string($agentOutput)) {
            $data = json_decode($agentOutput, true);
            if (!is_array($data)) {
                error_log("Failed to decode agent output: " . $agentOutput);
                $data = ['return' => []];
            }
        } else {
            error_log("Agent command failed: " . ($agentResponse['error'] ?? 'unknown error'));
            $data = ['return' => []];
        }
        
        // Iteriere über die Rückgabe und füge die Netzwerkschnittstellen (außer Loopback) hinzu
        if (isset($data['return']) && is_array($data['return'])) {
            foreach ($data['return'] as $interface) {

                if (!is_array($interface) || !isset($interface['name'])) {
                    continue;
                }
        
                // Loopback (lo) überspringen
                if ($interface['name'] === 'lo') {
                    continue;
                }
        
                $interfaceData = [
                    'name'             => $interface['name'],
                    'hardware_address' => $interface['hardware-address'] ?? 'unknown',
                    'ip_addresses'     => []
                ];
        
                if (isset($interface['ip-addresses']) && is_array($interface['ip-addresses'])) {
                    foreach ($interface['ip-addresses'] as $ip) {
                        if (!is_array($ip) || !isset($ip['ip-address'], $ip['ip-address-type'])) {
                            continue;
                        }
        
                        $interfaceData['ip_addresses'][] = [
                            'type'    => $ip['ip-address-type'],
                            'address' => $ip['ip-address']
                        ];
                    }
                }
        
                $vmDetails['network'][] = $interfaceData;
            }
        }
        
        return ['status' => 'success', 'data' => $vmDetails];
    }
}
