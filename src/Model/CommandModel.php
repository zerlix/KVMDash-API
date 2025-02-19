<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model;

use Exception;

class CommandModel
{

    /**
     * Execute a command and return the output
     * 
     * @param array<int, string> $command
     * @return array<string, string|mixed>
     */
    protected function executeCommand(array $command): array
    {
        try {
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            $commandString = implode(' ', $command);
            $process = proc_open($commandString, $descriptorspec, $pipes);

            if (!is_resource($process)) {
                throw new \Exception("Konnte Befehl nicht ausführen: $commandString");
            }

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            $exitCode = proc_close($process);

            if ($exitCode !== 0) {
                return [
                    'status' => 'error',
                    'message' => "Command returned non-zero exit code: $exitCode",
                    'error' => $error,
                    'command' => $commandString
                ];
            }

            return ['status' => 'success', 'output' => $output];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
