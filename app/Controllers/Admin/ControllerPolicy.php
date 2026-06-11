<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\PolicyModel;

class ControllerPolicy extends Controller
{
    protected $Policy;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->Policy = new PolicyModel();
    }

    public function showPolicydatalist()
    {
        $showData = $this->Policy->showPolicy();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data policy retrieved successfully',
                'result' => $this->formatWebsitePolicyRows($showData),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showPolicyData()
    {
        $activeFilter = $this->request->getGet('active');
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

        $policyData = $this->Policy->getPolicyData($activeFilter, $offset, $limit);

        return $this->response->setJSON([
            'status' => 200,
            'data' => $policyData,
            'result' => $policyData['policies'],
            'counts' => $policyData['counts'],
        ]);
    }

    public function uploadPolicyAPI()
    {
        $jsonData = $this->request->getJSON(true) ?? $this->request->getPost();

        $nameTH = $jsonData['nameTH'] ?? null;
        $nameEN = $jsonData['nameEN'] ?? null;
        $detailTH = $jsonData['detailTH'] ?? null;
        $detailEN = $jsonData['detailEN'] ?? null;
        $createname = $jsonData['createname'] ?? $jsonData['savename'] ?? 'Unknown';
        $active = $jsonData['active'] ?? 1;

        if (!$nameTH || !$nameEN || !$detailTH || !$detailEN) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Policy name and detail are required',
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_policy_num' => $jsonData['num'] ?? null,
                'int_saksiam_policy_nameTH' => $nameTH,
                'int_saksiam_policy_nameEN' => $nameEN,
                'int_saksiam_policy_detailTH' => $detailTH,
                'int_saksiam_policy_detailEN' => $detailEN,
                'int_saksiam_policy_active' => $active,
                'int_saksiam_policy_createname' => $createname,
            ];

            $insertID = $this->Policy->createPolicy($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Policy created successfully',
                'id' => $insertID,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showPolicy($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Policy ID is required',
            ])->setStatusCode(400);
        }

        try {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $this->formatWebsitePolicyRow($this->Policy->showPolicy($id)),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updatePolicyDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Policy ID is required',
            ])->setStatusCode(400);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'No data received',
            ])->setStatusCode(400);
        }

        if (!$this->Policy->find($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found',
            ])->setStatusCode(404);
        }

        $updateData = [];

        $map = [
            'num' => 'int_saksiam_policy_num',
            'nameTH' => 'int_saksiam_policy_nameTH',
            'nameEN' => 'int_saksiam_policy_nameEN',
            'detailTH' => 'int_saksiam_policy_detailTH',
            'detailEN' => 'int_saksiam_policy_detailEN',
            'active' => 'int_saksiam_policy_active',
            'updatename' => 'int_saksiam_policy_updatename',
            'changename' => 'int_saksiam_policy_changename',
            'approvename' => 'int_saksiam_policy_approvedName',
            'approvedName' => 'int_saksiam_policy_approvedName',
            'approvedate' => 'int_saksiam_policy_approvedDate',
            'approvedDate' => 'int_saksiam_policy_approvedDate',
            'note' => 'int_saksiam_policy_note',
            'rejectReason' => 'int_saksiam_policy_note',
            'improvement' => 'int_saksiam_policy_improvement',
            'improvement_text' => 'int_saksiam_policy_improvement',
            'improvementText' => 'int_saksiam_policy_improvement',
            'cancellation' => 'int_saksiam_policy_cancellation',
            'order' => 'int_saksiam_policy_order',
        ];

        foreach ($map as $requestField => $dbField) {
            if (array_key_exists($requestField, $data)) {
                $updateData[$dbField] = $data[$requestField];
            }
        }

        try {
            if (!empty($updateData)) {
                $this->Policy->updatePolicy($id, $updateData);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $updateData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function deletePolicyData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Policy ID is required',
            ])->setStatusCode(400);
        }

        try {
            if (!$this->Policy->find($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found',
                ])->setStatusCode(404);
            }

            if (!$this->Policy->delete($id)) {
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
                'status' => false,
                'message' => 'An unexpected error occurred',
            ])->setStatusCode(500);
        }
    }

    public function updatePolicyMove()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid data',
            ])->setStatusCode(400);
        }

        $this->Policy->updatePolicyOrder($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order updated',
        ]);
    }

    private function formatWebsitePolicyRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsitePolicyRow($row), $rows);
    }

    private function formatWebsitePolicyRow(?array $row): ?array
    {
        if (!$row) {
            return $row;
        }

        return [
            'policyNum' => $row['policyNum'] ?? $row['int_saksiam_policy_num'] ?? null,
            'nameTH' => $row['nameTH'] ?? $row['int_saksiam_policy_nameTH'] ?? null,
            'nameEN' => $row['nameEN'] ?? $row['int_saksiam_policy_nameEN'] ?? null,
            'detailTH' => $row['detailTH'] ?? $row['int_saksiam_policy_detailTH'] ?? null,
            'detailEN' => $row['detailEN'] ?? $row['int_saksiam_policy_detailEN'] ?? null,
            'active' => $row['active'] ?? $row['int_saksiam_policy_active'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_policy_createAt'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_policy_updateAt'] ?? null,
            'approvedDate' => $row['approvedDate'] ?? $row['approvedate'] ?? $row['int_saksiam_policy_approvedDate'] ?? null,
            'approvedate' => $row['approvedate'] ?? $row['approvedDate'] ?? $row['int_saksiam_policy_approvedDate'] ?? null,
            'approvedName' => $row['approvedName'] ?? $row['approvename'] ?? $row['int_saksiam_policy_approvedName'] ?? null,
            'approvename' => $row['approvename'] ?? $row['approvedName'] ?? $row['int_saksiam_policy_approvedName'] ?? null,
            'note' => $row['note'] ?? $row['int_saksiam_policy_note'] ?? null,
            'rejectReason' => $row['rejectReason'] ?? $row['note'] ?? $row['int_saksiam_policy_note'] ?? null,
            'reason' => $row['reason'] ?? $row['note'] ?? $row['int_saksiam_policy_note'] ?? null,
            'improvement' => $row['improvement'] ?? $row['int_saksiam_policy_improvement'] ?? null,
            'improvement_text' => $row['improvement_text'] ?? $row['improvementText'] ?? $row['int_saksiam_policy_improvement'] ?? null,
            'improvementText' => $row['improvementText'] ?? $row['improvement_text'] ?? $row['int_saksiam_policy_improvement'] ?? null,
            'cancellation' => $row['cancellation'] ?? $row['int_saksiam_policy_cancellation'] ?? null,
            'policyorder' => $row['policyorder'] ?? $row['int_saksiam_policy_order'] ?? null,
        ];
    }
}
