<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Website extends ResourceController
{
    protected $format = 'json';

    private string $jsonPath;

    public function __construct()
    {
        $configDir = WRITEPATH . 'config/';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }

        $this->jsonPath = $configDir . 'theme.json';
    }

    public function getThemeMode()
    {
        if (!file_exists($this->jsonPath)) {
            file_put_contents($this->jsonPath, json_encode(['mode' => 'normal'], JSON_PRETTY_PRINT));
        }

        $data = json_decode(file_get_contents($this->jsonPath), true);
        if (!is_array($data) || empty($data['mode'])) {
            $data = ['mode' => 'normal'];
        }

        return $this->respond($data);
    }

    public function setThemeMode()
    {
        $json = $this->request->getJSON(true);
        $mode = $json['mode'] ?? $this->request->getPost('mode') ?? 'normal';

        if (!in_array($mode, ['normal', 'grayscale', 'dark', 'light'], true)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Invalid theme mode',
            ], 400);
        }

        file_put_contents($this->jsonPath, json_encode(['mode' => $mode], JSON_PRETTY_PRINT));

        return $this->respond([
            'status' => 'success',
            'mode' => $mode,
        ]);
    }
}
