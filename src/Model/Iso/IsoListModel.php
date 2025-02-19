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
        $isoPath = $_ENV['LIBVIRT_INSTALL_IMAGES_PATH'] ?? '';
        
        if (!is_dir($isoPath)) {
            return [
                'status' => 'error',
                'message' => 'ISO-Verzeichnis nicht gefunden'
            ];
        }

        $isoFiles = array_filter(
            scandir($isoPath),
            fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'iso'
        );

        return [
            'status' => 'success',
            'data' => array_values($isoFiles)
        ];
    }
}