<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\VedioModel;

class ControllerVedio extends Controller
{
    protected $Review;

    public function __construct()
    {
        $this->Review = new VedioModel();
    }

    public function shoeReviewData()
    {
        $activeFilter = $this->request->getGet('active');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        $startDate = $this->request->getVar('startDate');
        $endDate = $this->request->getVar('endDate');

        if ($activeFilter === null || $activeFilter === '') {
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

        $reviewsData = $this->Review->getVedioDateSearch($offset, $limit, $activeFilter, $startDate, $endDate);

        return $this->response->setJSON([
            'status' => 200,
            'data' => $reviewsData,
        ]);
    }

    public function uploadReviewAPI()
    {
        $data = $this->payload();

        $nameTH = $data['nameTH'] ?? $data['nameTH_Vedio'] ?? null;
        $savename = $data['savename'] ?? $data['createname'] ?? null;
        $active = $data['active'] ?? 2;
        $linkVedio = $data['linkURL'] ?? $data['vedio_link'] ?? $data['link'] ?? null;
        $youtube = $data['youtubeID'] ?? $data['vedio_youtubeID'] ?? $this->extractYoutubeId($linkVedio);
        $videoDate = $data['videoCreated'] ?? $data['creationdate'] ?? date('Y-m-d');

        if (!$nameTH || !$savename || !$linkVedio || !$youtube || !$videoDate) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Please fill all required fields',
            ])->setStatusCode(400);
        }

        try {
            if ($this->Review->checkDuplicateLink($linkVedio)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'This video URL already exists',
                ])->setStatusCode(400);
            }

            $vedioData = [
                'int_saksiam_vedio_nameTH' => $nameTH,
                'int_saksiam_vedio_link' => $linkVedio,
                'int_saksiam_vedio_youtubeID' => $youtube,
                'int_saksiam_vedio_creationdate' => $videoDate,
                'int_saksiam_vedio_active' => $active,
                'int_saksiam_vedio_createname' => $savename,
            ];

            $vedioID = $this->Review->createVedioData($vedioData);
            $vedioData['int_saksiam_vedio_id'] = $vedioID;

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Vedio created successfully',
                'int_saksiam_vedio_id' => $vedioID,
                'vedio_data' => $vedioData,
                'data' => $vedioData,
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Save failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function ApprovedReviewStatusAPI($reviewID = null)
    {
        if (is_null($reviewID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Vedio ID is required',
            ])->setStatusCode(400);
        }

        $data = $this->payload();
        $updateData = [
            'int_saksiam_vedio_active' => $data['active'] ?? null,
            'int_saksiam_vedio_note' => $data['note'] ?? $data['rejectReason'] ?? null,
            'int_saksiam_vedio_improvement' => $data['improvement'] ?? $data['improvement_text'] ?? null,
            'int_saksiam_vedio_approvedname' => $data['approvedName'] ?? $data['approvedname'] ?? null,
            'int_saksiam_vedio_approvedDate' => $data['approvedDate'] ?? $data['approvedate'] ?? date('Y-m-d H:i:s'),
            'int_saksiam_vedio_cancellation' => $data['cancellation'] ?? null,
        ];

        $updateData = array_filter($updateData, fn ($value) => $value !== null);

        if (!$this->Review->approvedVedioData($reviewID, $updateData)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Vedio data not found',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Approved successfully',
        ]);
    }

    public function updateReviewDetail($reviewID = null)
    {
        if (is_null($reviewID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Vedio ID is required',
            ])->setStatusCode(400);
        }

        $existing = $this->Review->find($reviewID);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Vedio data not found',
            ])->setStatusCode(404);
        }

        $data = $this->payload();
        $map = [
            'nameTH' => 'int_saksiam_vedio_nameTH',
            'linkURL' => 'int_saksiam_vedio_link',
            'link' => 'int_saksiam_vedio_link',
            'youtubeID' => 'int_saksiam_vedio_youtubeID',
            'active' => 'int_saksiam_vedio_active',
            'updatename' => 'int_saksiam_vedio_updatename',
            'changename' => 'int_saksiam_vedio_changname',
            'approvedName' => 'int_saksiam_vedio_approvedname',
            'approvedname' => 'int_saksiam_vedio_approvedname',
            'approvedDate' => 'int_saksiam_vedio_approvedDate',
            'approvedate' => 'int_saksiam_vedio_approvedDate',
            'note' => 'int_saksiam_vedio_note',
            'rejectReason' => 'int_saksiam_vedio_note',
            'improvement' => 'int_saksiam_vedio_improvement',
            'improvement_text' => 'int_saksiam_vedio_improvement',
            'videoCreated' => 'int_saksiam_vedio_creationdate',
            'creationdate' => 'int_saksiam_vedio_creationdate',
            'cancellation' => 'int_saksiam_vedio_cancellation',
        ];

        $updateData = [];
        foreach ($map as $requestField => $dbField) {
            if (array_key_exists($requestField, $data)) {
                $updateData[$dbField] = $data[$requestField];
            }
        }

        if (
            (array_key_exists('approvedName', $data) || array_key_exists('approvedname', $data)) &&
            !isset($updateData['int_saksiam_vedio_approvedDate'])
        ) {
            $updateData['int_saksiam_vedio_approvedDate'] = date('Y-m-d H:i:s');
        }

        if (empty($updateData)) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'No data changed',
            ]);
        }

        if (!$this->Review->updateVedioData($reviewID, $updateData)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Vedio details updated successfully',
        ]);
    }

    public function showReview($reviewID = null)
    {
        if (is_null($reviewID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Vedio ID is required',
            ])->setStatusCode(400);
        }

        $data = $this->Review->showVedioDataID($reviewID);

        return $this->response->setJSON([
            'status' => 200,
            'data' => $data,
        ]);
    }

    public function deleteReview($reviewID)
    {
        if (empty($reviewID) || !is_numeric($reviewID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid Vedio ID',
            ])->setStatusCode(400);
        }

        if ($this->Review->delete($reviewID)) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Vedio deleted successfully',
            ])->setStatusCode(200);
        }

        return $this->response->setJSON([
            'status' => false,
            'message' => 'Failed to delete Vedio',
        ])->setStatusCode(500);
    }

    public function countActiveStatusReview()
    {
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Data retrieved successfully',
            'result' => $this->Review->countActiveWaitVedios(),
        ]);
    }

    public function showVediographApi()
    {
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Graph API is not configured for this module',
            'result' => [],
        ]);
    }

    public function reviewWebsiteData()
    {
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;

        return $this->response->setJSON([
            'status' => 200,
            'data' => $this->Review->getReviewWebsite($offset, $limit),
        ]);
    }

    private function payload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        return $this->request->getPost() ?: $this->request->getVar() ?: [];
    }

    private function extractYoutubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{6,20})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
