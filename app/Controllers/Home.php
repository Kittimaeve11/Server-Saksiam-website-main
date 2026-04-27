<?php

namespace App\Controllers;

use App\Models\ApiKeyModel;
use Exception;

class Home extends BaseController
{
    protected $ApiKeyModel;

    public function __construct()
    {
        $this->ApiKeyModel = new ApiKeyModel();
    }

    public function index(): string
    {
        return view('welcome_message');
    }

    public function gettest()
    {
        return $this->response->setJSON([
            'status' => 200,
            'message' => 'welcome_message https://saksiam.com/home'
        ]);
    }

 public function createApiKey()
{
    try {
        do {
            $key = bin2hex(random_bytes(16));
            $existingKey = $this->ApiKeyModel->where('key', $key)->first();
        } while ($existingKey);

    } catch (Exception $e) {
        return $this->response->setJSON([
            'status' => 500,
            'message' => 'Error generating API key: ' . $e->getMessage(),
        ])->setStatusCode(500);
    }

    $data = [
        'key'            => $key,
        'level'          => 1,
        'ignore_limits'  => 0,
        'is_private_key' => 0,
        'ip_addresses'   => null,
        'date_created'   => date('Y-m-d H:i:s'),  // Set manually
    ];

    $this->ApiKeyModel->insert($data);
    $insertedId = $this->ApiKeyModel->insertID();

    return $this->response->setJSON([
        'status'  => 200,
        'api_key' => $key,
        'id'      => $insertedId,
    ]);
}
}