<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionsModel extends Model
{
    protected $table            = 'int_saksiam_permistion';
    protected $primaryKey       = 'int_saksiam_permistion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_permistion_id',
        'int_saksiam_permistion_name',
        'int_saksiam_permistion_slug',
        'int_saksiam_permistion_groupby',
        'int_saksiam_permistion_createby',
        'int_saksiam_permistion_updatename',
        
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_permistion_createat';
    protected $updatedField  = 'int_saksiam_permistion_updateat';
    protected $deletedField  =  false;

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
        $data['data']['int_saksiam_permistion_createat'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_permistion_updateat'] = null;
        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_permistion_updatename']) || isset($data['data']['int_saksiam_editoria_id'])) {
            $data['data']['int_saksiam_permistion_updateat'] = date('Y-m-d H:i:s');
        }
        unset($data['data']['int_saksiam_permistion_createdat']);

        return $data;
    }


   public function PermissionsGrouped()
{
    $result = [];
    $rows = $this->orderBy('int_saksiam_permistion_groupby')->findAll();

    foreach ($rows as $row) {
        $group = $row['int_saksiam_permistion_groupby'];

        if (!isset($result[$group])) {
            $result[$group] = [];
        }

        // ✅ map field ใหม่
        $result[$group][] = [
            'permistion_id'   => $row['int_saksiam_permistion_id'],
            'permistion_name' => $row['int_saksiam_permistion_name'],
            'permistion_slug' => $row['int_saksiam_permistion_slug'] ?? null,
            'permistion_groupby' => $group,
        ];
    }

    return $result;
}
}
