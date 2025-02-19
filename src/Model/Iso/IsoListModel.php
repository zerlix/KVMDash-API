<?php

namespace Zerlix\KvmDash\Api\Model\Iso;

use Zerlix\KvmDash\Api\Model\CommandModel;

class IsoListModel extends CommandModel
{
    /**
     * Handle the ISO list command
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        /** @var string|false $envPath */
        $envPath = $_ENV['LIBVIRT_INSTALL_IMAGES_PATH'] ?? false;
        $isoPath = is_string($envPath) ? $envPath : '';
        
        if ($isoPath === '' || !is_dir($isoPath)) {
            return [
                'status' => 'error',
                'message' => 'ISO-Verzeichnis nicht gefunden'
            ];
        }

        $scanResult = scandir($isoPath);
        if ($scanResult === false) {
            return [
                'status' => 'error',
                'message' => 'Fehler beim Lesen des ISO-Verzeichnisses'
            ];
        }

        $isoFiles = array_filter(
            $scanResult,
            fn(string $file): bool => pathinfo($file, PATHINFO_EXTENSION) === 'iso'
        );

        return [
            'status' => 'success',
            'data' => array_values($isoFiles)
        ];
    }
}