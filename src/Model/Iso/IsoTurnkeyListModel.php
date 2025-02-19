<?php

namespace Zerlix\KvmDash\Api\Model\Iso;

use Zerlix\KvmDash\Api\Model\CommandModel;

class IsoTurnkeyListModel extends CommandModel
{

    /**
     * Handle the Turnkey ISO list command
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        /** @var string|false $envUrl */
        $envUrl = $_ENV['TURNKEY_URL'] ?? false;
        $turnkeyUrl = is_string($envUrl) ? trim($envUrl, "'\" ") : '';

        if ($turnkeyUrl === '') {
            return [
                'status' => 'error',
                'message' => 'Turnkey URL nicht konfiguriert'
            ];
        }

        // Verwende cURL statt file_get_contents
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $turnkeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Optional: SSL-Verifizierung deaktivieren
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html === false || $httpCode !== 200) {
            return [
                'status' => 'error',
                'message' => 'Fehler beim Abrufen der Turnkey ISO Liste (HTTP: ' . $httpCode . ')'
            ];
        }

        // Regex-Pattern für ISO-Dateien
        $pattern = '/href="(turnkey-.*?-[0-9]+\.[0-9]+-.*?\.iso)"/';
        preg_match_all($pattern, $html, $matches);

        if (empty($matches[1])) {
            return [
                'status' => 'error',
                'message' => 'Keine ISO-Dateien gefunden'
            ];
        }

        // Mappe die gefundenen ISOs zu einem Array ohne Größenabfrage
        $isoData = array_map(
            function ($file) use ($turnkeyUrl) {
                return [
                    'name' => $file,
                    'path' => $turnkeyUrl . $file,
                    // Optional: Größe später per AJAX nachladen
                    'size' => 'unknown'  // oder weglassen
                ];
            },
            $matches[1]
        );

        return [
            'status' => 'success',
            'data' => $isoData
        ];
    }

    /**
     * Holt die Dateigröße über HTTP-Header
     * 
     * @param string $url
     * @return string
     */
    private function getFileSize(string $url): string
    {
        $headers = get_headers($url, true);
        $size = $headers['Content-Length'] ?? '0';

        // Konvertiere Bytes in lesbare Größe
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (int)$size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
