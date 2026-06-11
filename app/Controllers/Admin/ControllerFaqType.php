<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\FaqTypeModel;

class ControllerFaqType extends Controller
{
    protected $FaqType;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->FaqType = new FaqTypeModel();
    }

    /* ======================================================
        SHOW LIST (WEB / SIMPLE)
    ====================================================== */
    public function showFaqTypedatalist()
    {
        $showData = $this->FaqType->showFaqType();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data FAQ type retrieved successfully',
                'result' => $showData
            ]);
        }

        return $this->response->setJSON(0);
    }

    /* ======================================================
        SHOW DATA (MANAGER + PAGINATION)
    ====================================================== */
    public function showFaqTypeData()
    {
        $activeFilter = $this->request->getGet('active');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;

        if ($activeFilter === "") {
            $activeFilter = null;
        } else {
            $activeFilter = (int) $activeFilter;

            if (!in_array($activeFilter, [0, 1])) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Invalid active filter value'
                ])->setStatusCode(400);
            }
        }

        $faqTypeData = $this->FaqType->getFaqTypeData($activeFilter, $offset, $limit);

        if ($faqTypeData) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $faqTypeData,
            ]);
        }

        return $this->response->setJSON(0);
    }

    /* ======================================================
        CREATE
    ====================================================== */
    public function uploadFaqTypeAPI()
    {
        $jsonData = $this->request->getJSON(true);

        $nameTH = $jsonData['nameTH'] ?? null;
        $nameEN = $jsonData['nameEN'] ?? null;
        $savename = $jsonData['savename'] ?? 'Unknown';
        $active = $jsonData['active'] ?? 1;

        if (!$nameTH || !$nameEN) {
            return $this->response->setJSON([
                'error' => 'กรุณากรอกข้อมูลที่จำเป็น'
            ])->setStatusCode(400);
        }

        $duplicate = $this->FaqType
            ->groupStart()
                ->where('int_saksiam_typefqa_nameTH', $nameTH)
                ->orWhere('int_saksiam_typefqa_nameEN', $nameEN)
            ->groupEnd()
            ->first();

        if ($duplicate) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ชื่อประเภทคำถามนี้มีอยู่แล้ว'
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_typefqa_nameTH' => $nameTH,
                'int_saksiam_typefqa_nameEN' => $nameEN,
                'int_saksiam_typefqa_savename' => $savename,
                'int_saksiam_typefqa_active' => $active,
            ];

            $insertID = $this->FaqType->createFaqType($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'FAQ Type created successfully',
                'id' => $insertID
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /* ======================================================
        SHOW BY ID
    ====================================================== */
    public function showFaqType($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ Type ID is required'
            ])->setStatusCode(400);
        }

        try {
            $data = $this->FaqType->showFaqType($id);

            return $this->response->setJSON([
                'status' => 200,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /* ======================================================
        UPDATE
    ====================================================== */
    public function updateFaqTypeDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ Type ID is required'
            ])->setStatusCode(400);
        }

        try {
            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'No data received'
                ])->setStatusCode(400);
            }

            $existing = $this->FaqType->find($id);

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found'
                ])->setStatusCode(404);
            }

            if (isset($data['nameTH']) || isset($data['nameEN'])) {
                $checkNameTH = $data['nameTH'] ?? $existing['int_saksiam_typefqa_nameTH'];
                $checkNameEN = $data['nameEN'] ?? $existing['int_saksiam_typefqa_nameEN'];

                $duplicate = $this->FaqType
                    ->groupStart()
                        ->where('int_saksiam_typefqa_nameTH', $checkNameTH)
                        ->orWhere('int_saksiam_typefqa_nameEN', $checkNameEN)
                    ->groupEnd()
                    ->where('int_saksiam_typefqa_id !=', $id)
                    ->first();

                if ($duplicate) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'ชื่อประเภทซ้ำ'
                    ])->setStatusCode(400);
                }
            }

            $updateData = [];

            if (isset($data['nameTH'])) {
                $updateData['int_saksiam_typefqa_nameTH'] = $data['nameTH'];
            }

            if (isset($data['nameEN'])) {
                $updateData['int_saksiam_typefqa_nameEN'] = $data['nameEN'];
            }

            if (isset($data['active'])) {
                $updateData['int_saksiam_typefqa_active'] = $data['active'];
            }

            if (isset($data['updatename'])) {
                $updateData['int_saksiam_typefqa_updatename'] = $data['updatename'];
            }

            if (isset($data['changename'])) {
                $updateData['int_saksiam_typefqa_changename'] = $data['changename'];
            }

            if (!empty($updateData)) {
                $this->FaqType->updateFaqType($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /* ======================================================
        DELETE
    ====================================================== */
    public function deleteFaqTypeData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ Type ID is required'
            ])->setStatusCode(400);
        }

        try {
            $existing = $this->FaqType->find($id);

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found'
                ])->setStatusCode(404);
            }

            $deleted = $this->FaqType->delete($id);

            if (!$deleted) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delete failed'
                ])->setStatusCode(500);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /* ======================================================
        MOVE ORDER
    ====================================================== */
    public function updateFaqTypeMove()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid data'
            ])->setStatusCode(400);
        }

        $this->FaqType->updateFaqTypeOrder($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order updated'
        ]);
    }
}
