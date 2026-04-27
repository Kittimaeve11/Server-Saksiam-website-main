<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table            = 'int_saksiam_branch';
    protected $primaryKey       = 'int_saksiam_branch_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_branch_id',
        'int_saksiam_branch_regionid',
        'int_saksiam_branch_areaid',
        'int_saksiam_branch_type',
        'int_saksiam_branch_name',
        'int_saksiam_branch_address',
        'int_saksiam_branch_DISTRICTID',
        'int_saksiam_branch_DISTRICTNAME',
        'int_saksiam_branch_AMPHURID',
        'int_saksiam_branch_AMPHURNAME',
        'int_saksiam_branch_PROVINCEID',
        'int_saksiam_branch_PROVINCENAME',
        'int_saksiam_branch_zipcode',
        'int_saksiam_branch_detail',
        'int_saksiam_branch_tel',
        'int_saksiam_branch_lng',
        'int_saksiam_branch_lng',
        'int_saksiam_branch_status',
        'int_saksiam_branch_savename',
        'int_saksiam_branch_updatename',
        'int_saksiam_branch_changename',
        'int_saksiam_branch_changetime'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_branch_createAt';
    protected $updatedField  = 'int_saksiam_branch_updateAt';
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
        $data['data']['int_saksiam_branch_createAt'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_branch_updateAt'] = null;
        return $data;
    }

      protected function handleUpdateTimestamps(array $data)
    {
        $hasActiveChange = array_key_exists('int_saksiam_branch_status', $data['data']);

        $hasNameChange =
            array_key_exists('int_saksiam_branch_regionid', $data['data']) ||
            array_key_exists('int_saksiam_branch_areaid', $data['data']) ||
            array_key_exists('int_saksiam_branch_type', $data['data']) ||
            array_key_exists('int_saksiam_branch_name', $data['data']) ||
            array_key_exists('int_saksiam_branch_address', $data['data'])||
            array_key_exists('int_saksiam_branch_DISTRICTID', $data['data'])||
            array_key_exists('int_saksiam_branch_DISTRICTNAME', $data['data'])||
            array_key_exists('int_saksiam_branch_AMPHURID', $data['data'])||
            array_key_exists('int_saksiam_branch_AMPHURNAME', $data['data'])||
            array_key_exists('int_saksiam_branch_PROVINCEID', $data['data'])||
            array_key_exists('int_saksiam_branch_PROVINCENAME', $data['data'])||
            array_key_exists('int_saksiam_branch_zipcode', $data['data'])||
            array_key_exists('int_saksiam_branch_detail', $data['data'])||
            array_key_exists('int_saksiam_branch_tel', $data['data'])||
            array_key_exists('int_saksiam_branch_lat', $data['data'])||
            array_key_exists('int_saksiam_branch_lng', $data['data']);

        $hasUpdateName =
            !empty($data['data']['int_saksiam_branch_updatename'] ?? null);

        // 🔥 1. เปลี่ยน active → changetime
        if ($hasActiveChange) {
            $data['data']['	int_saksiam_branch_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_branch_createAt']);
        }

        // 🔥 2. แก้ชื่อ → updateAt
        elseif ($hasNameChange) {
            $data['data']['int_saksiam_branch_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['	int_saksiam_branch_changetime']);
        }

        // 🔥 3. metadata → updateAt
        elseif ($hasUpdateName) {
            $data['data']['int_saksiam_branch_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['	int_saksiam_branch_changetime']);
        }

        unset($data['data']['int_saksiam_branch_createAt']);

        return $data;
    }
  // <--Start Manager sak -->
  public function getbranchData ($activeFilter = null, $offset, $limit)
  {
        $this->select('
            int_saksiam_branch.int_saksiam_branch_id,
            int_saksiam_branch.int_saksiam_branch_regionid,
            int_saksiam_branch.int_saksiam_branch_areaid,
            int_saksiam_branch.int_saksiam_branch_type,
            int_saksiam_branch.int_saksiam_branch_name,
            int_saksiam_branch.int_saksiam_branch_address,
            int_saksiam_branch.int_saksiam_branch_DISTRICTID,
            int_saksiam_branch.int_saksiam_branch_DISTRICTNAME,
            int_saksiam_branch.int_saksiam_branch_AMPHURID,
            int_saksiam_branch.int_saksiam_branch_AMPHURNAME,
            int_saksiam_branch.int_saksiam_branch_PROVINCEID,
            int_saksiam_branch.int_saksiam_branch_PROVINCENAME,
            int_saksiam_branch.int_saksiam_branch_zipcode,
            int_saksiam_branch.int_saksiam_branch_detail,
            int_saksiam_branch.int_saksiam_branch_tel,
            int_saksiam_branch.int_saksiam_branch_lat,
            int_saksiam_branch.int_saksiam_branch_lng,
            int_saksiam_branch.int_saksiam_branch_status,
            int_saksiam_branch.int_saksiam_branch_savename,
            int_saksiam_branch.int_saksiam_branch_createAt,
            int_saksiam_branch.int_saksiam_branch_updateAt
        ')
        ->orderBy('int_saksiam_branch.int_saksiam_branch_id', 'DESC');
        if ($activeFilter !== null) {
            $this->where('int_saksiam_branch.int_saksiam_branch_status', $activeFilter);
        }

        $query = $this->findAll($limit, $offset);
        $countQuery = $this->select('COUNT(int_saksiam_branch.int_saksiam_branch_id) as branchs', false);
        if ($activeFilter !== null) {
            $countQuery->where('int_saksiam_branch.int_saksiam_branch_status', $activeFilter);
        }

        $sizeCount = (int) $countQuery->get()->getRow()->branchs;
        $result = [];
        foreach ($query as $row) {
            if (!isset($result[$row[int_saksiam_branch_id]])) {
                $result[$row['int_saksiam_branch_id']] = [
                    'id' => $row['int_saksiam_branch_id'],
                    'region' => $row['int_saksiam_branch_regionid'],
                    'area' => $row['int_saksiam_branch_areaid'],
                    'type' => $row['int_saksiam_branch_type'],
                    'name' => $row['int_saksiam_branch_name'],
                    'address' => $row['int_saksiam_branch_address'],
                    'districtid' => $row['int_saksiam_branch_DISTRICTID'],
                    'districtname' => $row['int_saksiam_branch_DISTRICTNAME'],
                    'amphurid' => $row['int_saksiam_branch_AMPHURID'],
                    'amphurname' => $row['int_saksiam_branch_AMPHURNAME'],
                    'provinceid' => $row['int_saksiam_branch_PROVINCEID'],
                    'provincename' => $row['int_saksiam_branch_PROVINCENAME'],
                    'zipcode' => $row['int_saksiam_branch_zipcode'],
                    'detail' => $row['int_saksiam_branch_detail'],
                    'tel' => $row['int_saksiam_branch_tel'],
                    'lat' => $row['int_saksiam_branch_lat'],
                    'lng' => $row['int_saksiam_branch_lng'],
                    'status' => $row['int_saksiam_branch_status'],
                    'savename' => $row['int_saksiam_branch_savename'],
                    'createAt' => $row['int_saksiam_branch_createAt'],
                    'updateAt' => $row['int_saksiam_branch_updateAt']
                ];
            }
        }
         return [
            'counts' => $sizeCount,
            'data' => array_values($result),
        ];
  }

  public function createBranchData($data)
  {
        if (empty($data)) {
            throw new \Exception('Data is required for insertion');
        }
        $data['int_saksiam_branch_createAt'] = date('Y-m-d H:i:s'); // Use the correct column names
        $this->insert($data);
        return $this->getInsertID();
  }

  public function updateData($branchID, $data)
    {
        // Verify if the provided ID matches the data in the database
        $existingData = $this->find($branchID);
        if (!$existingData) {
            return false; // No data to update
        }

        // If the ID is correct
        if (!empty($branchID)) {
            // Check if detail, active, namelist, or updatename are modified and save the new values; if not provided, use the existing values from the database
            // Set the data to be updated

            $this->set($data);

            // Update condition specified by ID
            $this->where('int_saksiam_branch_id', $branchID);

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

     public function showBranchID($id = null)
    {
        $this->select('
           int_saksiam_branch.int_saksiam_branch_id As id,
            int_saksiam_branch.int_saksiam_branch_regionid As region,
            int_saksiam_branch.int_saksiam_branch_areaid As area,
            int_saksiam_branch.int_saksiam_branch_type As type,
            int_saksiam_branch.int_saksiam_branch_name As name,
            int_saksiam_branch.int_saksiam_branch_address As address,
            int_saksiam_branch.int_saksiam_branch_DISTRICTID As districtid,
            int_saksiam_branch.int_saksiam_branch_DISTRICTNAME As districtname,
            int_saksiam_branch.int_saksiam_branch_AMPHURID As amphurid,
            int_saksiam_branch.int_saksiam_branch_AMPHURNAME As amphurname,
            int_saksiam_branch.int_saksiam_branch_PROVINCEID As provinceid,
            int_saksiam_branch.int_saksiam_branch_PROVINCENAME As provincename,
            int_saksiam_branch.int_saksiam_branch_zipcode As zipcode,
            int_saksiam_branch.int_saksiam_branch_detail As detail,
            int_saksiam_branch.int_saksiam_branch_tel As tel,
            int_saksiam_branch.int_saksiam_branch_lat As lat,
            int_saksiam_branch.int_saksiam_branch_lng As lng,
            int_saksiam_branch.int_saksiam_branch_status As status,
            int_saksiam_branch.int_saksiam_branch_savename As savename,
            int_saksiam_branch.int_saksiam_branch_createAt As createAt,
            int_saksiam_branch.int_saksiam_branch_updateAt As updateAt
        ');
        if ($id !== null) {
            $query = $this->find($id);
        } else {
            $query = $this->orderBy(' int_saksiam_branch.int_saksiam_branch_id', 'ASC')->findAll();
        }
        return $query;
    }

      // <--end Manager sak -->
// -----------------------------------------------------------------------------------------------   
    // <--Start Web sak -->

public function getbranchWebsite($PROVINCEID = null, $LAT = null, $LNG = null)
{
    // ✅ filter status
    $this->where('int_saksiam_branch_status', 1);

    // 🔥 SELECT + distance
    if ($LAT !== null && $LNG !== null) {
        $this->select("
            int_saksiam_branch.*,
            (
                6371 * ACOS(
                    COS(RADIANS($LAT))
                    * COS(RADIANS(int_saksiam_branch_lat))
                    * COS(RADIANS(int_saksiam_branch_lng) - RADIANS($LNG))
                    + SIN(RADIANS($LAT))
                    * SIN(RADIANS(int_saksiam_branch_lat))
                )
            ) AS distance
        ");
    } else {
        $this->select('int_saksiam_branch.*');
    }

    // 🔥 filter จังหวัด
    if ($PROVINCEID !== null) {
        $this->where('int_saksiam_branch_PROVINCEID', $PROVINCEID);
    }

    // 🔥 filter ใกล้ฉัน
    if ($LAT !== null && $LNG !== null) {

        // bounding box
        $latRange = 0.3;
        $lngRange = 0.3;

        $this->where('int_saksiam_branch_lat >=', $LAT - $latRange);
        $this->where('int_saksiam_branch_lat <=', $LAT + $latRange);
        $this->where('int_saksiam_branch_lng >=', $LNG - $lngRange);
        $this->where('int_saksiam_branch_lng <=', $LNG + $lngRange);

        $this->having('distance <=', 32.1868);
        $this->orderBy('distance', 'ASC');
    } else {
        $this->orderBy('int_saksiam_branch_id', 'DESC');
    }

    $rows = $this->findAll();

    return [
        'counts' => count($rows),
        'data' => array_map(function ($row) {
            return [
                'id' => $row['int_saksiam_branch_id'],
                'type' => $row['int_saksiam_branch_type'],
                'name' => $row['int_saksiam_branch_name'],
                 'detail' => $row['int_saksiam_branch_detail'],
                'lat' => $row['int_saksiam_branch_lat'],
                'lng' => $row['int_saksiam_branch_lng'],
                'distance' => isset($row['distance']) ? round($row['distance'], 2) : null,
                'address' => $row['int_saksiam_branch_address'],
                'districtid' => $row['int_saksiam_branch_DISTRICTID'],
                'districtname' => $row['int_saksiam_branch_DISTRICTNAME'],
                'amphurid' => $row['int_saksiam_branch_AMPHURID'],
                'amphurname' => $row['int_saksiam_branch_AMPHURNAME'],
                'provinceid' => $row['int_saksiam_branch_PROVINCEID'],
                'provincename' => $row['int_saksiam_branch_PROVINCENAME'],
                'zipcode' => $row['int_saksiam_branch_zipcode'],
                'tel' => $row['int_saksiam_branch_tel'],
            ];
        }, $rows)
    ];
}
    // <--End Web sak -->

}
