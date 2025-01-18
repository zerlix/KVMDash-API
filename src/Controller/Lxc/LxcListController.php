<?php
namespace Zerlix\KvmDash\Api\Controller\Lxc;

use Zerlix\KvmDash\Api\Controller\CommandController;

class LxcListController extends CommandController
{
    private $uri = 'lxc:///';

    public function handle(string $route, string $method): array
    {
        // execute the virsh list command and return the output
        if ($route === 'list' && $method === 'GET') {
            $response = $this->executeCommand(['virsh', '-c', $this->uri, 'list', '--all']);
            if ($response['status'] === 'success') {
                $formattedOutput = $this->formatOutput($response['output']);
                return ['status' => 'success', 'data' => $formattedOutput];
            }
            return ['status' => 'error', 'message' => 'Unable to list containers'];
        }


        return ['status' => 'error', 'message' => 'Route not found'];
    }

    // Beispiel für die Formatierung der Ausgabe
    private function formatOutput(string $output): array
    {
        $result = [];
        var_dump($output);
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            // Formatierung der Ausgabe hier...
        }
        return $result;
    }
}