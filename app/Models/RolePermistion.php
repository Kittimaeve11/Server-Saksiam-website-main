<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermistion extends Model
{
    protected $table            = 'int_saksiam_permistion_role';
    protected $primaryKey       = 'int_saksiam_permistion_role_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_permistion_role_id',
        'int_saksiam_permistion_role_roleID',
        'int_saksiam_permistion_role_createname',
        'int_saksiam_permistion_role_updateby',
        'int_saksiam_permistion_role_permistionID'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_permistion_role_createat';
    protected $updatedField  = 'int_saksiam_permistion_role_updateat';
    protected $deletedField  = false;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

     public function saveRolepermistion(int $roleId, array $permissionIds)
    {
        // ลบของเก่าออกก่อน
        $this->where('int_saksiam_permistion_role_roleID', $roleId)->delete();

        // Insert ใหม่
        foreach ($permissionIds as $pid) {
            $this->insert([
                'int_saksiam_permistion_role_roleID' => $roleId,
                'int_saksiam_permistion_role_permistionID' => intval($pid)
            ]);
        }
    }

    public function updateRolePermissions(int $roleId, array $permissionIds)
    {
        $this->where('int_saksiam_permistion_role_roleID', $roleId)->delete();

        foreach ($permissionIds as $pid) {
            $this->insert([
                'int_saksiam_permistion_role_roleID' => $roleId,
                'int_saksiam_permistion_role_permistionID' => intval($pid)
            ]);
        }
    }


    public function getPermissionSlugsByRole($roleID)
    {
        $query = $this->select('int_saksiam_permistion.int_saksiam_permistion_slug AS slug')
            ->join(
                'int_saksiam_permistion',
                'int_saksiam_permistion.int_saksiam_permistion_id = int_saksiam_permistion_role.int_saksiam_permistion_role_permistionID'
            )
            ->where('int_saksiam_permistion_role_roleID', $roleID)
            ->get()
            ->getResultArray();

        // คืนค่าเฉพาะ slug เป็น array เช่น ["Analytics", "role.create"]
        return array_column($query, 'slug');
    }



}
