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

        // Versuche die Datei herunterzuladen
        try {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new \Exception('Curl konnte nicht initialisiert werden');
            }

            $fp = fopen($targetPath, 'w+');
            if ($fp === false) {
                throw new \Exception('Zieldatei konnte nicht erstellt werden');
            }

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0); // Kein Timeout
            
            $success = curl_exec($ch);
            
            if ($success === false) {
                unlink($targetPath); // Lösche unvollständige Datei
                throw new \Exception('Download fehlgeschlagen: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                unlink($targetPath); // Lösche unvollständige Datei
                throw new \Exception('HTTP-Fehler: ' . $httpCode);
            }

            curl_close($ch);
            fclose($fp);

            return [
                'status' => 'success',
                'message' => 'ISO-Datei erfolgreich heruntergeladen',
                'data' => [
                    'filename' => $filename,
                    'path' => $targetPath
                ]
            ];

        } catch (\Exception $e) {
            if (isset($ch)) curl_close($ch);
            if (isset($fp)) fclose($fp);
            
            return [
                'status' => 'error',
                'message' => 'Fehler beim Download: ' . $e->getMessage()
            ];
        }
    }
}