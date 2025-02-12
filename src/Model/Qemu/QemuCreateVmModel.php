<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model\Qemu;

use Zerlix\KvmDash\Api\Model\CommandModel;

class QemuCreateVmModel extends CommandModel
{
    private string $uri = 'qemu:///system';

    /**
     * Handle the QEMU stop command
     * 
     * @param string $route
     * @param string $method
     * @param string|null $domain
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method, ?string $domain = null): array {
        var_dump($route, $method, $domain);
        return ['status' => 'success', 'message' => 'Create VM'];
    }
}
