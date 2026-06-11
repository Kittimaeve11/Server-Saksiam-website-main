<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\MissionModel;

class ControllerMission extends Controller
{
    private const MAX_IMAGE_SIZE_MB = 2;
    private const MAX_IMAGE_SIZE_BYTES = self::MAX_IMAGE_SIZE_MB * 1024 * 1024;
    private const MAX_MISSION_ITEMS = 10;

    protected $Mission;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Mission = new MissionModel();
    }

    public function showMissionWebsite()
    {
        $missions = $this->Mission->getMissionWebsite();

        if ($missions) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data mission retrieved successfully',
                'result' => $missions,
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showMissionData()
    {
        $activeFilter = $this->request->getGet('active');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;

        if ($activeFilter === '' || $activeFilter === null || $activeFilter === 'all') {
            $activeFilter = null;
        } else {
            $activeFilter = (int) $activeFilter;
            if (!in_array($activeFilter, [0, 1], true)) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Invalid active filter value',
                ])->setStatusCode(400);
            }
        }

        return $this->response->setJSON([
            'status' => 200,
            'data' => $this->Mission->getMissionData($activeFilter, $offset, $limit),
        ]);
    }

    public function upMissionDataAPI()
    {
        $titleTH = $this->request->getVar('titleTH');
        $titleEN = $this->request->getVar('titleEN');
        $topicTH = $this->request->getVar('topicTH');
        $topicEN = $this->request->getVar('topicEN');
        $savename = $this->request->getVar('savename') ?? 'Unknown';
        $active = $this->request->getVar('active') ?? 1;

        if (!$titleTH || !$titleEN || !$topicTH || !$topicEN) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission topic and title are required',
            ])->setStatusCode(400);
        }

        if ($this->Mission->getMissionTotalCount() >= self::MAX_MISSION_ITEMS) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission data limit reached. Maximum ' . self::MAX_MISSION_ITEMS . ' items are allowed.',
            ])->setStatusCode(400);
        }

        $picture = $this->uploadMissionPicture(true);
        if (isset($picture['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $picture['error'],
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_mission_titleTH' => $titleTH,
                'int_saksiam_mission_titleEN' => $titleEN,
                'int_saksiam_mission_topicTH' => $topicTH,
                'int_saksiam_mission_topicEN' => $topicEN,
                'int_saksiam_mission_picture' => $picture['path'],
                'int_saksiam_mission_savename' => $savename,
                'int_saksiam_mission_active' => $active,
            ];

            $missionID = $this->Mission->createMissionData($data);
            $data['int_saksiam_mission_ID'] = $missionID;

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Mission data created successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $this->deletePicture($picture['path'] ?? null);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showMisstionDataID($missionID = null)
    {
        if (is_null($missionID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission ID is required',
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 200,
            'data' => $this->Mission->showMissionID($missionID),
        ]);
    }

    public function updateMisstion($missionID = null)
    {
        if (is_null($missionID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Mission->find($missionID);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission not found',
            ])->setStatusCode(404);
        }

        $updateData = [];
        $map = [
            'titleTH' => 'int_saksiam_mission_titleTH',
            'titleEN' => 'int_saksiam_mission_titleEN',
            'topicTH' => 'int_saksiam_mission_topicTH',
            'topicEN' => 'int_saksiam_mission_topicEN',
            'active' => 'int_saksiam_mission_active',
            'updatename' => 'int_saksiam_mission_updatename',
            'changename' => 'int_saksiam_mission_changename',
        ];

        foreach ($map as $requestField => $dbField) {
            if ($this->request->getVar($requestField) !== null) {
                $updateData[$dbField] = $this->request->getVar($requestField);
            }
        }

        $picture = $this->uploadMissionPicture(false, $existing['int_saksiam_mission_picture'] ?? null);
        if (isset($picture['error'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $picture['error'],
            ])->setStatusCode(400);
        }

        if (!empty($picture['path'])) {
            $this->deletePicture($existing['int_saksiam_mission_picture'] ?? null);
            $updateData['int_saksiam_mission_picture'] = $picture['path'];
        }

        try {
            if (!empty($updateData)) {
                $this->Mission->updateMissionData($missionID, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Mission details updated successfully',
            ]);
        } catch (\Exception $e) {
            if (!empty($picture['path'])) {
                $this->deletePicture($picture['path']);
            }

            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deleteMisstionData($missionID = null)
    {
        if (empty($missionID) || !is_numeric($missionID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid Mission ID',
            ])->setStatusCode(400);
        }

        $mission = $this->Mission->find($missionID);
        if (!$mission) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Mission not found',
            ])->setStatusCode(404);
        }

        try {
            if (!$this->Mission->delete($missionID)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Failed to delete Mission',
                ])->setStatusCode(500);
            }

            $this->deletePicture($mission['int_saksiam_mission_picture'] ?? null);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Mission deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'An unexpected error occurred',
            ])->setStatusCode(500);
        }
    }

    private function uploadMissionPicture($required = true, $ignorePath = null)
    {
        $file = $this->request->getFile('picture') ?: $this->request->getFile('missionPicture') ?: $this->request->getFile('image');

        if (!$file || !$file->isValid()) {
            return $required ? ['error' => 'Mission picture is required'] : [];
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            return ['error' => 'Invalid file type. Only JPG, JPEG, GIF, PNG are allowed.'];
        }

        if ($file->getSize() > self::MAX_IMAGE_SIZE_BYTES) {
            return ['error' => 'Image size must not exceed ' . self::MAX_IMAGE_SIZE_MB . 'MB per file'];
        }

        $uploadDir = WRITEPATH . '../public/Misstion/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileHash = md5_file($file->getTempName());
        if ($this->isDuplicatePicture($uploadDir, $fileHash, $ignorePath)) {
            return ['error' => 'This mission picture already exists in the system.'];
        }

        $newFileName = uniqid('Misstion_') . '.' . $extension;
        $file->move($uploadDir, $newFileName);
        $this->compressImageIfGdLoaded($uploadDir . $newFileName);

        return ['path' => 'Misstion/' . $newFileName];
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

    private function isDuplicatePicture($uploadDir, $fileHash, $ignorePath = null)
    {
        foreach (scandir($uploadDir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if ($ignorePath && 'Misstion/' . $file === $ignorePath) {
                continue;
            }

            $existingFile = $uploadDir . $file;
            if (is_file($existingFile) && md5_file($existingFile) === $fileHash) {
                return true;
            }
        }

        return false;
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
}
