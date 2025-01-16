<?php

namespace Zerlix\KvmDash\Api\Controller;
use Exception;

abstract class CommandController 
{
    protected function executeCommand(array $command): array 
    {
        try {

            // validate command 
            foreach ($command as $cmd) {
                if (!preg_match('/^[a-zA-Z0-9_\-\/\s:.]+$/', $cmd)) {
                    throw new Exception('Invalid command');
                }
            }

            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            $process = proc_open(implode(' ', $command), $descriptorspec, $pipes);    
            
            if (!is_resource($process)) {
                throw new Exception("Unable to execute command: $command");
            }

            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);
            
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnVar = proc_close($process);
            
            if ($returnVar !== 0) {
                throw new Exception($errorOutput);
            }

            return ['status' => 'success', 'output' => $output];

          // catch any exceptions  
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}