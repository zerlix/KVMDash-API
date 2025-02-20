<?php

namespace Zerlix\KvmDash\Api\Model\Iso;

use Zerlix\KvmDash\Api\Model\CommandModel;

class IsoUploadModel extends CommandModel {
    /**
     * Handle the ISO upload command
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array {
        // Prüfe ob POST-Request
        if ($method !== 'POST') {
            return ['status' => 'error', 'message' => 'Nur POST-Requests erlaubt'];
        }

        // Hole JSON-Daten aus dem Request-Body
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (!isset($data['url'])) {
            return ['status' => 'error', 'message' => 'Keine URL angegeben'];
        }

        $url = $data['url'];

        // Validiere URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['status' => 'error', 'message' => 'Ungültige URL'];
        }

        // Prüfe auf .iso-Endung
        if (!str_ends_with(strtolower($url), '.iso')) {
            return ['status' => 'error', 'message' => 'URL muss auf .iso enden'];
        }

        // Hole Zielverzeichnis aus ENV
        $targetDir = $_ENV['LIBVIRT_INSTALL_IMAGES_PATH'] ?? false;
        if (!$targetDir || !is_dir($targetDir)) {
            return ['status' => 'error', 'message' => 'Zielverzeichnis nicht konfiguriert oder nicht verfügbar'];
        }

        // Extrahiere Dateinamen aus URL
        $filename = basename($url);
        $targetPath = rtrim($targetDir, '/') . '/' . $filename;

        // Prüfe ob Datei bereits existiert
        if (file_exists($targetPath)) {
            return ['status' => 'error', 'message' => 'ISO-Datei existiert bereits'];
        }

        // Starte Download im Hintergrund
        $cmd = sprintf(
            'nohup php -r \'
            $ch = curl_init("%s");
            $fp = fopen("%s", "w+");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $success = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            if ($success) {
                file_put_contents("%s", json_encode(["status" => "success"]));
            } else {
                file_put_contents("%s", json_encode(["status" => "error", "message" => "Download failed"]));
            }
            \' > /dev/null 2>&1 &',
            escapeshellarg($url),
            escapeshellarg($targetPath),
            sys_get_temp_dir() . '/iso_download_status.json',
            sys_get_temp_dir() . '/iso_download_status.json'
        );
        
        exec($cmd);

        $this->updateDownloadStatus('downloading', 'Download gestartet');

        return [
            'status' => 'success',
            'message' => 'Download wurde gestartet'
        ];
    }

    private function updateDownloadStatus(string $status, string $message = ''): void {
        $statusFile = sys_get_temp_dir() . '/iso_download_status.json';
        $statusData = [
            'status' => $status,
            'message' => $message,
            'timestamp' => time()
        ];
        file_put_contents($statusFile, json_encode($statusData));
    }
}