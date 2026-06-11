<?php

namespace App\Models;

use CodeIgniter\Model;

class TopicModel extends Model
{
    protected $table            = 'int_saksiam_topic';
    protected $primaryKey       = 'int_saksiam_topic_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_topic_id',
        'int_saksiam_topic_nameTH',
        'int_saksiam_topic_nameEN',
        'int_saksiam_topic_savename',
        'int_saksiam_topic_updatename',
        'int_saksiam_topic_active',
        'int_saksiam_topic_changename',
        'int_saksiam_topic_changetime',
        'int_saksiam_topic_order'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_topic_createAt';
    protected $updatedField  = 'int_saksiam_topic_updateAt';
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
        $data['data']['int_saksiam_topic_createAt'] = date('Y-m-d H:i:s');
        $data['data']['int_saksiam_topic_updateAt'] = null;
        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
    $hasActiveChange = array_key_exists('int_saksiam_topic_active', $data['data']);

    $hasNameChange =
        array_key_exists('int_saksiam_topic_nameTH', $data['data']) ||
        array_key_exists('int_saksiam_topic_nameEN', $data['data']);

    $hasUpdateName =
        !empty($data['data']['int_saksiam_topic_updatename'] ?? null);

    // 🔥 1. เปลี่ยน active → changetime
    if ($hasActiveChange) {
        $data['data']['int_saksiam_topic_changetime'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_topic_updateAt']);
    }

    // 🔥 2. แก้ชื่อ → updateAt
    elseif ($hasNameChange) {
        $data['data']['int_saksiam_topic_updateAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_topic_changetime']);
    }

