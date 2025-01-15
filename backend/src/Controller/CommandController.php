<?php

namespace Zerlix\Backend\Controller;

use Symfony\Component\Process\Process;
use Exception;



abstract class CommandController 
{
    protected function executeCommand(array $command): array 
    {
        try {
            // execute the command
            $process = new Process($command);
            $process->run();

            // check if the command was successful
            if (!$process->isSuccessful()) {
                return [
                    'status' => 'error',
                    'message' => $process->getErrorOutput()
                ];
            }
            // return the output
            return [
                'status' => 'success',
                'data' => trim($process->getOutput())
            ];
            
          // catch any exceptions  
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}