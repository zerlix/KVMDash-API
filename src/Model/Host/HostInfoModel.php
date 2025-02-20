<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostInfoModel extends CommandModel
{
    /** @var array<string, string> */
    private array $commandPaths = [
        'hostnamectl' => '/usr/bin/hostnamectl'
    ];

    /**
     * Handle the host information request
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        $command = ['hostnamectl', 'status', '--json=pretty'];
        /** @var array{status:string, output:string, message?:string} */
        $output = $this->executeCommand($command);
        
        if ($output['status'] === 'error') {
            return [
                'status' => 'error',
                'message' => $output['message'] ?? 'Konnte Host-Informationen nicht abrufen'
            ];
        }

        $jsonData = json_decode($output['output'], true);
        if (!is_array($jsonData)) {
            return [
                'status' => 'error',
                'message' => 'Ungültige Ausgabe von hostnamectl'
            ];
        }

        return [
            'status' => 'success',
            'data' => $jsonData
        ];
    }
}
