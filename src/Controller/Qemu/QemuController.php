<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Model\Qemu\QemuListModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuStartModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuStopModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuRebootModel;

class QemuController
{
    private $listModel;
    private $startModel;
    private $stopModel;
    private $rebootModel;

    public function __construct()
    {
        $this->listModel = new QemuListModel();
        $this->startModel = new QemuStartModel();
        $this->stopModel = new QemuStopModel();
        $this->rebootModel = new QemuRebootModel();
    }

    public function handle(string $route, string $method): array
    {
        // remove the qemu prefix from the route
        $route = str_replace('qemu/', '', $route);

        // api/qemu/list
        if ($route === 'list' && $method === 'GET') {
            return $this->listModel->handle($route, $method);
        }

        // api/qemu/start/{domain}
        if (strpos($route, 'start') === 0 && $method === 'POST') {
            $domain = substr($route, strlen('start/'));
            return $this->startModel->handle($route, $method, $domain);
        }

        // api/qemu/stop/{domain}
        if (strpos($route, 'stop/') === 0 && $method === 'POST') {
            $domain = substr($route, strlen('stop/'));
            return $this->stopModel->handle($route, $method, $domain);
        }

        // api/qemu/reboot/{domain}
        if (strpos($route, 'reboot/') === 0 && $method === 'POST') {
            $domain = substr($route, strlen('reboot/'));
            return $this->rebootModel->handle($route, $method, $domain);
        }

        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'QemuController: Route not found'
        ];
    }
}
