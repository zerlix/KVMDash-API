<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuNetworkModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Get all available network options
     * 
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?array $data = null): array
    {
        $networks = [];

        // Get bridges
        $bridges = $this->getAvailableBridges();
        foreach ($bridges as $bridge) {
            $networks[] = [
                'name' => $bridge,
                'type' => 'bridge',
                'value' => $bridge
            ];
        }

        // Check NAT network
        $natStatus = $this->checkDefaultNetwork();
        if ($natStatus['exists']) {
            $networks[] = [
                'name' => 'NAT (default)',
                'type' => 'nat',
                'value' => 'default',
                'active' => $natStatus['active']
            ];
        }

        return [
            'status' => 'success',
            'data' => $networks
        ];
    }

    /**
     * Get list of available network bridges from libvirt
     * 
     * @return array<string>
     */
    private function getAvailableBridges(): array
    {
        // Force English locale for virsh output
        $env = ['LANG' => 'C'];
        $command = ['virsh', '--connect', $this->uri, 'net-list', '--all'];
        $result = $this->executeCommand($command, $env);

        if ($result['status'] === 'error') {
            error_log("Error executing virsh command");
            return [];
        }

        $bridges = [];
        $output = $result['output'] ?? '';
        error_log("Raw virsh output:\n" . $output);
        
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            error_log("Processing line: " . $line);
            
            // Skip header and empty lines
            if (empty(trim($line)) || strpos($line, '----') !== false || strpos($line, 'Name') !== false) {
                continue;
            }
            
            // Match "active" status regardless of language
            if (preg_match('/^\s*(\S+)\s+active\s+/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'default') {
                    $bridges[] = $name;
                    error_log("Added bridge: " . $name);
                }
            }
        }

        return array_unique($bridges);
    }

    /**
     * Check status of default NAT network
     * 
     * @return array{exists: bool, active: bool}
     */
    private function checkDefaultNetwork(): array
    {
        // Force English locale for virsh output
        $env = ['LANG' => 'C'];
        $command = ['virsh', '--connect', $this->uri, 'net-list', '--all'];
        $result = $this->executeCommand($command, $env);
        
        if ($result['status'] === 'error') {
            error_log("Error checking default network");
            return ['exists' => false, 'active' => false];
        }

        $output = $result['output'] ?? '';
        $exists = false;
        $active = false;

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (preg_match('/^\s*default\s+active\s+/', $line)) {
                error_log("Found active default network");
                $exists = true;
                $active = true;
                break;
            }
        }

        return [
            'exists' => $exists,
            'active' => $active
        ];
    }
}
