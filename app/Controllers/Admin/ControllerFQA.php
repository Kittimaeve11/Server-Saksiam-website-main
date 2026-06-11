<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\FqaModel;

class ControllerFQA extends Controller
{
    protected $Fqa;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Fqa = new FqaModel();
    }

    public function showFqadatalist()
    {
        $showData = $this->Fqa->showFqa();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data FAQ retrieved successfully',
                'result' => $this->formatWebsiteFaqRows($showData),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showFqaData()
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

        $fqaData = $this->Fqa->getFqaData($activeFilter, $offset, $limit, $typeFilter);

        if ($fqaData) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $fqaData,
                'result' => $fqaData['fqas'],
                'counts' => $fqaData['counts'],
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function uploadFqaAPI()
    {
        $jsonData = $this->request->getJSON(true);

        $typeID = $jsonData['typeID'] ?? $jsonData['faqtypeID'] ?? $jsonData['type'] ?? null;
        $questionTH = $jsonData['questionTH'] ?? null;
        $questionEN = $jsonData['questionEN'] ?? null;
        $answerTH = $jsonData['answerTH'] ?? $jsonData['answersTH'] ?? null;
        $answerEN = $jsonData['answerEN'] ?? $jsonData['answersEN'] ?? null;
        $savename = $jsonData['savename'] ?? 'Unknown';
        $active = $jsonData['active'] ?? 1;

        if (!$typeID || !$questionTH || !$questionEN || !$answerTH || !$answerEN) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ type, question and answer are required',
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_fqa_type' => $typeID,
                'int_saksiam_fqa_questionTH' => $questionTH,
                'int_saksiam_fqa_questionEN' => $questionEN,
                'int_saksiam_fqa_answersTH' => $answerTH,
                'int_saksiam_fqa_answersEN' => $answerEN,
                'int_saksiam_fqa_savename' => $savename,
                'int_saksiam_fqa_active' => $active,
            ];

            $insertID = $this->Fqa->createFqa($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'FAQ created successfully',
                'id' => $insertID,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showFqa($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ ID is required',
            ])->setStatusCode(400);
        }

        try {
            $data = $this->Fqa->showFqa($id);

            return $this->response->setJSON([
                'status' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updateFqaDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ ID is required',
            ])->setStatusCode(400);
        }

        try {
            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'No data received',
                ])->setStatusCode(400);
            }

            $existing = $this->Fqa->find($id);

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found',
                ])->setStatusCode(404);
            }

            $updateData = [];

            if (isset($data['typeID']) || isset($data['faqtypeID']) || isset($data['type'])) {
                $updateData['int_saksiam_fqa_type'] = $data['typeID'] ?? $data['faqtypeID'] ?? $data['type'];
            }

            if (isset($data['questionTH'])) {
                $updateData['int_saksiam_fqa_questionTH'] = $data['questionTH'];
            }

            if (isset($data['questionEN'])) {
                $updateData['int_saksiam_fqa_questionEN'] = $data['questionEN'];
            }

            if (isset($data['answerTH']) || isset($data['answersTH'])) {
                $updateData['int_saksiam_fqa_answersTH'] = $data['answerTH'] ?? $data['answersTH'];
            }

            if (isset($data['answerEN']) || isset($data['answersEN'])) {
                $updateData['int_saksiam_fqa_answersEN'] = $data['answerEN'] ?? $data['answersEN'];
            }

            if (isset($data['active'])) {
                $updateData['int_saksiam_fqa_active'] = $data['active'];
            }

            if (isset($data['updatename'])) {
                $updateData['int_saksiam_fqa_updatename'] = $data['updatename'];
            }

            if (isset($data['changename'])) {
                $updateData['int_saksiam_fqa_changname'] = $data['changename'];
            }

            if (isset($data['changname'])) {
                $updateData['int_saksiam_fqa_changname'] = $data['changname'];
            }

            if (!empty($updateData)) {
                $this->Fqa->updateFqa($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deleteFqaData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'FAQ ID is required',
            ])->setStatusCode(400);
        }

        try {
            if (!$this->Fqa->find($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found',
                ])->setStatusCode(404);
            }

            if (!$this->Fqa->delete($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delete failed',
                ])->setStatusCode(500);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function getFqaDataMove()
    {
        $showFqa = $this->Fqa->getFqaMove();

        if ($showFqa) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data FAQ retrieved successfully',
                'result' => $showFqa,
            ]);
        }

        return $this->response->setJSON([
            'status' => false,
            'message' => 'Data not found',
        ])->setStatusCode(404);
    }

    public function updateFqaMove()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid data',
            ])->setStatusCode(400);
        }

        $this->Fqa->updateFqaOrder($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order updated',
        ]);
    }

    private function formatWebsiteFaqRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsiteFaqRow($row), $rows);
    }

    private function formatWebsiteFaqRow(array $row): array
    {
        return [
            'id' => $row['id'] ?? $row['int_saksiam_fqa_id'] ?? null,
            'faqtypeID' => $row['faqtypeID'] ?? $row['int_saksiam_fqa_type'] ?? null,
            'typeID' => $row['typeID'] ?? $row['int_saksiam_fqa_type'] ?? null,
            'faqtypeNameTH' => $row['faqtypeNameTH'] ?? $row['int_saksiam_typefqa_nameTH'] ?? null,
            'faqtypeNameEN' => $row['faqtypeNameEN'] ?? $row['int_saksiam_typefqa_nameEN'] ?? null,
            'typeNameTH' => $row['typeNameTH'] ?? $row['int_saksiam_typefqa_nameTH'] ?? null,
            'typeNameEN' => $row['typeNameEN'] ?? $row['int_saksiam_typefqa_nameEN'] ?? null,
            'questionTH' => $row['questionTH'] ?? $row['int_saksiam_fqa_questionTH'] ?? null,
            'questionEN' => $row['questionEN'] ?? $row['int_saksiam_fqa_questionEN'] ?? null,
            'answerTH' => $row['answerTH'] ?? $row['int_saksiam_fqa_answersTH'] ?? null,
            'answerEN' => $row['answerEN'] ?? $row['int_saksiam_fqa_answersEN'] ?? null,
            'answersTH' => $row['answersTH'] ?? $row['int_saksiam_fqa_answersTH'] ?? null,
            'answersEN' => $row['answersEN'] ?? $row['int_saksiam_fqa_answersEN'] ?? null,
            'active' => $row['active'] ?? $row['int_saksiam_fqa_active'] ?? null,
            'savename' => $row['savename'] ?? $row['int_saksiam_fqa_savename'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_fqa_createAt'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_fqa_updateAt'] ?? null,
            'fqaorder' => $row['fqaorder'] ?? $row['int_saksiam_fqa_order'] ?? null,
        ];
    }
}
