<?php

namespace Zerlix\Backend\Controller;

use Symfony\Component\Process\Process;
use Exception;

abstract class CommandController 
{
    protected function executeCommand(array $command): array 
    {
        try {
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                return [
                    'status' => 'error',
                    'message' => $process->getErrorOutput()
                ];
            }

            return [
                'status' => 'success',
                'data' => trim($process->getOutput())
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}