<?php

namespace App\Models;

use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table            = 'int_saksiam_banner';
    protected $primaryKey       = 'int_saksiam_banner_ID ';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
     'int_saksiam_banner_ID',
     'int_saksiam_banner_name',
     'int_saksiam_banner_picturePC',
     'int_saksiam_banner_pictureMoblie',
     'int_saksiam_banner_type',
     'int_saksiam_banner_link',
     'int_saksiam_banner_active',
     'int_saksiam_banner_savename',
     'int_saksiam_banner_updatename',
     'int_saksiam_banner_changetime',
     'int_saksiam_banner_changename',
     'int_saksiam_banner_order'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_banner_createAt';
    protected $updatedField  = 'int_saksiam_banner_updateAt';
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
        $data['data']['int_saksiam_banner_createAt'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_banner_updateAt'] = null;
        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasActiveChange = array_key_exists('int_saksiam_banner_active', $data['data']);

        $hasNameChange =
            array_key_exists('int_saksiam_banner_name', $data['data']) ||
            array_key_exists('int_saksiam_banner_picturePC', $data['data']);
            array_key_exists('int_saksiam_banner_pictureMoblie', $data['data']);
            array_key_exists('int_saksiam_banner_link', $data['data']);

        $hasUpdateName =
            !empty($data['data']['int_saksiam_banner_updatename'] ?? null);

        // 🔥 1. เปลี่ยน active → changetime
        if ($hasActiveChange) {
            $data['data']['int_saksiam_banner_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_banner_createAt']);
        }

        // 🔥 2. แก้ชื่อ → updateAt
        elseif ($hasNameChange) {
            $data['data']['int_saksiam_banner_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_banner_changetime']);
        }

        // 🔥 3. metadata → updateAt
        elseif ($hasUpdateName) {
            $data['data']['int_saksiam_banner_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_banner_changetime']);
        }

        unset($data['data']['int_saksiam_banner_createAt']);

        return $data;
    }

        // <--Start Manager sak -->

    public function getBanner($type = null, $offset = 0, $limit = 10, $activeFilter = null)
{
    $this->select('
        int_saksiam_banner_ID,
        int_saksiam_banner_name,
        int_saksiam_banner_picturePC,
        int_saksiam_banner_pictureMoblie,
        int_saksiam_banner_type,
        int_saksiam_banner_link,
        int_saksiam_banner_active,
        int_saksiam_banner_savename,
        int_saksiam_banner_createAt,
        int_saksiam_banner_updateAt
    ')
    ->orderBy('int_saksiam_banner_ID', 'DESC');

    // filter type
    if ($type !== null) {
        $this->where('int_saksiam_banner_type', $type);
    }

    // filter active
    if ($activeFilter !== null) {
        if (is_array($activeFilter)) {
            $this->whereIn('int_saksiam_banner_active', $activeFilter);
        } else {
            $this->where('int_saksiam_banner_active', $activeFilter);
        }
    }

    $query = $this->findAll($limit, $offset);

    $countQuery = $this->select('COUNT(int_saksiam_banner_ID) as total', false);

    if ($type !== null) {
        $countQuery->where('int_saksiam_banner_type', $type);
    }

    if ($activeFilter !== null) {
        if (is_array($activeFilter)) {
            $countQuery->whereIn('int_saksiam_banner_active', $activeFilter);
        } else {
            $countQuery->where('int_saksiam_banner_active', $activeFilter);
        }
    }

    $sizeCount = (int) $countQuery->get()->getRow()->total;

    $result = [];
    foreach ($query as $row) {
        if (!isset($result[$row['int_saksiam_banner_ID']])) {
            $result[$row['int_saksiam_banner_ID']] = [
                'id' => $row['int_saksiam_banner_ID'],
                'name' => $row['int_saksiam_banner_name'],
                'picturePC' => $row['int_saksiam_banner_picturePC'],
                'pictureMoblie' => $row['int_saksiam_banner_pictureMoblie'],
                'type' => $row['int_saksiam_banner_type'],
                'link' => $row['int_saksiam_banner_link'],
                'active' => $row['int_saksiam_banner_active'],
                'savename' => $row['int_saksiam_banner_savename'],
                'createAt' => $row['int_saksiam_banner_createAt'],
                'updateAt' => $row['int_saksiam_banner_updateAt'],
            ];
        }
    }

    return [
        'bannerscount' => $sizeCount,
        'bannder' => array_values($result),
    ];
}

        public function createBannerData($data)
        {
            // Ensure that the $data variable is set before use
            if (empty($data)) {
                throw new \Exception('Data is required for insertion');
            }
            $data['int_saksiam_banner_createAt'] = date('Y-m-d H:i:s'); // Use the correct column names
            $this->insert($data);
            return $this->getInsertID();
        }

        public function updateData($branderID, $data)
        {
            $existingData = $this->find($branderID);
            if (!$existingData) {
                return false; // No data to update
            }
            if (!empty($branderID)) {
                $this->set($data);
                $this->where('int_saksiam_banner_ID', $branderID);
                 if ($this->update()) {
                    return true; // Update successful
                 } else {
                    return false;
                 }
            } else {
                return false; 
            }
        }
          public function showBannerID($id = null)
    {
        $this->select('
                          int_saksiam_banner_ID As id,
                int_saksiam_banner_name As name,
                int_saksiam_banner_picturePC As picturePC,
                int_saksiam_banner_pictureMoblie As pictureMoblie,
                int_saksiam_banner_type As type,
                int_saksiam_banner_link As link,
                int_saksiam_banner_active As active,
                int_saksiam_banner_createAt As createAt,
             int_saksiam_banner_savename As savename,
             int_saksiam_banner_updateAt As updateAt
        ');
        if ($id !== null) {
            $query = $this->find($id);
        } else {
            $query = $this->orderBy('int_saksiam_banner_ID', 'ASC')->findAll();
        }
        return $query;
    }


        public function getBannerMoveData()
        {
            return $this->select('
                int_saksiam_banner.int_saksiam_banner_ID As id,
                int_saksiam_banner.int_saksiam_banner_name As name,
                int_saksiam_banner.int_saksiam_banner_picturePC As picturePC,
                int_saksiam_banner.int_saksiam_banner_pictureMoblie As pictureMoblie,
                int_saksiam_banner.int_saksiam_banner_type As type,
                int_saksiam_banner.int_saksiam_banner_link As link,
                int_saksiam_banner.int_saksiam_banner_active As active,
                int_saksiam_banner.int_saksiam_banner_order As order
            ')
            ->where('int_saksiam_banner.int_saksiam_banner_type', 'หน้าหลัก')
            ->whereIn('int_saksiam_banner.int_saksiam_banner_active', [0, 1])
            ->orderBy('int_saksiam_banner.int_saksiam_banner_order', 'ASC')
            ->orderBy('int_saksiam_banner.int_saksiam_banner_ID', 'ASC')
            ->findAll();
        }

        public function updateBannerMove($orderData)
        {
            if (empty($orderData)) {
                return false;
            }
            $allSuccess = true;
            foreach ($orderData as $item) {
                 if (!isset($item['int_saksiam_banner_ID'], $item['int_saksiam_banner_order'])) {
                continue;
            }

            $existingData = $this->find($item['int_saksiam_banner_ID']);
                if (!$existingData) {
                    $allSuccess = false;
                continue;
            }

            $updateResult = $this->update($item['int_saksiam_banner_ID'], ['int_saksiam_banner_order' => $item['int_saksiam_banner_order']]);
                if (!$updateResult) {
                    $allSuccess = false;
                }
            }
           return $allSuccess;
        }

    // <--end Manager sak -->

    // <--Start Web sak -->   

        public function getBannerWebsite()
        {
            $this->select('
                int_saksiam_banner.int_saksiam_banner_ID As id,
                int_saksiam_banner.int_saksiam_banner_name As name,
                int_saksiam_banner.int_saksiam_banner_picturePC As picturePC,
                int_saksiam_banner.int_saksiam_banner_pictureMoblie As pictureMoblie,
                int_saksiam_banner.int_saksiam_banner_link As link,
                int_saksiam_banner.int_saksiam_banner_active As active,
            ')
             ->where('int_saksiam_banner.int_saksiam_banner_type', 'หน้าหลัก')
             ->where('int_saksiam_banner_active', 1)
             ->groupBy('int_saksiam_banner_ID,  int_saksiam_banner_order')
             ->orderBy('int_saksiam_banner_order', 'ASC')
             ->orderBy('int_saksiam_banner_ID', 'ASC');
            return $this->findAll();
        }
       // <--End Web sak -->
}