    // 🔥 3. metadata → updateAt
    elseif ($hasUpdateName) {
        $data['data']['int_saksiam_topic_updateAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_topic_changetime']);
    }

    unset($data['data']['int_saksiam_topic_createAt']);

    return $data;
    }

  // <--Start Manager sak -->
   public function getTopicData($activeFilter = null, $offset, $limit)
    {
        $this->select('
            int_saksiam_topic.int_saksiam_topic_id,
            int_saksiam_topic.int_saksiam_topic_nameTH,
            int_saksiam_topic.int_saksiam_topic_nameEN,
            int_saksiam_topic.int_saksiam_topic_savename,
            int_saksiam_topic.int_saksiam_topic_active,
            int_saksiam_topic.int_saksiam_topic_createAt,
            int_saksiam_topic.int_saksiam_topic_updateAt
        ')
            ->orderBy('int_saksiam_topic.int_saksiam_topic_id', 'DESC');
        if ($activeFilter !== null) {
            $this->where('int_saksiam_topic.int_saksiam_topic_active', $activeFilter);
        }
        $query = $this->findAll($limit, $offset);
        $countQuery = $this->select('COUNT(int_saksiam_topic.int_saksiam_topic_id) as total', false);
        if ($activeFilter !== null) {
            $countQuery->where('int_saksiam_topic.int_saksiam_topic_active', $activeFilter);
        }

        $sizeCount = (int) $countQuery->get()->getRow()->total;
        $result = [];
        foreach ($query as $row) {
            if (!isset($result[$row['int_saksiam_topic_id']])) {
                $result[$row['int_saksiam_topic_id']] = [
                    'topic_id' => $row['int_saksiam_topic_id'],
                    'nameTH' => $row['int_saksiam_topic_nameTH'],
                    'nameEN' => $row['int_saksiam_topic_nameEN'],
                    'savename' => $row['int_saksiam_topic_savename'],
                    'active' => $row['int_saksiam_topic_active'],
                    'createAt' => $row['int_saksiam_topic_createAt'],
                    'updateAt' => $row['int_saksiam_topic_updateAt'],
                ];
            }

        }
        return [
            'counts' => $sizeCount,
            'topics' => array_values($result),

        ];
    }

 public function createTopicData($data)  // Receive the data to be saved
    {
        // Ensure that the $data variable is set before use
        if (empty($data)) {
            throw new \Exception('Data is required for insertion');
        }
        $data['int_saksiam_topic_createAt'] = date('Y-m-d H:i:s'); // Use the correct column names
        $this->insert($data);
        return $this->getInsertID();
    }

    public function updateData($topicID, $data)
    {
        // Verify if the provided ID matches the data in the database
        $existingData = $this->find($topicID);
        if (!$existingData) {
            return false; // No data to update
        }

        // If the ID is correct
        if (!empty($topicID)) {
            // Check if detail, active, namelist, or updatename are modified and save the new values; if not provided, use the existing values from the database
            // Set the data to be updated

            $this->set($data);

            // Update condition specified by ID
            $this->where('int_saksiam_topic_id', $topicID);

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

    public function showTopicID($id = null)
    {
        $this->select('
            int_saksiam_topic.int_saksiam_topic_id As topic_ID,
            int_saksiam_topic.int_saksiam_topic_nameTH  As nameTH,
            int_saksiam_topic.int_saksiam_topic_nameEN  As nameEN,
            int_saksiam_topic.int_saksiam_topic_savename As savename,
            int_saksiam_topic.int_saksiam_topic_active As active,
            int_saksiam_topic.int_saksiam_topic_createAt As createAt,
            int_saksiam_topic.int_saksiam_topic_updateAt As updateAt
        ');
        if ($id !== null) {
            $query = $this->find($id);
        } else {
            $query = $this->orderBy(' int_saksiam_topic.int_saksiam_topic_id', 'ASC')->findAll();
        }
        return $query;
    }

    public function getTopicMove()
    {
        $this->select('
            int_saksiam_topic.int_saksiam_topic_id As topic_ID,
            int_saksiam_topic.int_saksiam_topic_nameTH As nameTH,
            int_saksiam_topic.int_saksiam_topic_nameEN As nameEN,
            int_saksiam_topic.int_saksiam_topic_order As topicorder,
            int_saksiam_topic.int_saksiam_topic_active As active
        ')
            ->whereIn('int_saksiam_topic_active', [0, 1])
            ->orderBy('int_saksiam_topic_order', 'ASC')
            ->orderBy('int_saksiam_topic_id', 'ASC');
        return $this->findAll();
    }



    public function updateTopicMove($orderData)
    {
        if (empty($orderData)) {
            return false;
        }

        $allSuccess = true;
        foreach ($orderData as $item) {
            if (!isset($item['int_saksiam_topic_id'], $item['int_saksiam_topic_order'])) {
                continue;
            }

            $existingData = $this->find($item['int_saksiam_topic_id']);
            if (!$existingData) {
                $allSuccess = false;
                continue;
            }

            $updateResult = $this->update($item['int_saksiam_topic_id'], ['int_saksiam_topic_order' => $item['int_saksiam_topic_order']]);
            if (!$updateResult) {
                $allSuccess = false;
            }
        }
        return $allSuccess;
    }

    public function getTopicShow()
    {
        $this->select('
            int_saksiam_topic.int_saksiam_topic_id As topicID,
            int_saksiam_topic.int_saksiam_topic_nameTH As topic_nameTH,
            int_saksiam_topic.int_saksiam_topic_nameEN As topic_nameEN,
            int_saksiam_topic.int_saksiam_topic_active As topic_active,
            int_saksiam_topic.int_saksiam_topic_order As topicorder,
    ')
            ->groupBy('int_saksiam_topic_id,  int_saksiam_topic_order')
            ->orderBy('int_saksiam_topic_order', 'ASC')
            ->orderBy('int_saksiam_topic_id', 'ASC');
        return $this->findAll();
    }


    // <--end Manager sak -->
// -----------------------------------------------------------------------------------------------   
    // <--Start Web sak -->

    public function getTopiceWebsite()
    {
        return $this->getTopicWebsite();
    }

    public function getTopicWebsite()
    {
        return $this->select('
            int_saksiam_topic.int_saksiam_topic_id AS id,
            int_saksiam_topic.int_saksiam_topic_nameTH AS name,
            int_saksiam_topic.int_saksiam_topic_nameTH AS nameTH,
            int_saksiam_topic.int_saksiam_topic_nameEN AS nameEN,
            int_saksiam_topic.int_saksiam_topic_active AS active,
            int_saksiam_topic.int_saksiam_topic_order AS topicorder
        ')
            ->orderBy('int_saksiam_topic_order', 'ASC')
            ->orderBy('int_saksiam_topic_id', 'ASC')
            ->findAll();
    }
    // <--End Web sak -->


}
