<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuOsInfoModel extends CommandModel
{
    /**
     * Handle the OS variants list request
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        $command = ['virt-install', '--osinfo', 'list'];
        $result = $this->executeCommand($command);

        if ($result['status'] === 'error') {
            return $result;
        }

        // Konvertiere Output-String in Array und filtere leere Zeilen
        $variants = array_values(array_filter(
            explode("\n", $result['output']),
            fn($line) => !empty(trim($line))
        ));

        return [
            'status' => 'success',
            'data' => $variants
        ];
    }
}
