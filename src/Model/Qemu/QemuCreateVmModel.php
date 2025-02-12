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
        if (!$data) {
            return ['status' => 'error', 'message' => 'Keine VM-Konfiguration übermittelt'];
        }

        // check if all required fields are set
        $validationResult = $this->validateData($data);
        if ($validationResult !== true) {
            return ['status' => 'error', 'message' => $validationResult];
        }

        // typecast values to correct types
        if (!is_string($data['name']) || 
            !is_numeric($data['memory']) || 
            !is_numeric($data['vcpus']) || 
            !is_numeric($data['disk_size']) || 
            !is_string($data['iso_image']) || 
            !is_string($data['network_bridge'])) {
            return ['status' => 'error', 'message' => 'Ungültige Datentypen'];
        }

        // Cast values to correct types
        $name = $data['name'];
        $memory = (string)intval($data['memory']);
        $vcpus = (string)intval($data['vcpus']);
        $diskSize = (string)intval($data['disk_size']);
        $isoImage = $data['iso_image'];
        $networkBridge = $data['network_bridge'];

        // Create Disk-Image
        $diskPath = "/var/lib/libvirt/images/{$name}.qcow2";
        $createDiskCommand = [
            'qemu-img', 'create', '-f', 'qcow2', $diskPath, "{$diskSize}G"
        ];

        $diskResult = $this->executeCommand($createDiskCommand);
        if ($diskResult['status'] === 'error') {
            return $diskResult;
        }

        // Create VM mit virt-install
        $virtInstallCommand = [
            'virt-install',
            '--connect', $this->uri,
            '--name', $name,
            '--memory', $memory,
            '--vcpus', $vcpus,
            '--disk', "path={$diskPath},format=qcow2",
            '--cdrom', $isoImage,
            '--network', "bridge={$networkBridge}",
            '--graphics', 'spice',
            '--noautoconsole'
        ];

        return $this->executeCommand($virtInstallCommand);
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
}
