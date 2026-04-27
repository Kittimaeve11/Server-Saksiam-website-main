<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'int_saksiam_role';
    protected $primaryKey       = 'int_saksiam_role_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_role_id',
        'int_saksiam_role_name',
        'int_saksiam_role_createname',
        'int_saksiam_role_updateby',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_role_createat';
    protected $updatedField  = 'int_saksiam_role_updateat';
    protected $deletedField  = false;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['handleInsertTimestamps'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['handleUpdateTimestamps'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

     protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_role_createat'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_role_updateat'] = null;
        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        // แก้ไขชื่อตรงนี้ (updatedby)
        if (
            isset($data['data']['int_saksiam_role_updatedby'])
            || isset($data['data']['int_saksiam_role_id'])
        ) {

            $data['data']['int_saksiam_role_updateat'] = date('Y-m-d H:i:s');
        }

        unset($data['data']['int_saksiam_role_createat']);

        return $data;
    }

    public function getRoleData($offset, $limit)
    {
        $this->select('
        int_saksiam_role.int_saksiam_role_id,
        int_saksiam_role.int_saksiam_role_name,
        int_saksiam_role.int_saksiam_role_createname,
        int_saksiam_role.int_saksiam_role_createat,
        int_saksiam_role.int_saksiam_role_updateat
        ')
            ->orderBy(' int_saksiam_role.int_saksiam_role_id', 'DESC');
        $query = $this->findAll($limit, $offset);
        $countQuery = $this->select('COUNT( int_saksiam_role.int_saksiam_role_id) as total', false);
        $sizeCount = (int) $countQuery->get()->getRow()->total;
        $result = [];
        foreach ($query as $row) {
            if (!isset($result[$row['int_saksiam_role_id']])) {
                $result[$row['int_saksiam_role_id']] = [
                    'role_id' => $row['int_saksiam_role_id'],
                    'role_name' => $row['int_saksiam_role_name'],
                    'savename' => $row['int_saksiam_role_createname'],
                    'createAt' => $row['int_saksiam_role_createat'],
                    'updateAt' => $row['int_saksiam_role_updateat'],
                ];
            }
        }
        return [
            'counts' => $sizeCount,
            'roles' => array_values($result)
        ];
    }

    public function getroleShow()
    {
        $this->select('
        int_saksiam_role.int_saksiam_role_id As id,
        int_saksiam_role.int_saksiam_role_name As name,
        ')
            ->orderBy('int_saksiam_role_id', 'ASC');
        return $this->findAll();
    }



}
