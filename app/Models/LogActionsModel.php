<?php

namespace App\Models;

use CodeIgniter\Model;

class LogActionsModel extends Model
{
    protected $table            = 'int_saksiam_log';
    protected $primaryKey       = 'int_saksiam_log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_log_id',
        'int_saksiam_log_ActionType',
        'int_saksiam_log_ActionDetail',
        'int_saksiam_log_TypeUser',
        'int_saksiam_log_IPAddress',
        'int_saksiam_log_datatype',
        'int_saksiam_log_Device',
        'int_saksiam_log_dataID',
        'int_saksiam_log_datatypeID',
        'int_saksiam_log_dataname',
        'int_saksiam_log_FullNamePer',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_log_DatetimeActions';
    protected $updatedField  = '';
    protected $deletedField  = '';

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

       public function insert_data($data)
    {
        $this->save($data);
        return $this->getInsertID();
    }

}
