<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table            = 'int_saksiam_loan';
    protected $primaryKey       = 'int_saksiam_loan_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_loan_id',
        'int_saksiam_loan_titleTH',
        'int_saksiam_loan_titleEN',
        'int_saksiam_loan_detail',
        'int_saksiam_loan_imagelarge',
        'int_saksiam_loan_small',
        'int_saksiam_loan_highlight',
        'int_saksiam_loan_qualifications',
        'int_saksiam_loan_documens',
        'int_saksiam_loan_vehicleType',
        'int_saksiam_loan_dose',
        'int_saksiam_loan_minamount',
        'int_saksiam_loan_maxamount',
        'int_saksiam_loan_active',
        'int_saksiam_loan_isopen',
        'int_saksiam_loan_savename',
        'int_saksiam_loan_updatename',
        'int_saksiam_loan_approvedate',
        'int_saksiam_loan_approvename',
        'int_saksiam_loan_changetime',
        'int_saksiam_loan_changename',
        'int_saksiam_loan_order',
        'int_saksiam_loan_note',
        'int_saksiam_loan_improvement',
        'int_saksiam_loan_cancellation'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_loan_createAt';
    protected $updatedField  = 'int_saksiam_loan_updateAt';
    protected $deletedField  = '';

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
        $data['data']['int_saksiam_loan_createAt'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_loan_updateAt'] = null;
        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasActiveChange = array_key_exists('int_saksiam_loan_active', $data['data']);

        $hasNameChange =
            array_key_exists('int_saksiam_loan_titleTH', $data['data']) ||
            array_key_exists('int_saksiam_loan_titleEN', $data['data']) ||
            array_key_exists('int_saksiam_loan_imagelarge', $data['data'])||
            array_key_exists('int_saksiam_loan_small', $data['data'])||
            array_key_exists('int_saksiam_loan_highlight', $data['data']);
            array_key_exists('int_saksiam_loan_qualifications', $data['data']);
            array_key_exists('int_saksiam_loan_documens', $data['data']);
            array_key_exists('int_saksiam_loan_vehicleType', $data['data']);
            array_key_exists('int_saksiam_loan_dose', $data['data']);
            array_key_exists('int_saksiam_loan_minamount', $data['data']);
            array_key_exists('int_saksiam_loan_maxamount', $data['data']);

        $hasUpdateName =
            !empty($data['data']['int_saksiam_loan_updatename'] ?? null);

        // 🔥 1. เปลี่ยน active → changetime
        if ($hasActiveChange) {
            $data['data']['int_saksiam_loan_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_loan_createAt']);
        }

        // 🔥 2. แก้ชื่อ → updateAt
        elseif ($hasNameChange) {
            $data['data']['int_saksiam_loan_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_loan_changetime']);
        }

        // 🔥 3. metadata → updateAt
        elseif ($hasUpdateName) {
            $data['data']['int_saksiam_loan_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_loan_changetime']);
        }

        unset($data['data']['int_saksiam_loan_createAt']);

        return $data;
    }

    public function getloan($offset = 0, $limit = 10, $activeFilter = null) 
    {
        $this -> select('
        int_saksiam_loan_id,
        int_saksiam_loan_titleTH,
        int_saksiam_loan_titleEN,
        int_saksiam_loan_imagelarge,
        int_saksiam_loan_small,
        int_saksiam_loan_highlight,
        int_saksiam_loan_qualifications,
        int_saksiam_loan_documens,
        int_saksiam_loan_vehicleType,
        int_saksiam_loan_dose,
        int_saksiam_loan_minamount,
        int_saksiam_loan_maxamount,
        int_saksiam_loan_active,
        int_saksiam_loan_isopen,
        int_saksiam_loan_savename,
        int_saksiam_loan_createAt,
        int_saksiam_loan_updateAt,
        int_saksiam_loan_approvedate,
        int_saksiam_loan_approvename,
        ')
         ->orderBy('int_saksiam_loan_id', 'DESC');

             // filter active
        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $this->whereIn('int_saksiam_loan_active', $activeFilter);
            } else {
                $this->where('int_saksiam_loan_active', $activeFilter);
            }
        }
        $query = $this->findAll($limit, $offset);

        $countQuery = $this->select('COUNT(int_saksiam_loan_id) as total', false);
        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $countQuery->whereIn('int_saksiam_loan_active', $activeFilter);
            } else {
                $countQuery->where('int_saksiam_loan_active', $activeFilter);
            }
        }

        $sizeCount = (int) $countQuery->get()->getRow()->total;
        $result = [];

        foreach ($query as $row) {
            if (!isset($result[$row['int_saksiam_loan_id']])) {
                 $result[$row['int_saksiam_loan_id']] =[
                    'id' => $row['int_saksiam_loan_id'],
                    'nameTH' => $row['int_saksiam_loan_titleTH'],
                    'nameEN' => $row['int_saksiam_loan_titleEN'],
                    'imagelarge' => $row['int_saksiam_loan_imagelarge'],
                    'imagesmall' => $row['int_saksiam_loan_small'],
                    'highlight' => $row['int_saksiam_loan_highlight'],
                    'qualifications' => $row['int_saksiam_loan_qualifications'],
                    'documens' => $row['int_saksiam_loan_documens'],
                    'vehicleType' => $row['int_saksiam_loan_vehicleType'],
                    'dose' => $row['int_saksiam_loan_dose'],
                    'minamount' => $row['int_saksiam_loan_minamount'],
                    'maxamount' => $row['int_saksiam_loan_maxamount'],
                    'active' => $row['int_saksiam_loan_active'],
                    'isopen' => $row['int_saksiam_loan_isopen'],
                    'savename' => $row['int_saksiam_loan_savename'],
                    'createAt' => $row['int_saksiam_loan_createAt'],
                    'updateAt' => $row['int_saksiam_loan_updateAt'],
                    'approvedate' => $row['int_saksiam_loan_approvedate'],
                    'approvename' => $row['int_saksiam_loan_approvename'],
                 ];
            }
        }
        return [
        'loanscount' => $sizeCount,
        'loan' => array_values($result),
        ];
    }

    public function createLoanData($data)
    {
         if (empty($data)) {
                throw new \Exception('Data is required for insertion');
            }
            $data['int_saksiam_loan_createAt'] = date('Y-m-d H:i:s'); // Use the correct column names
            $this->insert($data);
            return $this->getInsertID();
    }

    public function updateData($loanID, $data)
    {
          $existingData = $this->find($loanID);
            if (!$existingData) {
                return false; // No data to update
            }
            if (!empty($loanID)) {
                $this->set($data);
                $this->where('int_saksiam_loan_id', $loanID);
                 if ($this->update()) {
                    return true; // Update successful
                 } else {
                    return false;
                 }
            } else {
                return false; 
            }
    }

  public function showLoanID($id = null)
{
    $this->select('
        int_saksiam_loan_id As id,
        int_saksiam_loan_titleTH As nameTH,
        int_saksiam_loan_titleEN As nameEN,
        int_saksiam_loan_detail As detail,
        int_saksiam_loan_imagelarge As imagelarge,
        int_saksiam_loan_small As imagesmall,
        int_saksiam_loan_highlight As highlight,
        int_saksiam_loan_qualifications As qualifications,
        int_saksiam_loan_documens As documens,
        int_saksiam_loan_vehicleType As vehicleType,
        int_saksiam_loan_dose As dose,
        int_saksiam_loan_minamount As minamount,
        int_saksiam_loan_maxamount As maxamount,
        int_saksiam_loan_active As active,
        int_saksiam_loan_isopen As isopen,
        int_saksiam_loan_savename As savename,
        int_saksiam_loan_createAt As createAt,
        int_saksiam_loan_updateAt As updateAt,
        int_saksiam_loan_updatename As updatename,
        int_saksiam_loan_approvedate As approvedate,
        int_saksiam_loan_approvename As approvename,
        int_saksiam_loan_note As note,
        int_saksiam_loan_improvement As improvement,
        int_saksiam_loan_cancellation As cancellation
    ');

    if ($id !== null) {
        $query = $this->find($id);
    } else {
        $query = $this->orderBy('int_saksiam_loan_id', 'ASC')->findAll();
    }

    return $query;
}
    public function getLoanMoveData() 
    {
        return $this->select('
         int_saksiam_loan_id As id ,
        int_saksiam_loan_titleTH As nameTH,
        int_saksiam_loan_imagelarge As imagelarge,
        int_saksiam_loan_minamount As minamount,
        int_saksiam_loan_maxamount As maxamount,
        int_saksiam_loan_active As active,
        int_saksiam_loan_savename As savename,
        int_saksiam_loan_createAt As createAt,
        int_saksiam_loan_updateAt As updateAt
        ')

        ->whereIn('int_saksiam_loan_active', [0, 1])
        ->orderBy('int_saksiam_loan_order', 'ASC')
        ->orderBy('int_saksiam_loan_id', 'ASC')
        ->findAll();
    }

    public function updateLoanMove($orderData)
    {
        if (empty($orderData)) {
                return false;
            }
            $allSuccess = true;
            foreach ($orderData as $item) {
                 if (!isset($item['int_saksiam_loan_id'], $item['int_saksiam_loan_order'])) {
                continue;
            }

            $existingData = $this->find($item['int_saksiam_loan_id']);
                if (!$existingData) {
                    $allSuccess = false;
                continue;
            }

            $updateResult = $this->update($item['int_saksiam_loan_id'], ['int_saksiam_loan_order' => $item['int_saksiam_loan_order']]);
                if (!$updateResult) {
                    $allSuccess = false;
                }
            }
           return $allSuccess;
    }

        // <--end Manager sak -->

    // <--Start Web sak -->   
    public function listshowheader()
    {
        $query = $this->select('
          int_saksiam_loan_id As id,
        int_saksiam_loan_titleTH As nameTH,
        int_saksiam_loan_titleEN As nameEN,
        ')  
        ->where('int_saksiam_loan_active', 1)
        ->orderBy('int_saksiam_loan_order', 'ASC')
        ->findAll();
          return $query;
    }
    public function listFormApp()
    {
        $query = $this->select('
          int_saksiam_loan_id As id,
          int_saksiam_loan_titleTH As name,
          int_saksiam_loan_titleEN As nameEN,
          int_saksiam_loan_vehicleType As vehicleType,
          int_saksiam_loan_minamount As minamount,
          int_saksiam_loan_maxamount As maxamount,
        ')  
        ->where('int_saksiam_loan_active', 1)
        ->where('int_saksiam_loan_isopen', 1)
        ->orderBy('int_saksiam_loan_order', 'ASC')
        ->findAll();
          return $query;
    }

    public function listsdiagnosis()
    {
        $query = $this->select('
        int_saksiam_loan_id As id,
        int_saksiam_loan_titleTH As nameTH,
        int_saksiam_loan_titleEN As nameEN,
        int_saksiam_loan_detail As detail,
        int_saksiam_loan_imagelarge As imagelarge,
        int_saksiam_loan_vehicleType As vehicleType,
        int_saksiam_loan_isopen As isopen,
        int_saksiam_loan_minamount As minamount,
        int_saksiam_loan_maxamount As maxamount,
        ')  
        ->where('int_saksiam_loan_active', 1)
        ->orderBy('int_saksiam_loan_order', 'ASC')
        ->findAll();
          return $query;
    }


   public function showLoanpageID($slug = null)
    {
        $this->select('
            int_saksiam_loan_id As id,
            int_saksiam_loan_titleTH As nameTH,
            int_saksiam_loan_titleEN As nameEN,
            int_saksiam_loan_imagelarge As imagelarge,
            int_saksiam_loan_small As imagesmall,
            int_saksiam_loan_highlight As highlight,
            int_saksiam_loan_qualifications As qualifications,
            int_saksiam_loan_isopen As isopen,
            int_saksiam_loan_documens As documens,
            int_saksiam_loan_vehicleType As vehicleType,
            int_saksiam_loan_dose As dose,
            int_saksiam_loan_minamount As minamount,
            int_saksiam_loan_maxamount As maxamount,
        ');

        if ($slug !== null) {
            $query = $this->where('int_saksiam_loan_titleEN', $slug)
                        ->first();
        } else {
            $query = $this->orderBy('int_saksiam_loan_id', 'ASC')
                        ->findAll();
        }

        return $query;
    }

    // <--end Web sak -->


}
