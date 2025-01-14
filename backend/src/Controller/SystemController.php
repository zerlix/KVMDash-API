<?php

namespace Zerlix\Backend\Controller;

use Symfony\Component\Process\Process;
use Exception;

class SystemController
{
    public function handle(string $route, string $method): array
    {
        if ($route === '/api/system/uptime' && $method === 'GET') {
            $process = new Process(['uptime']);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new Exception($process->getErrorOutput());
            }

            return [
                'status' => 'success',
                'data' => trim($process->getOutput())
            ];
        }

        // Neues Kommando für cat /proc/loadavg (Systemlast)
        if ($route === '/api/system/load' && $method === 'GET') {
            $process = new Process(['cat', '/proc/loadavg']);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new Exception($process->getErrorOutput());
            }

            return [
                'status' => 'success',
                'data' => trim($process->getOutput())
            ];
        }

        // Neues Kommando für df -h (Festplattennutzung)
        if ($route === '/api/system/disk' && $method === 'GET') {
          $process = new Process(['df', '-h']);
          $process->run();

          if (!$process->isSuccessful()) {
              throw new Exception($process->getErrorOutput());
          }

          return [
              'status' => 'success',
              'data' => trim($process->getOutput())
          ];
      }

      return ['error' => 'Route not found'];
    }
}