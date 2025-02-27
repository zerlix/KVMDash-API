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

        // Systeminformationen aus domstats abrufen
        $statsCommand = [
            'virsh',
            '-c',
            $this->uri,
            'domstats',
            $domain
        ];
        

        $statsResponse = $this->executeCommand($statsCommand);
        if ($statsResponse['status'] === 'success' && is_string($statsResponse['output'])) {
            $vmDetails['stats'] = $this->parseStats($statsResponse['output']);
        }


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

    /**
     * Parst die domstats Ausgabe
     *
     * @param string $output
     * @return array<string, mixed>
     */
    private function parseStats(string $output): array
    {
        $stats = [
            'cpu' => [
                'total_time' => 0,
                'user_time' => 0,
                'system_time' => 0
            ],
            'memory' => [
                'current' => 0,
                'available' => 0,
                'unused' => 0,
                'rss' => 0
            ],
            'disk' => [],
            'network' => []
        ];

        $lines = explode("\n", trim($output));
        $currentDisk = null;
        $currentNet = null;

        foreach ($lines as $line) {
            if (preg_match('/^\s*(\S+)\s*=\s*(\S+)/', $line, $matches)) {
                [$_, $key, $value] = $matches;

                // CPU Statistiken
                if (strpos($key, 'cpu.time') === 0) {
                    $stats['cpu']['total_time'] = (int)$value;
                } elseif (strpos($key, 'cpu.user') === 0) {
                    $stats['cpu']['user_time'] = (int)$value;
                } elseif (strpos($key, 'cpu.system') === 0) {
                    $stats['cpu']['system_time'] = (int)$value;
                }

                // Speicher Statistiken
                elseif ($key === 'balloon.current') {
                    $stats['memory']['current'] = (int)$value;
                } elseif ($key === 'balloon.available') {
                    $stats['memory']['available'] = (int)$value;
                } elseif ($key === 'balloon.unused') {
                    $stats['memory']['unused'] = (int)$value;
                } elseif ($key === 'balloon.rss') {
                    $stats['memory']['rss'] = (int)$value;
                }

                // Block Device Statistiken
                elseif (strpos($key, 'block.') === 0) {
                    if (preg_match('/block\.(\d+)\.name/', $key, $m)) {
                        $currentDisk = $value;
                        $stats['disk'][$currentDisk] = [
                            'reads' => 0,
                            'writes' => 0,
                            'capacity' => 0,
                            'allocation' => 0
                        ];
                    } elseif ($currentDisk && strpos($key, 'rd.bytes') !== false) {
                        $stats['disk'][$currentDisk]['reads'] = (int)$value;
                    } elseif ($currentDisk && strpos($key, 'wr.bytes') !== false) {
                        $stats['disk'][$currentDisk]['writes'] = (int)$value;
                    } elseif ($currentDisk && strpos($key, 'capacity') !== false) {
                        $stats['disk'][$currentDisk]['capacity'] = (int)$value;
                    } elseif ($currentDisk && strpos($key, 'allocation') !== false) {
                        $stats['disk'][$currentDisk]['allocation'] = (int)$value;
                    }
                }

                // Netzwerk Statistiken
                elseif (strpos($key, 'net.') === 0) {
                    if (preg_match('/net\.(\d+)\.name/', $key, $m)) {
                        $currentNet = $value;
                        $stats['network'][$currentNet] = [
                            'rx_bytes' => 0,
                            'tx_bytes' => 0,
                            'rx_packets' => 0,
                            'tx_packets' => 0
                        ];
                    } elseif ($currentNet && strpos($key, 'rx.bytes') !== false) {
                        $stats['network'][$currentNet]['rx_bytes'] = (int)$value;
                    } elseif ($currentNet && strpos($key, 'tx.bytes') !== false) {
                        $stats['network'][$currentNet]['tx_bytes'] = (int)$value;
                    } elseif ($currentNet && strpos($key, 'rx.pkts') !== false) {
                        $stats['network'][$currentNet]['rx_packets'] = (int)$value;
                    } elseif ($currentNet && strpos($key, 'tx.pkts') !== false) {
                        $stats['network'][$currentNet]['tx_packets'] = (int)$value;
                    }
                }
            }
        }

        return $stats;
    }
}
