<?php

namespace Zerlix\KvmDash\Api\Controller\Iso;

use Zerlix\KvmDash\Api\Model\Iso\IsoListModel;

class IsoController
{
    private  IsoListModel $listModel;

    public function __construct()
    {
        $this->listModel = new IsoListModel();
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


        // api/iso/upload
        if ($route === 'upload' && $method === 'POST') {
            return ['status' => 'success', 'data' => 'ISO upload'];
        }

        // api/iso/delete
        if ($route === 'delete' && $method === 'DELETE') {
            return ['status' => 'success', 'data' => 'ISO delete'];
        }

        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'IsoController: Route not found'
        ];
    }
}