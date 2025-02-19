<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuDeleteModel extends CommandModel
{
    private string $uri = 'qemu:///system';
    private string $vhdBasePath;

    public function __construct()
    {
        /** @var string|false $envPath */
        $envPath = $_ENV['LIBVIRT_IMAGES_PATH'] ?? false;
        $this->vhdBasePath = is_string($envPath) ? $envPath : '/var/lib/libvirt/images';
    }

    /**
     * @param string $route
     * @param string $method
     * @param string|null $domain
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?string $domain = null): array
    {
        if (!$domain) {
            return ['status' => 'error', 'message' => 'Domain nicht angegeben'];
        }

        // Prüfen ob VHD-Files gelöscht werden sollen
        $deleteVhd = isset($_GET['delete_vhd']) && $_GET['delete_vhd'] === 'true';

        // VM Status prüfen und ggf. zuerst stoppen
        $statusCommand = $this->executeCommand(['virsh', '-c', $this->uri, 'domstate', $domain]);
        if ($statusCommand['status'] === 'success' && trim($statusCommand['output']) === 'running') {
            $stopResponse = $this->executeCommand(['virsh', '-c', $this->uri, 'destroy', $domain]);
            if ($stopResponse['status'] !== 'success') {
                return [
                    'status' => 'error',
                    'message' => 'Fehler beim Stoppen der VM',
                    'error' => $stopResponse['error'] ?? 'Unbekannter Fehler'
                ];
            }
        }

        // VM Definition löschen
        $undefineResponse = $this->executeCommand(['virsh', '-c', $this->uri, 'undefine', $domain]);
        if ($undefineResponse['status'] !== 'success') {
            return [
                'status' => 'error',
                'message' => 'Fehler beim Löschen der VM Definition',
                'error' => $undefineResponse['error'] ?? 'Unbekannter Fehler'
            ];
        }

        // Optional: VHD-Files löschen
        if ($deleteVhd) {
            $vhdPath = $this->vhdBasePath . "/$domain.qcow2";
            if (file_exists($vhdPath)) {
                $vhdResponse = $this->executeCommand(['rm', '-f', $vhdPath]);
                if ($vhdResponse['status'] !== 'success') {
                    return [
                        'status' => 'warning',
                        'message' => 'VM Definition gelöscht, aber Fehler beim Löschen der VHD-Datei',
                        'error' => $vhdResponse['error'] ?? 'Unbekannter Fehler'
                    ];
                }
            }
        }

        return [
            'status' => 'success',
            'message' => 'VM erfolgreich gelöscht' . ($deleteVhd ? ' (inkl. VHD-Dateien)' : '')
        ];
    }
}