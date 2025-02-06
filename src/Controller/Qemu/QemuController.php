<?php
declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Controller\Qemu;

use Zerlix\KvmDash\Api\Model\Qemu\QemuListModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuStartModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuStopModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuRebootModel;
use Zerlix\KvmDash\Api\Model\Qemu\QemuListDetailsModel; // Ensure this class exists in the specified namespace

class QemuController
{
    private QemuListModel $listModel;
    private QemuStartModel $startModel;
    private QemuStopModel $stopModel;
    private QemuRebootModel $rebootModel;
    private QemuListDetailsModel $listDetailModel;

    public function __construct()
    {
        $this->listModel = new QemuListModel();
        $this->startModel = new QemuStartModel();
        $this->stopModel = new QemuStopModel();
        $this->rebootModel = new QemuRebootModel();
        $this->listDetailModel = new QemuListDetailsModel();
    }

    /**
    * Handle the QEMU API requests
    * 
    * @param string $route
    * @param string $method
    * @return array<string, mixed>
    */
    
    public function handle(string $route, string $method): array
    {
        // remove the qemu prefix from the route
        $route = str_replace('qemu/', '', $route);

        // api/qemu/list
        if ($route === 'list' && $method === 'GET') {
            return $this->listModel->handle($route, $method);
        }

        // api/qemu/listdetails/{domain}
        if (strpos($route, 'listdetails/') === 0 && $method === 'GET') {    
            $domain = substr($route, strlen('listdetails/'));
            return $this->listDetailModel->handle($route, $method, $domain);
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
