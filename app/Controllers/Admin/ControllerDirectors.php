<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\DirectorsModel;

class ControllerDirectors extends Controller
{
    private const MAX_IMAGE_SIZE_MB = 2;
    private const MAX_IMAGE_SIZE_BYTES = self::MAX_IMAGE_SIZE_MB * 1024 * 1024;

    protected $Directors;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Directors = new DirectorsModel();
    }

    public function showDirectorsdatalist()
    {
        $showData = $this->Directors->getTeamWebsite();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data directors retrieved successfully',
                'result' => $this->formatWebsiteDirectorRows($showData),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showDirectorsData()
    {
        $activeFilter = $this->request->getGet('active');
        $tagFilter = $this->request->getGet('tag');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;

        if ($activeFilter === '' || $activeFilter === null || $activeFilter === 'all') {
            $activeFilter = [0, 1, 2, 3, 4];
        } else {
            $activeFilter = array_map('intval', explode(',', (string) $activeFilter));
            foreach ($activeFilter as $filter) {
                if (!in_array($filter, [0, 1, 2, 3, 4])) {
                    return $this->response->setJSON([
                        'status' => 400,
                        'message' => 'Invalid active filter value',
                    ])->setStatusCode(400);
                }
            }
        }

        if ($tagFilter === '' || $tagFilter === null) {
            $tagFilter = null;
        }

        $directorsData = $this->Directors->getDirectorsData($activeFilter, $offset, $limit, $tagFilter);

        return $this->response->setJSON([
            'status' => 200,
            'data' => $directorsData,
            'result' => $directorsData['directors'],
            'counts' => $directorsData['counts'],
        ]);
    }

    public function uploadDirectorsAPI()
    {
        $picture = $this->uploadDirectorPicture(true);
        if (isset($picture['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $picture['error'],
            ])->setStatusCode(400);
        }

        $nameTH = $this->request->getVar('teamnameth') ?? $this->request->getVar('nameTH');
        $nameEN = $this->request->getVar('teamnameen') ?? $this->request->getVar('nameEN');
        $positionTH = $this->request->getVar('teamPositionth') ?? $this->request->getVar('positionTH');
        $positionEN = $this->request->getVar('teamPositionen') ?? $this->request->getVar('positionEN');
        $active = $this->request->getVar('active') ?? 1;
        $savename = $this->request->getVar('savename') ?? 'Unknown';
        $tag = $this->normalizeTag($this->requestValue([
            'int_saksiam_directors_tag',
            'teamTag',
            'directorsTag',
            'tag',
        ]));

        if (!$nameTH || !$nameEN || !$positionTH || !$positionEN) {
            $this->deletePicture($picture['path'] ?? null);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Director name and position are required',
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_directors__picture' => $picture['path'],
                'int_saksiam_directors_nameTH' => $nameTH,
                'int_saksiam_directors_nameEN' => $nameEN,
                'int_saksiam_directors_positionTH' => $positionTH,
                'int_saksiam_directors_positionEN' => $positionEN,
                'int_saksiam_directors_active' => $active,
                'int_saksiam_directors_savename' => $savename,
                'int_saksiam_directors_tag' => $tag,
            ];

            $insertID = $this->Directors->createDirectors($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Directors created successfully',
                'id' => $insertID,
                'picture' => $picture['path'],
            ]);
        } catch (\Exception $e) {
            $this->deletePicture($picture['path'] ?? null);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showDirectors($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Directors ID is required',
            ])->setStatusCode(400);
        }

        try {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $this->formatWebsiteDirectorRow($this->Directors->showDirectors($id)),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updateDirectorsDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Directors ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Directors->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        $updateData = [];

        $map = [
            'teamnameth' => 'int_saksiam_directors_nameTH',
            'teamnameen' => 'int_saksiam_directors_nameEN',
            'teamPositionth' => 'int_saksiam_directors_positionTH',
            'teamPositionen' => 'int_saksiam_directors_positionEN',
            'nameTH' => 'int_saksiam_directors_nameTH',
            'nameEN' => 'int_saksiam_directors_nameEN',
            'positionTH' => 'int_saksiam_directors_positionTH',
            'positionEN' => 'int_saksiam_directors_positionEN',
            'active' => 'int_saksiam_directors_active',
            'tag' => 'int_saksiam_directors_tag',
            'int_saksiam_directors_tag' => 'int_saksiam_directors_tag',
            'teamTag' => 'int_saksiam_directors_tag',
            'directorsTag' => 'int_saksiam_directors_tag',
            'updatename' => 'int_saksiam_directors_updatename',
            'changename' => 'int_saksiam_directors_changename',
            'order' => 'int_saksiam_directors_order',
        ];

        foreach ($map as $requestField => $dbField) {
            if ($this->request->getVar($requestField) !== null) {
                $value = $this->request->getVar($requestField);
                $updateData[$dbField] = $dbField === 'int_saksiam_directors_tag'
                    ? $this->normalizeTag($value)
                    : $value;
            }
        }

        $picture = $this->uploadDirectorPicture(false);
        if (isset($picture['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $picture['error'],
            ])->setStatusCode(400);
        }

        if (!empty($picture['path'])) {
            $this->deletePicture($existing['int_saksiam_directors__picture'] ?? null);
            $updateData['int_saksiam_directors__picture'] = $picture['path'];
        }

        try {
            if (!empty($updateData)) {
                $this->Directors->updateDirectors($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);
        } catch (\Exception $e) {
            if (!empty($picture['path'])) {
                $this->deletePicture($picture['path']);
            }

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deleteDirectorsData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Directors ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Directors->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        try {
            if (!$this->Directors->delete($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delete failed',
                ])->setStatusCode(500);
            }

            $this->deletePicture($existing['int_saksiam_directors__picture'] ?? null);

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

    public function updateDirectorsMove()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid data',
            ])->setStatusCode(400);
        }

        $this->Directors->updateDirectorsOrder($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order updated',
        ]);
    }

    public function getDirectorsDataMove()
    {
        $directors = $this->Directors->getTeamsMove();

        return $this->response->setJSON([
            'status' => 200,
            'data' => $directors,
            'result' => $directors,
        ]);
    }

    private function uploadDirectorPicture($required = true)
    {
        $file = null;
        foreach (['teamPicture', 'picture', 'directors_picture', 'directors__picture', 'directorsPicture', 'directorPicture', 'image', 'file'] as $field) {
            $candidate = $this->request->getFile($field);
            if ($candidate && $candidate->isValid()) {
                $file = $candidate;
                break;
            }
        }

        if (!$file) {
            return $required ? ['error' => 'Director picture is required'] : [];
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
            return ['error' => 'Invalid file type. Only JPG, JPEG, GIF, PNG are allowed.'];
        }

        if ($file->getSize() > self::MAX_IMAGE_SIZE_BYTES) {
            return ['error' => 'Image size must not exceed ' . self::MAX_IMAGE_SIZE_MB . 'MB per file'];
        }

        $uploadDir = WRITEPATH . '../public/About/Teams/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = uniqid('Teams_') . '.' . $extension;
        $file->move($uploadDir, $newFileName);
        $savedPath = $uploadDir . $newFileName;
        $this->compressImageIfGdLoaded($savedPath);

        return ['path' => 'About/Teams/' . $newFileName];
    }

    private function compressImageIfGdLoaded(string $filePath): void
    {
        if (!extension_loaded('gd') || !is_file($filePath)) {
            return;
        }

        \Config\Services::image()
            ->withFile($filePath)
            ->resize(600, 600, true, 'auto')
            ->save($filePath, 70);
    }

    private function deletePicture($path)
    {
        if (empty($path)) {
            return;
        }

        $fullPath = WRITEPATH . '../public/' . ltrim($path, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function requestValue(array $fields)
    {
        foreach ($fields as $field) {
            $value = $this->request->getVar($field);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function normalizeTag($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    private function formatWebsiteDirectorRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsiteDirectorRow($row), $rows);
    }

    private function formatWebsiteDirectorRow(?array $row): ?array
    {
        if (!$row) {
            return $row;
        }

        return [
            'id' => $row['teamsID'] ?? $row['id'] ?? $row['int_saksiam_directors_ID'] ?? null,
            'picture' => $row['teams_picture'] ?? $row['picture'] ?? $row['int_saksiam_directors__picture'] ?? null,
            'nameTH' => $row['teams_nameTH'] ?? $row['nameTH'] ?? $row['int_saksiam_directors_nameTH'] ?? null,
            'nameEN' => $row['teams_nameEN'] ?? $row['nameEN'] ?? $row['int_saksiam_directors_nameEN'] ?? null,
            'positionTH' => $row['teams_positionTH'] ?? $row['positionTH'] ?? $row['int_saksiam_directors_positionTH'] ?? null,
            'positionEN' => $row['teams_positionEN'] ?? $row['positionEN'] ?? $row['int_saksiam_directors_positionEN'] ?? null,
            'active' => $row['active'] ?? $row['int_saksiam_directors_active'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_directors_createAt'] ?? null,
            'savename' => $row['savename'] ?? $row['int_saksiam_directors_savename'] ?? null,
            'tag' => $row['tag'] ?? $row['int_saksiam_directors_tag'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_directors_updateAt'] ?? null,
            'updatename' => $row['updatename'] ?? $row['int_saksiam_directors_updatename'] ?? null,
            'changetime' => $row['changetime'] ?? $row['int_saksiam_directors_changetime'] ?? null,
            'changename' => $row['changename'] ?? $row['int_saksiam_directors_changename'] ?? null,
            'order' => $row['teams_order'] ?? $row['order'] ?? $row['directorsorder'] ?? $row['int_saksiam_directors_order'] ?? null,
        ];
    }
}
