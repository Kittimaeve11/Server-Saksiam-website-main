<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'int_saksiam_personnel';
    protected $primaryKey       = 'int_saksiam_personnel_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_personnel_id',
        'int_saksiam_personnel_num',
        'int_saksiam_personnel_pname',
        'int_saksiam_personnel_fname',
        'int_saksiam_personnel_lname',
        'int_saksiam_personnel_nickname',
        'int_saksiam_personnel_birthday',
        'int_saksiam_personnel_IDCard',
        'int_saksiam_personnel_address',
        'int_saksiam_personnel_district',
        'int_saksiam_personnel_amphoe',
        'int_saksiam_personnel_province',
        'int_saksiam_personnel_zipcode',
        'int_saksiam_personnel_password',
        'int_saksiam_personnel_phone',
        'int_saksiam_personnel_phone6',
        'int_saksiam_personnel_role',
        'int_saksiam_personnel_status',
        'int_saksiam_personnel_createby',
        'int_saksiam_personnel_email',
        'int_saksiam_personnel_updateby',
        'int_saksiam_personnel_dateregis',
        'int_saksiam_personnel_regisname',
        'int_saksiam_personnel_photo',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_personnel_createdate';
    protected $updatedField  = 'int_saksiam_personnel_updatedate';
    protected $deletedField  = 'int_saksiam_personnel_dateregis';

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
        $data['data']['int_saksiam_personnel_createdate'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_personnel_updatedate'] = null;
        $data['data']['int_saksiam_personnel_dateregis'] = null;
        return $data;
    }
    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_personnel_updateby']) || isset($data['data']['int_saksiam_personnel_id'])) {
            $data['data']['int_saksiam_personnel_updatedate'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_personnel_dateregis']);
        }
        if (isset($data['data']['int_saksiam_personnel_regisname'])) {
            $data['data']['int_saksiam_personnel_dateregis'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_personnel_updatedate']);
        }
        unset($data['data']['int_saksiam_personnel_createdate']);

        return $data;
    }

     public function createpersonnelData(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data is required for insertion.');
        }

        // ป้องกันชื่อซ้ำอีกชั้น
        $exists = $this->where('int_saksiam_personnel_num', $data['int_saksiam_personnel_num'])->first();
        if ($exists) {
            throw new \RuntimeException('Personnel number already exists. Please try again.');
        }

        $data = $this->handleInsertTimestamps(['data' => $data])['data'];

        if (!$this->insert($data, true)) {
            throw new \RuntimeException('Failed to insert inquiries data.');
        }

        return $this->getInsertID();
    }

    public function generatePersonnelNumber()
    {
        $prefix = 'SAK';
        $try = 0;

        do {
            // หาค่า PSN ล่าสุด
            $latest = $this->like('int_saksiam_personnel_num', $prefix, 'after')
                ->orderBy('int_saksiam_personnel_num', 'DESC')
                ->first();

            if ($latest) {
                // ตัดเลข 6 หลักท้ายออกมา
                $lastNumber = (int) substr($latest['int_saksiam_personnel_num'], -6);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // ประกอบเลขใหม่แบบ 6 หลัก
            $newPersonnelNum = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

            // ตรวจสอบว่ามีเลขนี้แล้วหรือยัง
            $exists = $this->where('int_saksiam_personnel_num', $newPersonnelNum)->first();
            $try++;

            if ($try > 5) {
                throw new \RuntimeException('ไม่สามารถสร้างหมายเลขผู้ใช้งานได้ กรุณาลองใหม่');
            }

        } while ($exists);

        return $newPersonnelNum;
    }

    public function getPersonalData($roleId, $activeFilter = null, $offset, $limit)
    {
        $this->select('
            int_saksiam_personnel.int_saksiam_personnel_id,
            int_saksiam_personnel.int_saksiam_personnel_num,
            int_saksiam_personnel.int_saksiam_personnel_pname,
            int_saksiam_personnel.int_saksiam_personnel_fname,
            int_saksiam_personnel.int_saksiam_personnel_lname,
            int_saksiam_personnel.int_saksiam_personnel_nickname,
            int_saksiam_personnel.int_saksiam_personnel_email,
            int_saksiam_personnel.int_saksiam_personnel_phone6,
            int_saksiam_personnel.int_saksiam_personnel_status,
            int_saksiam_personnel.int_saksiam_personnel_createby,
            int_saksiam_personnel.int_saksiam_personnel_createdate,
            int_saksiam_personnel.int_saksiam_personnel_updatedate,
            int_saksiam_role.int_saksiam_role_id,
            int_saksiam_role.int_saksiam_role_name,
        ')
            ->join('int_saksiam_role', 'int_saksiam_personnel.int_saksiam_personnel_role = int_saksiam_role.int_saksiam_role_id', 'left')
            ->orderBy('int_saksiam_personnel.int_saksiam_personnel_id', 'DESC');

        if ($roleId) {
            $this->where('int_saksiam_personnel.int_saksiam_personnel_role', $roleId);
        }

        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $this->whereIn('int_saksiam_personnel.int_saksiam_personnel_status', $activeFilter);
            } else {
                $this->where('int_saksiam_personnel.int_saksiam_personnel_status', $activeFilter);
            }
        }
        $query = $this->findAll($limit, $offset);
        $countQuery = $this->select('COUNT(int_saksiam_personnel.int_saksiam_personnel_id) as total', false);

        if ($roleId) {
            $countQuery->where('int_saksiam_personnel.int_saksiam_personnel_role', $roleId); // Update column name if necessary
        }

        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $countQuery->whereIn('int_saksiam_personnel.int_saksiam_personnel_status', $activeFilter);
            } else {
                $countQuery->where('int_saksiam_personnel.int_saksiam_personnel_status', $activeFilter);
            }
        }
        $sizeCount = (int) $countQuery->get()->getRow()->total;
        $result = [];

        foreach ($query as $row) {
            if (!isset($result[$row['int_saksiam_personnel_id']])) {
                $result[$row['int_saksiam_personnel_id']] = [
                    'user_id' => $row['int_saksiam_personnel_id'],
                    'usernum' => $row['int_saksiam_personnel_num'],
                    'pname' => $row['int_saksiam_personnel_pname'],
                    'fname' => $row['int_saksiam_personnel_fname'],
                    'lname' => $row['int_saksiam_personnel_lname'],
                    'nickname' => $row['int_saksiam_personnel_nickname'],
                    'email' => $row['int_saksiam_personnel_email'],
                    'phone6' => $row['int_saksiam_personnel_phone6'],
                    'status' => $row['int_saksiam_personnel_status'],
                    'savename' => $row['int_saksiam_personnel_createby'],
                    'createAt' => $row['int_saksiam_personnel_createdate'],
                    'updateAt' => $row['int_saksiam_personnel_updatedate'],
                    'role_id' => $row['int_saksiam_role_id'],
                    'role_name' => $row['int_saksiam_role_name'],
                ];
            }
        }
        return [
            'counts' => $sizeCount,
            'users' => array_values($result)
        ];
    }

    public function showUserID($id = null)
    {
        $this->select('
        int_saksiam_personnel.int_saksiam_personnel_id As personnel_ID,
        int_saksiam_personnel.int_saksiam_personnel_num As personnel_num,
        int_saksiam_personnel.int_saksiam_personnel_pname As personnel_pname,
        int_saksiam_personnel.int_saksiam_personnel_fname As personnel_fname,
        int_saksiam_personnel.int_saksiam_personnel_lname As personnel_lname,
        int_saksiam_personnel.int_saksiam_personnel_nickname As nickname,
        int_saksiam_personnel.int_saksiam_personnel_birthday As birthday,
        int_saksiam_personnel.int_saksiam_personnel_IDCard As IDCard,
        int_saksiam_personnel.int_saksiam_personnel_address As address,
        int_saksiam_personnel.int_saksiam_personnel_district As district,
        int_saksiam_personnel.int_saksiam_personnel_amphoe As amphoe,
        int_saksiam_personnel.int_saksiam_personnel_province As province,
        int_saksiam_personnel.int_saksiam_personnel_zipcode As zipcod,
        int_saksiam_personnel.int_saksiam_personnel_phone As phone,
        int_saksiam_personnel.int_saksiam_personnel_phone6 As phone6,
        int_saksiam_personnel.int_saksiam_personnel_status As status,
        int_saksiam_personnel.int_saksiam_personnel_email As email,
        int_saksiam_personnel.int_saksiam_personnel_regisname As regisname,
        int_saksiam_personnel.int_saksiam_personnel_photo As photo,
        int_saksiam_personnel.int_saksiam_personnel_createby As createby,
        int_saksiam_personnel.int_saksiam_personnel_updateby As updateby,
        int_saksiam_personnel.int_saksiam_personnel_createdate As createdate,
        int_saksiam_personnel.int_saksiam_personnel_updatedate As updatedate,
        int_saksiam_role.int_saksiam_role_id As role_id,
        int_saksiam_role.int_saksiam_role_name As role_name,

    ');
        $this->join('int_saksiam_role', 'int_saksiam_personnel.int_saksiam_personnel_role = int_saksiam_role.int_saksiam_role_id', 'left');
        if ($id !== null) {
            $query = $this->find($id);

        } else {
            $query = $this->orderBy('int_saksiam_personnel_id', 'ASC')->findAll();

        }
        return $query;
    }

    public function updateuserData($id, $data)
    {
        $existingData = $this->find($id);
        if (!$existingData) {
            return false; // No data to update
        }

        // If the ID is correct
        if (!empty($id)) {
            // Check if detail, active, namelist, or updatename are modified and save the new values; if not provided, use the existing values from the database
            // Set the data to be updated

            $this->set($data);

            // Update condition specified by ID
            $this->where('int_saksiam_personnel_id', $id);

            // Perform the data update
            if ($this->update()) {
                return true; // Update successful
            } else {
                // Check if there are any errors
                return false;
            }
        } else {
            return false; // Case where ID is not provided
        }
    }

}