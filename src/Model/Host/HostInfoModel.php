<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Host;

use Zerlix\KvmDash\Api\Model\CommandModel;

class HostInfoModel extends CommandModel
{
    /**
     * Handle the host information request
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        $output = $this->executeCommand(['hostnamectl status --json=pretty']);
        if ($output['status'] === 'success') {
            $data = json_decode($output['output'], true);
            return ['status' => 'success', 'data' => $data];
        }
        return ['status' => 'error', 'message' => $output['message']];
    }
}
