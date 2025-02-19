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
        // if data is null, try to read data from request body
        if (!$data) {
            $rawData = file_get_contents('php://input');
            if ($rawData === false) {
                return ['status' => 'error', 'message' => 'Konnte Request-Body nicht lesen'];
            }
            /** @var array<string, mixed>|null */
            $data = json_decode($rawData, true);
        }

        if (!is_array($data)) {
            return ['status' => 'error', 'message' => 'Keine VM-Konfiguration übermittelt'];
        }

        // validate data 
        $validationResult = $this->validateData($data);
        if ($validationResult !== true) {
            return [
                'status' => 'error',
                'message' => $validationResult
            ];
        }

       
        /** @var array{
         *     name: string,
         *     memory: numeric-string|int,
         *     vcpus: numeric-string|int,
         *     disk_size: numeric-string|int,
         *     iso_image: string,
         *     network_bridge: string,
         *     os_variant: string
         * } $data 
         */
        $data = $data;

        // Cast values to correct types
        $name = $data['name'];
        $memory = (string)intval($data['memory']);
        $vcpus = (string)intval($data['vcpus']);
        $diskSize = (string)intval($data['disk_size']);
        $isoImage = $data['iso_image'];
        $networkBridge = $data['network_bridge'];
        $osVariant = $data['os_variant'];

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
            (string)$name,          
            '--memory',
            (string)$memory,        
            '--vcpus',
            (string)$vcpus,        
            '--disk',
            "path={$diskPath},format=qcow2",
            '--cdrom',
            (string)$isoImage,     
            '--network',
            "bridge={$networkBridge}",
            '--graphics',
            'spice',
            '--noautoconsole',
            '--osinfo',
            "name={$osVariant}"   
        ];

        $vmResult = $this->executeCommand($virtInstallCommand);
        if ($vmResult['status'] === 'error') {
            return $vmResult;
        }

        // wait for creat vm command to finish
        sleep(2);

        // run wsSockets.sh to update WebSocket
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

        /** @var string $output */
        $output = $result['output'] ?? '';
        if ($output === '') {
            throw new \RuntimeException('Keine Ausgabe vom Storage-Pool-Command');
        }

        $xml = simplexml_load_string($output);
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
