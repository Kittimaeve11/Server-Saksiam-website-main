<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use App\Models\RoleModel;
use App\Models\RolePermistion;
use App\Models\PermissionsModel;


class RoleController extends BaseController

{

    protected $Role;
    protected $Rolepermistion;
    protected $Permistion;
    
   public function __construct()
    {
        $this->role = new RoleModel();
        $this->rolePermission = new RolePermistion();
        $this->permission = new PermissionsModel();

    }

public function createdataRoleAPI()
{
    $name = trim($this->request->getVar('name'));
    $permIds = (array) ($this->request->getVar('permission_ids') ?? []);

    if (!$name) {
        return $this->failValidationErrors("กรุณากรอกชื่อ Role");
    }

    // 🔥 เช็คชื่อซ้ำ
    $exists = $this->Role
        ->where('int_saksiam_role_name', $name)
        ->first();

    if ($exists) {
        return $this->failValidationErrors("พบชื่อ Role นี้ในระบบแล้ว");
    }

    // Insert Role
    $roleId = $this->Role->insert([
        'int_saksiam_role_name' => $name,
        'int_saksiam_role_createname' => 'Administrator System',
        'int_saksiam_role_createat' => date('Y-m-d H:i:s')
    ]);

    if (!$roleId) {
        return $this->fail("ไม่สามารถสร้าง Role ได้");
    }

    $this->Rolepermistion->saveRolepermistion($roleId, $permIds);

    return $this->respondCreated([
        "message" => "สร้าง Role สำเร็จ",
        "role_id" => $roleId,
        "permissions" => $permIds
    ]);
}

    public function showRoleData()
    {
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        $roleDAta = $this->role->getRoleData($offset, $limit);
        if ($roleDAta) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $roleDAta,
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

    public function uploandRoleApi()
    {
        // รับ JSON แบบ array
        $json = $this->request->getJSON(true);

        if (!$json) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Invalid JSON format'
            ])->setStatusCode(400);
        }

        $name = $json['name'] ?? null;
        $createdBy = $json['created_by'] ?? null;
        $permIds = $json['permission_ids'] ?? [];

        if (!$name) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Role name is required'
            ])->setStatusCode(400);
        }

        if (empty($permIds)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Permission list cannot be empty'
            ])->setStatusCode(400);
        }

        // Insert Role
        $id = $this->role->insert([
            'int_saksiam_role_name' => $name,
            'int_saksiam_role_createname' => $createdBy, // เพิ่มเข้าไปที่นี่
        ]);

        // Insert Permissions
        $this->rolePermission->saveRolepermistion($id, $permIds);

        return $this->response->setJSON([
            'status' => 201,
            'message' => 'Role created successfully',
            'id' => $id 
        ])->setStatusCode(201);
    }

    public function updateRoleApi($id = null)
    {
        if (is_null($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Role ID is required'
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

            $roleData = $this->role->find($id);

            if (!$roleData) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Role not found'
                ])->setStatusCode(404);
            }

            $updateData = [];

            // ⭐ เปรียบเทียบแบบ null-safe
            if (isset($data['name']) && $data['name'] !== ($roleData['int_saksiam_role_name'] ?? null)) {
                $updateData['int_saksiam_role_name'] = $data['name'];
            }


            $updateData['int_saksiam_role_updateby'] = $data['updated_by'];


            // ⭐ อัปเดตเฉพาะ field ที่เปลี่ยน
            if (!empty($updateData)) {
                $this->role->update($id, $updateData);
            }

            // อัปเดต permission กลุ่มนี้แยกจากกัน
            if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
                $this->rolePermission->updateRolePermissions($id, $data['permission_ids']);
            }
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Role updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function showPermissionsAPI()
    {
        try {
            // ใช้ตัวแปร permission ที่ประกาศไว้ใน constructor
            $grouped = $this->permission->PermissionsGrouped();

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'success',
                'data' => $grouped
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 500,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function showRoleID($roleID = null)
    {
        if (is_null($roleID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Role ID is required'
            ])->setStatusCode(400);
        }

        try {
            $role = $this->role->find($roleID);

            if (!$role) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Role not found'
                ])->setStatusCode(404);
            }

            $permissions = $this->rolePermission
                ->select("
                int_saksiam_permistion.int_saksiam_permistion_id AS permistion_id,
                int_saksiam_permistion.int_saksiam_permistion_name AS permistion_name,
                int_saksiam_permistion.int_saksiam_permistion_slug AS permistion_slug,
                int_saksiam_permistion.int_saksiam_permistion_groupby AS permistion_groupby
            ")
                ->join(
                    'int_saksiam_permistion',
                    'int_saksiam_permistion.int_saksiam_permistion_id = int_saksiam_permistion_role.int_saksiam_permistion_role_permistionID'
                )
                ->where('int_saksiam_permistion_role_roleID', $roleID)
                ->findAll();

            return $this->response->setJSON([
                'status' => 200,
                'data' => [
                    'role_id' => $roleID,
                    'role_name' => $role['int_saksiam_role_name'],
                    'permission_ids' => $permissions
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function showRoledatalist()
    {
        $showData = $this->role->getroleShow(); // ✔ เปลี่ยน Role → role

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data Product brand retrieved successfully',
                'result' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

}