<?php

namespace Zerlix\KvmDash\Api\Model\Iso;

use Zerlix\KvmDash\Api\Model\CommandModel;

class IsoStatusModel extends CommandModel {
    public function handle(string $route, string $method): array {
        $statusFile = sys_get_temp_dir() . '/iso_download_status.json';
        
        if (!file_exists($statusFile)) {
            return ['status' => 'error', 'message' => 'Kein aktiver Download'];
        }

        $status = json_decode(file_get_contents($statusFile), true);
        
        if ($status === null) {
            return ['status' => 'error', 'message' => 'Status konnte nicht gelesen werden'];
        }

        return $status;
    }
}
