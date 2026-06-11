<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Controller;

class ControlleContact extends BaseController
{
 protected $jsonPath;

    public function __construct()
    {
        $this->jsonPath = WRITEPATH . 'config/contact.json';
    }

    // =========================
    // GET CONTACT
    // =========================
     public function index()
    {
        if (!file_exists($this->jsonPath)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'contact.json not found'
            ]);
        }

        $json = file_get_contents($this->jsonPath);
        $data = json_decode($json, true);

        return $this->response->setJSON([
            'status' => true,
            'data' => $data
        ]);
    }

    // =========================
    // UPDATE CONTACT
    // =========================
 public function update()
{
    helper(['filesystem']);

    // =========================
    // READ OLD JSON
    // =========================

    $oldData = [];

    if (file_exists($this->jsonPath)) {
        $oldJson = file_get_contents($this->jsonPath);
        $oldData = json_decode($oldJson, true);
    }

    // =========================
    // FORM DATA
    // =========================

    $request = $this->request->getPost();

    if (!$request) {
        $request = $oldData;
    }

    // =========================
    // IMAGES DEFAULT
    // =========================

    if (!isset($request['images'])) {
        $request['images'] = $oldData['images'] ?? [];
    }

    // =========================
    // UPLOAD FOLDER
    // =========================

    $folder = FCPATH . 'Contact/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // =========================
    // COVER
    // =========================

    $coverFile = $this->request->getFile('cover');

    if ($coverFile && $coverFile->isValid()) {

        // ลบไฟล์เก่า
        if (
            isset($oldData['images']['cover']) &&
            file_exists(FCPATH . $oldData['images']['cover'])
        ) {
            unlink(FCPATH . $oldData['images']['cover']);
        }

        $newName = $coverFile->getRandomName();

        $coverFile->move($folder, $newName);

        $request['images']['cover'] =
            '/Contact/' . $newName;
    }

    // =========================
    // QR LINE
    // =========================

    $qrFile = $this->request->getFile('qr_line');

    if ($qrFile && $qrFile->isValid()) {

        if (
            isset($oldData['images']['qr_line']) &&
            file_exists(FCPATH . $oldData['images']['qr_line'])
        ) {
            unlink(FCPATH . $oldData['images']['qr_line']);
        }

        $newName = $qrFile->getRandomName();

        $qrFile->move($folder, $newName);

        $request['images']['qr_line'] =
            '/Contact/' . $newName;
    }

    // =========================
    // PDF
    // =========================

   $pdfFile = $this->request->getFile('regiter');

if ($pdfFile && $pdfFile->isValid()) {

    $allowedMimeTypes = [
        'application/pdf'
    ];

    if (
        !in_array(
            $pdfFile->getMimeType(),
            $allowedMimeTypes
        )
    ) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Only PDF files are allowed'
        ]);
    }

    // ลบไฟล์เก่า
    if (
        isset($oldData['images']['regiter']) &&
        file_exists(FCPATH . $oldData['images']['regiter'])
    ) {
        unlink(FCPATH . $oldData['images']['regiter']);
    }

    $newName = $pdfFile->getRandomName();

    $pdfFile->move($folder, $newName);

    $request['images']['regiter'] =
        '/Contact/' . $newName;
}
    // =========================
    // SAVE JSON
    // =========================

    $json = json_encode(
        $request,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    file_put_contents(
        $this->jsonPath,
        $json
    );

    return $this->response->setJSON([
        'status' => true,
        'data' => $request,
        'message' => 'Updated successfully'
    ]);
}

}
