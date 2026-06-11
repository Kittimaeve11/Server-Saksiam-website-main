<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\EditorialTypeModel;

class ControllerEditorialType extends Controller
{
    protected $EditorialType;

    public function __construct()
    {
        helper('setaccesstoken');
        $this->EditorialType = new EditorialTypeModel();
    }

    public function showEditorialTypedatalist()
    {
        $showData = $this->EditorialType->showEditorialType();

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data editorial type retrieved successfully',
                'result' => $this->formatWebsiteEditorialTypeRows($showData),
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function showEditorialTypeData()
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

        $editorialTypeData = $this->EditorialType->getEditorialTypeData($activeFilter, $offset, $limit);

        if ($editorialTypeData) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $editorialTypeData,
                'result' => $editorialTypeData['editorialtypes'],
                'counts' => $editorialTypeData['counts'],
            ]);
        }

        return $this->response->setJSON(0);
    }

    public function uploadEditorialTypeAPI()
    {
        $jsonData = $this->request->getJSON(true);

        $nameTH = $jsonData['nameTH'] ?? null;
        $nameEN = $jsonData['nameEN'] ?? null;
        $savename = $jsonData['savename'] ?? 'Unknown';
        $active = $jsonData['active'] ?? 1;

        if (!$nameTH || !$nameEN) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Name TH and name EN are required',
            ])->setStatusCode(400);
        }

        $duplicate = $this->EditorialType
            ->groupStart()
                ->where('int_saksiam_Typeeditoria_nameTH', $nameTH)
                ->orWhere('int_saksiam_Typeeditoria_nameEN', $nameEN)
            ->groupEnd()
            ->first();

        if ($duplicate) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial type already exists',
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'int_saksiam_Typeeditoria_nameTH' => $nameTH,
                'int_saksiam_Typeeditoria_nameEN' => $nameEN,
                'int_saksiam_Typeeditoria_savename' => $savename,
                'int_saksiam_Typeeditoria_active' => $active,
            ];

            $insertID = $this->EditorialType->createEditorialType($data);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Editorial type created successfully',
                'id' => $insertID,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showEditorialType($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial type ID is required',
            ])->setStatusCode(400);
        }

        try {
            $data = $this->EditorialType->showEditorialType($id);

            return $this->response->setJSON([
                'status' => 200,
                'data' => $this->formatWebsiteEditorialTypeRow($data),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function updateEditorialTypeDetail($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial type ID is required',
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

            $existing = $this->EditorialType->find($id);

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found',
                ])->setStatusCode(404);
            }

            if (isset($data['nameTH']) || isset($data['nameEN'])) {
                $checkNameTH = $data['nameTH'] ?? $existing['int_saksiam_Typeeditoria_nameTH'];
                $checkNameEN = $data['nameEN'] ?? $existing['int_saksiam_Typeeditoria_nameEN'];

                $duplicate = $this->EditorialType
                    ->groupStart()
                        ->where('int_saksiam_Typeeditoria_nameTH', $checkNameTH)
                        ->orWhere('int_saksiam_Typeeditoria_nameEN', $checkNameEN)
                    ->groupEnd()
                    ->where('int_saksiam_Typeeditorial_id !=', $id)
                    ->first();

                if ($duplicate) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Editorial type already exists',
                    ])->setStatusCode(400);
                }
            }

            $updateData = [];

            if (isset($data['nameTH'])) {
                $updateData['int_saksiam_Typeeditoria_nameTH'] = $data['nameTH'];
            }

            if (isset($data['nameEN'])) {
                $updateData['int_saksiam_Typeeditoria_nameEN'] = $data['nameEN'];
            }

            if (isset($data['active'])) {
                $updateData['int_saksiam_Typeeditoria_active'] = $data['active'];
            }

            if (isset($data['updatename'])) {
                $updateData['int_saksiam_Typeeditoria_updatename'] = $data['updatename'];
            }

            if (isset($data['changename'])) {
                $updateData['int_saksiam_Typeeditoria_changename'] = $data['changename'];
            }

            if (!empty($updateData)) {
                $this->EditorialType->updateEditorialType($id, $updateData);
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

    public function deleteEditorialTypeData($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Editorial type ID is required',
            ])->setStatusCode(400);
        }

        try {
            if (!$this->EditorialType->find($id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Data not found',
                ])->setStatusCode(404);
            }

            if (!$this->EditorialType->delete($id)) {
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

    public function updateEditorialTypeMove()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid data',
            ])->setStatusCode(400);
        }

        $this->EditorialType->updateEditorialTypeOrder($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Order updated',
        ]);
    }

    private function formatWebsiteEditorialTypeRows(array $rows): array
    {
        return array_map(fn ($row) => $this->formatWebsiteEditorialTypeRow($row), $rows);
    }

    private function formatWebsiteEditorialTypeRow(?array $row): ?array
    {
        if (!$row) {
            return $row;
        }

        return [
            'id' => $row['id'] ?? $row['int_saksiam_Typeeditorial_id'] ?? null,
            'editorialtypeID' => $row['editorialtypeID'] ?? $row['int_saksiam_Typeeditorial_id'] ?? null,
            'typeeditoriaID' => $row['typeeditoriaID'] ?? $row['int_saksiam_Typeeditorial_id'] ?? null,
            'nameTH' => $row['nameTH'] ?? $row['int_saksiam_Typeeditoria_nameTH'] ?? null,
            'nameEN' => $row['nameEN'] ?? $row['int_saksiam_Typeeditoria_nameEN'] ?? null,
            'editorialtypenameTH' => $row['editorialtypenameTH'] ?? $row['int_saksiam_Typeeditoria_nameTH'] ?? null,
            'editorialtypenameEN' => $row['editorialtypenameEN'] ?? $row['int_saksiam_Typeeditoria_nameEN'] ?? null,
            'active' => $row['active'] ?? $row['int_saksiam_Typeeditoria_active'] ?? null,
            'savename' => $row['savename'] ?? $row['int_saksiam_Typeeditoria_savename'] ?? null,
            'createAt' => $row['createAt'] ?? $row['int_saksiam_Typeeditoria_createAt'] ?? null,
            'updateAt' => $row['updateAt'] ?? $row['int_saksiam_Typeeditoria_updateAt'] ?? null,
            'editorialtypeorder' => $row['editorialtypeorder'] ?? $row['int_saksiam_Typeeditoria_order'] ?? null,
            'typeeditoriaorder' => $row['typeeditoriaorder'] ?? $row['int_saksiam_Typeeditoria_order'] ?? null,
        ];
    }
}
