<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\GalleryModel;

class ControllerGallery extends Controller
{
    private const MAX_IMAGES = 10;
    private const MAX_IMAGE_SIZE_MB = 2;
    private const MAX_IMAGE_SIZE_BYTES = self::MAX_IMAGE_SIZE_MB * 1024 * 1024;

    protected $Gallery;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Gallery = new GalleryModel();
    }

    public function showGalleryData()
    {
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        $type = $this->request->getGet('type') ?: null;
        $namepage = $this->request->getGet('namepage') ?: null;

        $galleryData = $this->Gallery->getGalleryData($offset, $limit, $type, $namepage);
        $galleryData['gallery'] = $this->decodeGalleryRows($galleryData['gallery']);
        $galleryData['galleries'] = $galleryData['gallery'];

        return $this->response->setJSON([
            'status' => 200,
            'data' => $galleryData,
            'result' => $galleryData['gallery'],
            'counts' => $galleryData['counts'],
        ]);
    }

    public function showGallerylist()
    {
        $showData = $this->Gallery->showGallery();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data gallery retrieved successfully',
                'result' => $this->formatWebsiteGalleryRows($this->decodeGalleryRows($showData)),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function uploadGalleryAPI()
    {
        $namepage = $this->resolveNamepage();

        $uploaded = $this->uploadGalleryImages();
        if (isset($uploaded['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $uploaded['error'],
            ])->setStatusCode(400);
        }

        try {
            $created = [];

            foreach ($uploaded['items'] as $item) {
                $data = [
                    'int_saksiam_gallery_path' => $item['path'],
                    'int_saksiam_gallery_type' => $item['mime'],
                    'int_saksiam_gallery_namepage' => $namepage,
                    'int_saksiam_gallery_thumbnail' => '',
                    'int_saksiam_gallery_filesize' => $item['size'],
                    'int_saksiam_gallery_extensin' => $item['extension'],
                ];

                $data['int_saksiam_gallery_ID'] = $this->Gallery->createGallery($data);
                $created[] = $data;
            }

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Gallery created successfully',
                'data' => $created,
                'paths' => $uploaded['paths'],
            ]);
        } catch (\Exception $e) {
            $this->deleteGalleryFiles($uploaded['paths']);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showGallery($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gallery ID is required',
            ])->setStatusCode(400);
        }

        try {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $this->formatWebsiteGalleryRow($this->decodeGalleryRow($this->Gallery->showGallery($id))),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updateGalleryAPI($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gallery ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Gallery->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        $updateData = [];

        if (
            $this->request->getVar('namepage') !== null ||
            $this->request->getVar('page') !== null ||
            $this->request->getVar('type') !== null
        ) {
            $updateData['int_saksiam_gallery_namepage'] = $this->resolveNamepage();
        }

        $uploaded = $this->uploadGalleryImages(false);
        if (isset($uploaded['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $uploaded['error'],
            ])->setStatusCode(400);
        }

        if (!empty($uploaded['items'])) {
            $item = $uploaded['items'][0];
            $oldPaths = $this->decodeList($existing['int_saksiam_gallery_path'] ?? '');
            $this->deleteGalleryFiles($oldPaths);

            $updateData['int_saksiam_gallery_path'] = $item['path'];
            $updateData['int_saksiam_gallery_type'] = $item['mime'];
            $updateData['int_saksiam_gallery_thumbnail'] = '';
            $updateData['int_saksiam_gallery_filesize'] = $item['size'];
            $updateData['int_saksiam_gallery_extensin'] = $item['extension'];
        }

        try {
            if (!empty($updateData)) {
                $this->Gallery->updateGallery($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);
        } catch (\Exception $e) {
            if (!empty($uploaded['paths'])) {
                $this->deleteGalleryFiles($uploaded['paths']);
            }

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deleteGalleryData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gallery ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Gallery->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        try {
            if (!$this->Gallery->delete($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delete failed',
                ])->setStatusCode(500);
            }

            $this->deleteGalleryFiles($this->decodeList($existing['int_saksiam_gallery_path'] ?? ''));

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

        $paths = [];
        $items = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            $mimeType = $file->getMimeType() ?: 'image/jpeg';
            $originalSize = $file->getSize();
            if (!in_array($extension, $allowedTypes)) {
                $this->deleteGalleryFiles($paths);
                return ['error' => 'Invalid file type. Only JPG files are allowed.'];
            }

            if ($originalSize > self::MAX_IMAGE_SIZE_BYTES) {
                $this->deleteGalleryFiles($paths);
                return ['error' => 'Image size must not exceed ' . self::MAX_IMAGE_SIZE_MB . 'MB per file'];
            }

            $newFileName = uniqid('Gallery_') . '.' . $extension;
            $file->move($uploadDir, $newFileName);
            $savedPath = $uploadDir . $newFileName;

            if (extension_loaded('gd')) {
                \Config\Services::image()
                    ->withFile($savedPath)
                    ->save($savedPath, 70);
            }

            $paths[] = 'Gallery/' . $newFileName;
            $items[] = [
                'path' => 'Gallery/' . $newFileName,
                'mime' => $mimeType,
                'size' => is_file($savedPath) ? filesize($savedPath) : $originalSize,
                'extension' => $extension,
            ];
        }

        if ($required && empty($paths)) {
            return ['error' => 'At least one valid image is required'];
        }

        return [
            'items' => $items,
            'paths' => $paths,
        ];
    }

    private function resolveNamepage()
    {
        $rawNamepage = $this->request->getVar('namepage') ?? $this->request->getVar('page');
        $module = strtolower((string) ($this->request->getVar('module') ?? $this->request->getVar('type') ?? ''));

        if ($rawNamepage !== null && $rawNamepage !== '' && !is_numeric($rawNamepage)) {
            return $rawNamepage;
        }

        if (in_array($module, ['editoria', 'editorial', 'article', 'articles'], true)) {
            return 'บทความ';
        }

        if (in_array($module, ['portfolio', 'work', 'works'], true)) {
            return 'ผลงาน';
        }

        if (in_array($module, ['product', 'products'], true)) {
            return 'ผลิตภัณฑ์';
        }

        return $rawNamepage ?: 'แกลเลอรี';
    }

    private function getGalleryFiles()
    {
        foreach (['gallery', 'gallary', 'images', 'pictures', 'path'] as $field) {
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

    private function decodeGalleryRows($rows)
    {
        return array_map(fn ($row) => $this->decodeGalleryRow($row), $rows);
    }

    private function decodeGalleryRow($row)
    {
        if (!$row) {
            return $row;
        }

        $paths = $this->decodeList($row['int_saksiam_gallery_path'] ?? $row['path'] ?? '');

        $row['paths'] = $paths;
        $row['pathList'] = $paths;

        return $row;
    }

    private function decodeList($value)
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

    private function formatWebsiteGalleryRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsiteGalleryRow($row), $rows);
    }

    private function formatWebsiteGalleryRow(?array $row): ?array
    {
        if (!$row) {
            return $row;
        }

        return [
            'id' => $row['id'] ?? $row['int_saksiam_gallery_ID'] ?? null,
            'path' => $row['path'] ?? $row['int_saksiam_gallery_path'] ?? null,
            'type' => $row['type'] ?? $row['int_saksiam_gallery_type'] ?? null,
            'namepage' => $row['namepage'] ?? $row['int_saksiam_gallery_namepage'] ?? null,
            'thumbnail' => $row['thumbnail'] ?? $row['int_saksiam_gallery_thumbnail'] ?? null,
            'filesize' => $row['filesize'] ?? $row['int_saksiam_gallery_filesize'] ?? null,
            'extensin' => $row['extensin'] ?? $row['int_saksiam_gallery_extensin'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_gallery_creatreAt'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_gallery_updateAt'] ?? null,
            'paths' => $row['paths'] ?? [],
            'pathList' => $row['pathList'] ?? $row['paths'] ?? [],
        ];
    }
}
