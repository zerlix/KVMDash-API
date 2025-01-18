<?php

namespace Zerlix\KvmDash\Api\Controller\Host;

use Zerlix\KvmDash\Api\Controller\Host\HostCpuController;
use Zerlix\KvmDash\Api\Controller\Host\HostMemController;
use Zerlix\KvmDash\Api\Controller\Host\HostDiskController;
use Zerlix\KvmDash\Api\Controller\CommandController;

class HostController extends CommandController
{
    private $cpuController;
    private $memController;
    private $diskController;

    public function __construct()
    {
        $this->cpuController = new HostCpuController();
        $this->memController = new HostMemController();
        $this->diskController = new HostDiskController();
    }

    public function handle(string $route, string $method): array
    {
        // remove the host prefix from the route
        $route = str_replace('host/', '', $route);

        // api/host/cpu
        if ($route === 'cpu' && $method === 'GET') {
            return $this->cpuController->handle($route, $method);
        }

        // api/host/mem
        if ($route === 'mem' && $method === 'GET') {
            return $this->memController->handle($route, $method);
        }

        // api/host/disk
        if ($route === 'disk' && $method === 'GET') {
            return $this->diskController->handle($route, $method);
        }

        return [
            'status' => 'error',
            'message' => 'HostController: Route not found'
        ];
    }

}