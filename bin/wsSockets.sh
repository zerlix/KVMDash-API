<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuCreateVmModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Handle the VM creation
     * 
     * @param string $route
     * @param string $method
     * @param array<string, mixed>|null $data VM configuration data
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?array $data = null): array
    {

        /** Testdaten
        $data = [
            'name' => 'test-vm-' . time(), // Unique Name mit Timestamp
            'memory' => 2048,              // 2GB RAM
            'vcpus' => 2,                  // 2 CPUs
            'disk_size' => 20,             // 20GB Festplatte
            'iso_image' => '/mnt/raid/CDImages/debian-12.9.0-amd64-netinst.iso', // Pfad zu Ihrer ISO
            'network_bridge' => 'br0',  // Standard libvirt Network Bridge
            'os_variant' => 'linux2022'  // OS-Typ
        ]; */
     
        // Wenn $data null ist, versuche die Daten aus dem Request-Body zu lesen
        if (!$data) {
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            error_log('Parsed JSON data: ' . print_r($data, true));
        }

        if (!$data) {
            return ['status' => 'error', 'message' => 'Keine VM-Konfiguration übermittelt'];
        }

        // Validierung der erforderlichen Felder
        $requiredFields = ['name', 'memory', 'vcpus', 'disk_size', 'iso_image', 'network_bridge', 'os_variant'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return [
                    'status' => 'error',
                    'message' => "Fehlende Pflichtangabe: $field",
                    'received_data' => $data
                ];
            }
        }
        // typecast values to correct types
        if (
            !is_string($data['name']) ||
            !is_numeric($data['memory']) ||
            !is_numeric($data['vcpus']) ||
            !is_numeric($data['disk_size']) ||
            !is_string($data['iso_image']) ||
            !is_string($data['network_bridge'])
        ) {
            return ['status' => 'error', 'message' => 'Ungültige Datentypen'];
        }

        // Cast values to correct types
        $name = $data['name'];
        $memory = (string)intval($data['memory']);
        $vcpus = (string)intval($data['vcpus']);
        $diskSize = (string)intval($data['disk_size']);
        $isoImage = $data['iso_image'];
        $networkBridge = $data['network_bridge'];
        $osVariant = (string)$data['os_variant'];

        // get Storage-Pool-Path
        try {
            $storagePath = $this->getStoragePoolPath();
            $diskPath = "{$storagePath}/{$name}.qcow2";
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Create Disk-Image
        $createDiskCommand = [
            'qemu-img',
            'create',
            '-f',
            'qcow2',
            $diskPath,
            "{$diskSize}G"
        ];

        $diskResult = $this->executeCommand($createDiskCommand);
        if ($diskResult['status'] === 'error') {
            return $diskResult;
        }

        // Create VM mit virt-install
        $virtInstallCommand = [
            'virt-install',
            '--connect',
            $this->uri,
            '--name',
            $name,
            '--memory',
            $memory,
            '--vcpus',
            $vcpus,
            '--disk',
            "path={$diskPath},format=qcow2",
            '--cdrom',
            $isoImage,
            '--network',
            "bridge={$networkBridge}",
            '--graphics',
            'spice',
            '--noautoconsole',
            '--osinfo',
            "name={$osVariant}"  // Dynamischer OS-Typ
        ];

        //return $this->executeCommand($virtInstallCommand);
        $vmResult = $this->executeCommand($virtInstallCommand);
        if ($vmResult['status'] === 'error') {
            return $vmResult;
        }
    
        // Warte kurz bis die VM erstellt ist
        sleep(2);
    
        // Führe wsSockets.sh aus um WebSocket zu aktualisieren
        $scriptPath = __DIR__ . '/../../../bin/wsSockets.sh';
        $wsResult = $this->executeCommand(['bash', $scriptPath]);
    
        return [
            'status' => 'success',
            'message' => 'VM erfolgreich erstellt',
            'vm_result' => $vmResult,
            'websocket_status' => $wsResult['status']
        ];
    }

    /**
     * Validate VM configuration data
     * 
     * @param array<string, mixed> $data
     * @return true|string True if valid, error message otherwise
     */
    private function validateData(array $data): bool|string
    {
        $requiredFields = ['name', 'memory', 'vcpus', 'disk_size', 'iso_image', 'network_bridge'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return "Fehlendes Feld: {$field}";
            }
        }

        // Validiere Typen
        if (!is_string($data['name'])) {
            return 'Name muss ein String sein';
        }
        if (!is_numeric($data['memory'])) {
            return 'Memory muss numerisch sein';
        }
        if (!is_numeric($data['vcpus'])) {
            return 'VCPUs muss numerisch sein';
        }
        if (!is_numeric($data['disk_size'])) {
            return 'Disk Size muss numerisch sein';
        }
        if (!is_string($data['iso_image'])) {
            return 'ISO Image muss ein String sein';
        }
        if (!is_string($data['network_bridge'])) {
            return 'Network Bridge muss ein String sein';
        }

        return true;
    }


    private function getStoragePoolPath(): string
    {
        $command = ['virsh', '--connect', $this->uri, 'pool-dumpxml', 'default'];
        $result = $this->executeCommand($command);

        if ($result['status'] === 'error') {
            throw new \RuntimeException('Konnte Storage-Pool-Pfad nicht auslesen');
        }

        $xml = simplexml_load_string($result['output']);
        if (!$xml) {
            throw new \RuntimeException('Ungültiges Storage-Pool XML');
        }

        $path = (string)$xml->target->path;
        if (empty($path)) {
            throw new \RuntimeException('Storage-Pool-Pfad nicht gefunden');
        }

        return $path;
    }
}
