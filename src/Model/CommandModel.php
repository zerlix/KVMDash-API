<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model;

use Exception;

class CommandModel
{
    /** @var array<string, string> */
    private array $commandPaths = [
        'virsh' => '/usr/bin/virsh',
        'qemu-img' => '/usr/bin/qemu-img',
        'virt-install' => '/usr/bin/virt-install',
        'ip' => '/usr/sbin/ip',
        'bash' => '/bin/bash'
    ];

    /**
     * Execute a command and return the result
     * 
     * @param array<string> $command
     * @param array<string, string> $env Optional environment variables
     * @return array{status: string, output?: string, error?: string}
     */
    protected function executeCommand(array $command, array $env = []): array
    {
        // Replace command with full path
        if (isset($this->commandPaths[$command[0]])) {
            $command[0] = $this->commandPaths[$command[0]];
        }

        error_log("Executing command: " . implode(' ', $command));

        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        // Set base environment variables if none provided
        if (empty($env)) {
            $env = [
                'LANG' => 'C',
                'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
            ];
        }

        $process = proc_open($command, $descriptorspec, $pipes, null, $env);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnValue = proc_close($process);

            if ($returnValue === 0) {
                return ['status' => 'success', 'output' => $output];
            }

            return [
                'status' => 'error',
                'output' => $output,
                'error' => $error
            ];
        }

        return ['status' => 'error', 'error' => 'Could not execute command'];
    }
}
