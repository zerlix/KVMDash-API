<?php

namespace Zerlix\KvmDash\Api\Model;
use Exception;

abstract class CommandModel 
{
    protected function executeCommand(array $command): array 
    {
        try {

            // validate the command
            $this->validateCommand($command);

            // file descriptors
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            // execute the command
            $process = proc_open(implode(' ', $command), $descriptorspec, $pipes);    
            
            // check if the command was executed successfully
            if (!is_resource($process)) {
                throw new Exception("Unable to execute command: $command");
            }

            // read the output
            $output = stream_get_contents($pipes[1]);
            
            // read the error output
            $errorOutput = stream_get_contents($pipes[2]);
            
            // close the pipes
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }

            // close the process
            $returnVar = proc_close($process);
            if ($returnVar !== 0) {
                throw new Exception($errorOutput);
            }

            // return the output
            return ['status' => 'success', 'output' => $output];

          // catch any exceptions  
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // validate command
    private function validateCommand(array $command): void
    {
        foreach ($command as $cmd) {
            if (!preg_match('/^[a-zA-Z0-9_\-\/\s:.]+$/', $cmd)) {
                throw new Exception('Invalid command');
            }
        }
    }
}