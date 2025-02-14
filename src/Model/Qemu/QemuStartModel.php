<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuStartModel extends CommandModel
{
    private string $uri = 'qemu:///system';

     /**
     * Handle the QEMU start command
     * 
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
     
         // execute the virsh start command and return the output
         $response = $this->executeCommand(['virsh', '-c', $this->uri, 'start', $domain]);
         
         if ($response['status'] === 'success') {
             $output = is_string($response['output']) ? trim($response['output']) : '';
             
             // WebSocket für SPICE nach erfolgreichem Start initialisieren
             $scriptPath = __DIR__ . '/../../../bin/wsSockets.sh';
             $wsResponse = $this->executeCommand(['/bin/bash', $scriptPath]);
             
             if ($wsResponse['status'] !== 'success') {
                 return [
                     'status' => 'warning',
                     'message' => 'VM gestartet, aber WebSocket-Initialisierung fehlgeschlagen',
                     'data' => $output,
                     'wsError' => $wsResponse['error'] ?? 'Unbekannter Fehler'
                 ];
             }
             
             return [
                 'status' => 'success',
                 'data' => $output,
                 'wsStatus' => 'WebSocket erfolgreich initialisiert'
             ];
         } 
     
         return [
             'status' => 'error',
             'message' => 'Fehler beim Starten der Domain',
             'error' => $response
         ];
     }
}
