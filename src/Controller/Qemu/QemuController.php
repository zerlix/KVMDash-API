<?php

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Controller\CommandController;
use Zerlix\KvmDash\Api\Controller\Qemu\QemuListController;
use Zerlix\KvmDash\Api\Controller\Qemu\QemuStartController;
use Zerlix\KvmDash\Api\Controller\Qemu\QemuStopController;

class QemuController extends CommandController
{
    private $listController;
    private $startController;
    private $stopController;

    public function __construct()
    {
        $this->listController = new QemuListController();
        $this->startController = new QemuStartController();
        $this->stopController = new QemuStopController();
    }

    public function handle(string $route, string $method): array
    {
        // remove the qemu prefix from the route
        $route = str_replace('qemu/', '', $route);

        // api/qemu/list
        if ($route === 'list' && $method === 'GET') {
            return $this->listController->handle($route, $method);
        }

        var_dump($method);
        // api/qemu/start/{domain}
        if (strpos($route, 'start') === 0 && $method === 'POST') {
            $domain = substr($route, strlen('start/'));
            return $this->startController->handle($route, $method, $domain);
        }

        // api/qemu/stop/{domain}
        if (strpos($route, 'stop/') === 0 && $method === 'POST') {
            $domain = substr($route, strlen('stop/'));
            return $this->stopController->handle($route, $method, $domain);
        }

        return [
            'status' => 'error',
            'message' => 'Route not found QemuController'
        ];
    }
}
