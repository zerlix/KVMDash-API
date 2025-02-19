<?php

namespace Zerlix\KvmDash\Api\Controller\Iso;

use Zerlix\KvmDash\Api\Model\Iso\IsoListModel;
use Zerlix\KvmDash\Api\Model\Iso\IsoTurnkeyListModel;


class IsoController
{
    private  IsoListModel $listModel;
    private  IsoTurnkeyListModel $turnkeyListModel;

    public function __construct()
    {
        $this->listModel = new IsoListModel();
        $this->turnkeyListModel = new IsoTurnkeyListModel();
    }

    /**
     * Handle the ISO API requests
     * 
     * @param string $route
     * @param string $method
     * @return array<string, mixed>
     */
    public function handle(string $route, string $method): array
    {
        // remove the iso prefix from the route
        $route = str_replace('iso/', '', $route);

        // api/iso/list
        if ($route === 'list' && $method === 'GET') {
            return $this->listModel->handle($route, $method);
        }

        /** TODO: Implement the following routes
        // api/iso/upload
        if ($route === 'upload' && $method === 'POST') {
            return ['status' => 'success', 'data' => 'ISO upload'];
        }

        // api/iso/delete
        if ($route === 'delete' && $method === 'DELETE') {
            return ['status' => 'success', 'data' => 'ISO delete'];
        }
        */

        // api/iso/turnkey/list
        if ($route === 'turnkey/list' && $method === 'GET') {
            return $this->turnkeyListModel->handle($route, $method);
        }

        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'IsoController: Route not found'
        ];
    }
}