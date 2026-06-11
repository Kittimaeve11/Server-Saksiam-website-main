<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\EditoriaModel;

class ControllerEditoria extends Controller
{
    private const MAX_IMAGES = 15;
    private const MAX_IMAGE_SIZE_MB = 2;
    private const MAX_IMAGE_SIZE_BYTES = self::MAX_IMAGE_SIZE_MB * 1024 * 1024;

    protected $Editoria;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Editoria = new EditoriaModel();
    }

    public function showEditoriadatalist()
    {
        $showData = $this->Editoria->showEditoria();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data editorial retrieved successfully',
                'result' => $this->formatWebsiteEditoriaRows($this->decodeGalleryRows($showData)),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showEditoriaData()
    {
        $activeFilter = $this->request->getGet('active');
        $typeFilter = $this->request->getGet('typeID');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;

        if ($activeFilter === '' || $activeFilter === null || $activeFilter === 'all') {
            $activeFilter = null;
        } else {
            $activeFilter = (int) $activeFilter;

            if (!in_array($activeFilter, [0, 1])) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Invalid active filter value',
                ])->setStatusCode(400);
            }
        }

        if ($typeFilter === '' || $typeFilter === null) {
            $typeFilter = null;
        } else {
            $typeFilter = (int) $typeFilter;
        }

        $editoriaData = $this->Editoria->getEditoriaData($activeFilter, $offset, $limit, $typeFilter);
        $editoriaData['editorias'] = $this->decodeGalleryRows($editoriaData['editorias']);
        $editoriaData['articles'] = $editoriaData['editorias'];

        return $this->response->setJSON([
            'status' => 200,
            'data' => $editoriaData,
            'result' => $editoriaData['editorias'],
            'counts' => $editoriaData['counts'],
        ]);
    }

    public function showPinnedLatestEditoria()
    {
        $limit = 10;
        $rows = $this->Editoria->getPinnedLatestEditoria($limit);
        $rows = $this->decodeGalleryRows($rows);
        $rows = $this->formatWebsiteEditoriaRows($rows);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Pinned latest editorial retrieved successfully',
            'limit' => (int) $limit,
            'count' => count($rows),
            'result' => $rows,
            'data' => $rows,
        ]);
    }

    public function uploadEditoriaAPI()
    {
        $typeID = $this->request->getVar('typeID') ?? $this->request->getVar('type');
        $titleTH = $this->request->getVar('titleTH') ?? $this->request->getVar('titieTH');
        $titleEN = $this->request->getVar('titleEN') ?? $this->request->getVar('titieEN');
        $descriptionTH = $this->request->getVar('descriptionTH');
        $descriptionEN = $this->request->getVar('descriptionEN');
        $pin = $this->request->getVar('pin') ?? 0;
        $active = $this->request->getVar('active') ?? 1;
        $createname = $this->request->getVar('createname') ?? $this->request->getVar('savename') ?? 'Unknown';

        if (!$typeID || !$titleTH || !$titleEN || !$descriptionTH || !$descriptionEN) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial type, title and description are required',
            ])->setStatusCode(400);
        }

        $uploadedImages = $this->uploadGalleryImages();
        if (isset($uploadedImages['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $uploadedImages['error'],
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_editoria_num' => $this->request->getVar('num'),
                'int_saksiam_editoria_typeID' => $typeID,
                'int_saksiam_editoria_titieTH' => $titleTH,
                'int_saksiam_editoria_titieEN' => $titleEN,
                'int_saksiam_editoria_descriptionTH' => $descriptionTH,
                'int_saksiam_editoria_descriptionEN' => $descriptionEN,
                'int_saksiam_editoria_gallary' => json_encode($uploadedImages, JSON_UNESCAPED_SLASHES),
                'int_saksiam_editoria_pin' => $pin,
                'int_saksiam_editoria_active' => $active,
                'int_saksiam_editoria_createname' => $createname,
            ];

            $insertID = $this->Editoria->createEditoria($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Editorial created successfully',
                'id' => $insertID,
                'gallery' => $uploadedImages,
            ]);
        } catch (\Exception $e) {
            $this->deleteGalleryFiles($uploadedImages);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showEditoria($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial ID is required',
            ])->setStatusCode(400);
        }

        try {
            $data = $this->Editoria->showEditoria($id);

            return $this->response->setJSON([
                'status' => 200,
                'data' => $this->formatWebsiteEditoriaRow($this->decodeGalleryRow($data)),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updateEditoriaDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Editoria->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        $updateData = [];

        if ($this->request->getVar('typeID') !== null || $this->request->getVar('type') !== null) {
            $updateData['int_saksiam_editoria_typeID'] = $this->request->getVar('typeID') ?? $this->request->getVar('type');
        }

        if ($this->request->getVar('titleTH') !== null || $this->request->getVar('titieTH') !== null) {
            $updateData['int_saksiam_editoria_titieTH'] = $this->request->getVar('titleTH') ?? $this->request->getVar('titieTH');
        }

        if ($this->request->getVar('titleEN') !== null || $this->request->getVar('titieEN') !== null) {
            $updateData['int_saksiam_editoria_titieEN'] = $this->request->getVar('titleEN') ?? $this->request->getVar('titieEN');
        }

        if ($this->request->getVar('descriptionTH') !== null) {
            $updateData['int_saksiam_editoria_descriptionTH'] = $this->request->getVar('descriptionTH');
        }

        if ($this->request->getVar('descriptionEN') !== null) {
            $updateData['int_saksiam_editoria_descriptionEN'] = $this->request->getVar('descriptionEN');
        }

        if ($this->request->getVar('pin') !== null) {
            $updateData['int_saksiam_editoria_pin'] = $this->request->getVar('pin');
        }

        if ($this->request->getVar('active') !== null) {
            $updateData['int_saksiam_editoria_active'] = $this->request->getVar('active');
        }

        if ($this->request->getVar('updatename') !== null) {
            $updateData['int_saksiam_editoria_updatename'] = $this->request->getVar('updatename');
        }

        if ($this->request->getVar('changename') !== null) {
            $updateData['int_saksiam_editoria_chagename'] = $this->request->getVar('changename');
        }

        if ($this->request->getVar('approvename') !== null) {
            $updateData['int_saksiam_editoria_approvename'] = $this->request->getVar('approvename');
        }

        if ($this->request->getVar('approvedate') !== null) {
            $updateData['int_saksiam_editoria_approvedate'] = $this->request->getVar('approvedate');
        }

        if ($this->request->getVar('note') !== null || $this->request->getVar('rejectReason') !== null) {
            $updateData['int_saksiam_editoria_note'] =
                $this->request->getVar('note') ?? $this->request->getVar('rejectReason');
        }

        if ($this->request->getVar('improvement_text') !== null || $this->request->getVar('improvementText') !== null) {
            $updateData['int_saksiam_editoria_improvement'] =
                $this->request->getVar('improvement_text') ?? $this->request->getVar('improvementText');
        }

        if ($this->request->getVar('cancellation') !== null) {
            $updateData['int_saksiam_editoria_cancellation'] = $this->request->getVar('cancellation');
        }

        $uploadedImages = $this->uploadGalleryImages(false);
        if (isset($uploadedImages['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $uploadedImages['error'],
            ])->setStatusCode(400);
        }

        if (!empty($uploadedImages)) {
            $oldGallery = $this->decodeGalleryValue($existing['int_saksiam_editoria_gallary'] ?? '');
            $this->deleteGalleryFiles($oldGallery);
            $updateData['int_saksiam_editoria_gallary'] = json_encode($uploadedImages, JSON_UNESCAPED_SLASHES);
        }

        try {
            if (!empty($updateData)) {
                $this->Editoria->updateEditoria($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);
        } catch (\Exception $e) {
            if (!empty($uploadedImages)) {
                $this->deleteGalleryFiles($uploadedImages);
            }

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deleteEditoriaData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Editoria->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        try {
            if (!$this->Editoria->delete($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delete failed',
                ])->setStatusCode(500);
            }

            $this->deleteGalleryFiles($this->decodeGalleryValue($existing['int_saksiam_editoria_gallary'] ?? ''));

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'An unexpected error occurred',
            ])->setStatusCode(500);
        }
    }

    private function uploadGalleryImages($required = true)
    {
        $files = $this->getGalleryFiles();

        if ($required && empty($files)) {
            return ['error' => 'At least one image is required'];
        }

        if (count($files) > self::MAX_IMAGES) {
            return ['error' => 'Upload images cannot exceed ' . self::MAX_IMAGES . ' files'];
        }

        $allowedTypes = ['jpg', 'jpeg'];
        $uploadDir = WRITEPATH . '../public/Gallery/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploaded = [];
        $uploadedHashes = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            if (!in_array(strtolower($file->getExtension()), $allowedTypes)) {
                $this->deleteGalleryFiles($uploaded);
                return ['error' => 'Invalid file type. Only JPG files are allowed.'];
            }

            if ($file->getSize() > self::MAX_IMAGE_SIZE_BYTES) {
                $this->deleteGalleryFiles($uploaded);
                return ['error' => 'Image size must not exceed ' . self::MAX_IMAGE_SIZE_MB . 'MB per file'];
            }

            $imageHash = $this->getComparableImageHash($file->getTempName());
            if ($imageHash && (in_array($imageHash, $uploadedHashes, true) || $this->isDuplicateGalleryImage($uploadDir, $imageHash))) {
                $this->deleteGalleryFiles($uploaded);
                return ['error' => 'This news/activity image already exists in the system.'];
            }

            $newFileName = uniqid('Gallery_') . '.' . strtolower($file->getExtension());
            $file->move($uploadDir, $newFileName);
            $this->compressImageIfGdLoaded($uploadDir . $newFileName);

            $uploaded[] = 'Gallery/' . $newFileName;
            if ($imageHash) {
                $uploadedHashes[] = $imageHash;
            }
        }

        if ($required && empty($uploaded)) {
            return ['error' => 'At least one valid image is required'];
        }

        return $uploaded;
    }

    private function getGalleryFiles()
    {
        foreach (['gallary', 'gallery', 'images', 'pictures'] as $field) {
            $files = $this->request->getFileMultiple($field);
            if (!empty($files)) {
                return $files;
            }

            $singleFile = $this->request->getFile($field);
            if ($singleFile && $singleFile->isValid()) {
                return [$singleFile];
            }
        }

        return [];
    }

    private function compressImageIfGdLoaded(string $filePath): void
    {
        if (!extension_loaded('gd') || !is_file($filePath)) {
            return;
        }

        \Config\Services::image()
            ->withFile($filePath)
            ->save($filePath, 70);
    }

    private function decodeGalleryRows($rows)
    {
        return array_map(fn ($row) => $this->decodeGalleryRow($row), $rows);
    }

    private function decodeGalleryRow($row)
    {
        if (!$row) {
            return $row;
        }

        $gallery = $this->decodeGalleryValue($row['int_saksiam_editoria_gallary'] ?? $row['gallary'] ?? $row['gallery'] ?? '');
        $row['gallery'] = $gallery;

        return $row;
    }

    private function decodeGalleryValue($value)
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function deleteGalleryFiles($paths)
    {
        foreach ($paths as $path) {
            $fullPath = WRITEPATH . '../public/' . ltrim($path, '/');
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private function formatWebsiteEditoriaRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsiteEditoriaRow($row), $rows);
    }

    private function formatWebsiteEditoriaRow(?array $row): ?array
    {
        if (!$row) {
            return $row;
        }

        return [
            'editoriaNum' => $row['editoriaNum'] ?? $row['int_saksiam_editoria_num'] ?? null,
            'typeID' => $row['typeID'] ?? $row['int_saksiam_editoria_typeID'] ?? null,
            'typeNameTH' => $row['typeNameTH'] ?? null,
            'typeNameEN' => $row['typeNameEN'] ?? null,
            'titleTH' => $row['titleTH'] ?? $row['int_saksiam_editoria_titieTH'] ?? null,
            'titleEN' => $row['titleEN'] ?? $row['int_saksiam_editoria_titieEN'] ?? null,
            'descriptionTH' => $row['descriptionTH'] ?? $row['int_saksiam_editoria_descriptionTH'] ?? null,
            'descriptionEN' => $row['descriptionEN'] ?? $row['int_saksiam_editoria_descriptionEN'] ?? null,
            'gallery' => $row['gallery'] ?? [],
            'pin' => $row['pin'] ?? $row['int_saksiam_editoria_pin'] ?? null,
            'active' => $row['active'] ?? $row['int_saksiam_editoria_active'] ?? null,
            'createname' => $row['createname'] ?? $row['int_saksiam_editoria_createname'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_editoria_creacteAt'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_editoria_updateAt'] ?? null,
            'approvedate' => $row['approvedate'] ?? $row['int_saksiam_editoria_approvedate'] ?? null,
            'approvename' => $row['approvename'] ?? $row['int_saksiam_editoria_approvename'] ?? null,
            'note' => $row['note'] ?? $row['int_saksiam_editoria_note'] ?? null,
            'rejectReason' => $row['rejectReason'] ?? $row['note'] ?? $row['int_saksiam_editoria_note'] ?? null,
            'reason' => $row['reason'] ?? $row['note'] ?? $row['int_saksiam_editoria_note'] ?? null,
            'improvement' => $row['improvement'] ?? $row['int_saksiam_editoria_improvement'] ?? null,
            'improvement_text' => $row['improvement_text'] ?? $row['improvementText'] ?? $row['int_saksiam_editoria_improvement'] ?? null,
            'improvementText' => $row['improvementText'] ?? $row['improvement_text'] ?? $row['int_saksiam_editoria_improvement'] ?? null,
            'cancellation' => $row['cancellation'] ?? $row['int_saksiam_editoria_cancellation'] ?? null,
        ];
    }

    private function getComparableImageHash($path)
    {
        if (!is_file($path)) {
            return null;
        }

        if (!extension_loaded('gd')) {
            return md5_file($path);
        }

        $tempPath = WRITEPATH . 'editoria_hash_' . uniqid() . '.jpg';

        try {
            \Config\Services::image()
                ->withFile($path)
                ->save($tempPath, 70);

            $hash = is_file($tempPath) ? md5_file($tempPath) : md5_file($path);
        } catch (\Throwable $e) {
            $hash = md5_file($path);
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }

        return $hash;
    }

    private function isDuplicateGalleryImage($uploadDir, $newHash)
    {
        if (!is_dir($uploadDir) || empty($newHash)) {
            return false;
        }

        foreach (scandir($uploadDir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $existingFile = $uploadDir . $file;
            if (!is_file($existingFile)) {
                continue;
            }

            $extension = strtolower(pathinfo($existingFile, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg'], true)) {
                continue;
            }

            if (md5_file($existingFile) === $newHash) {
                return true;
            }
        }

        return false;
    }
}
