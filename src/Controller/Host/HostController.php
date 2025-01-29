<?php

namespace Zerlix\KvmDash\Api\Controller\Host;

use Zerlix\KvmDash\Api\Model\Host\HostCpuModel;
use Zerlix\KvmDash\Api\Model\Host\HostMemModel;
use Zerlix\KvmDash\Api\Model\Host\HostDiskModel;
use Zerlix\KvmDash\Api\Model\Host\HostInfoModel;

class HostController
{
    private $cpuModel;
    private $memModel;
    private $diskModel;
    private $infoModel;

    public function __construct()
    {
        $this->cpuModel = new HostCpuModel();
        $this->memModel = new HostMemModel();
        $this->diskModel= new HostDiskModel();
        $this->infoModel = new HostInfoModel();
        
    }

    public function handle(string $route, string $method): array
    {
        // remove the host prefix from the route
        $route = str_replace('host/', '', $route);

        // api/host/sysinfo
        if ($route === 'info' && $method === 'GET') {
            return $this->infoModel->handle($route, $method);
        }
        // api/host/cpu
        if ($route === 'cpu' && $method === 'GET') {
            return $this->cpuModel->handle($route, $method);
        }

        // api/host/mem
        if ($route === 'mem' && $method === 'GET') {
            return $this->memModel->handle($route, $method);
        }

        // api/host/disk
        if ($route === 'disk' && $method === 'GET') {
            return $this->diskModel->handle($route, $method);
        }

        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'HostController: Route not found'
        ];
    }

}