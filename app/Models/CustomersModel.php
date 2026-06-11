<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomersModel extends Model
{
    protected $table            = 'int_saksiam_customers';
    protected $primaryKey       = 'int_saksiam_customers_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_customers_id',
        'int_saksiam_customers_fullname',
        'int_saksiam_customers_tell',
        'int_saksiam_customers_tell2',
        'int_saksiam_customers_incom',
        'int_saksiam_customers_email',
        'int_saksiam_customers_IDcard',
        'int_saksiam_customers_subdistrictid',
        'int_saksiam_customers_subdistrictname',
        'int_saksiam_customers_districtname',
        'int_saksiam_customers_provincename',
        'int_saksiam_customers_code',
        'int_saksiam_customers_IPAddress',
        'int_saksiam_customers_device',
        'int_saksiam_customers_savename',
        'int_saksiam_customers_updatename'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_customers_createAT';
    protected $updatedField  = 'int_saksiam_customers_updateAT';
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
    
        public function createapplicationdata($data)
    {
        // Ensure that the $data variable is set before use
        if (empty($data)) {
            throw new \Exception('Data is required for insertion');
        }
        $data['created_at'] = date('Y-m-d H:i:s'); // Use the correct column names
        $this->insert($data);
        return $this->getInsertID();
    }
}
