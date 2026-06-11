<?php

namespace App\Models;

use CodeIgniter\Model;

class PolicyModel extends Model
{
    protected $table = 'int_saksiam_policy';
    protected $primaryKey = 'int_saksiam_policy_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_policy_num',
        'int_saksiam_policy_nameTH',
        'int_saksiam_policy_nameEN',
        'int_saksiam_policy_detailTH',
        'int_saksiam_policy_detailEN',
        'int_saksiam_policy_active',
        'int_saksiam_policy_createname',
        'int_saksiam_policy_updatename',
        'int_saksiam_policy_approvedDate',
        'int_saksiam_policy_approvedName',
        'int_saksiam_policy_changename',
        'int_saksiam_policy_changetime',
        'int_saksiam_policy_note',
        'int_saksiam_policy_improvement',
        'int_saksiam_policy_cancellation',
        'int_saksiam_policy_order',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_policy_createAt';
    protected $updatedField = 'int_saksiam_policy_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_policy_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_policy_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasStatusChange =
            array_key_exists('int_saksiam_policy_active', $data['data']) ||
            !empty($data['data']['int_saksiam_policy_changename'] ?? null);

        if ($hasStatusChange) {
            $data['data']['int_saksiam_policy_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_policy_updateAt']);
        } elseif (!empty($data['data']['int_saksiam_policy_updatename'] ?? null)) {
            $data['data']['int_saksiam_policy_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_policy_changetime']);
        }

        unset($data['data']['int_saksiam_policy_createAt']);

        return $data;
    }

    public function getPolicyData($activeFilter = null, $offset = 0, $limit = 50)
    {
        $builder = $this->baseSelect()
            ->orderBy('int_saksiam_policy_order', 'ASC')
            ->orderBy('int_saksiam_policy_id', 'DESC');

        if ($activeFilter !== null) {
            $builder->where('int_saksiam_policy_active', $activeFilter);
        }

        $query = $builder->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            $countBuilder->where('int_saksiam_policy_active', $activeFilter);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'policies' => $query,
            'policy' => $query,
        ];
    }

    public function createPolicy($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        if (empty($data['int_saksiam_policy_num'])) {
            $data['int_saksiam_policy_num'] = $this->generatePolicyNumber();
        }

        $data['int_saksiam_policy_createAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updatePolicy($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showPolicy($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            if (is_numeric($id)) {
                return $this->find($id);
            }

            return $this->where('int_saksiam_policy_num', $id)->first();
        }

        return $this
            ->where('int_saksiam_policy_active', 1)
            ->orderBy('int_saksiam_policy_order', 'ASC')
            ->orderBy('int_saksiam_policy_id', 'ASC')
            ->findAll();
    }

    public function updatePolicyOrder($orderData)
    {
        if (empty($orderData)) {
            return false;
        }

        $allSuccess = true;

        foreach ($orderData as $item) {
            $id = $item['int_saksiam_policy_id'] ?? $item['id'] ?? null;
            $order = $item['int_saksiam_policy_order'] ?? $item['order'] ?? null;

            if ($id === null || $order === null) {
                continue;
            }

            if (!$this->update($id, ['int_saksiam_policy_order' => $order])) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function generatePolicyNumber()
    {
        $prefix = 'PO' . date('ymd');
        $latest = $this->select('int_saksiam_policy_num')
            ->like('int_saksiam_policy_num', $prefix, 'after')
            ->orderBy('int_saksiam_policy_id', 'DESC')
            ->first();

        $nextNumber = 1;
        if (!empty($latest['int_saksiam_policy_num'])) {
            $nextNumber = ((int) substr($latest['int_saksiam_policy_num'], -4)) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_policy.*,
            int_saksiam_policy_id AS id,
            int_saksiam_policy_num AS policyNum,
            int_saksiam_policy_nameTH AS nameTH,
            int_saksiam_policy_nameEN AS nameEN,
            int_saksiam_policy_detailTH AS detailTH,
            int_saksiam_policy_detailEN AS detailEN,
            int_saksiam_policy_active AS active,
            int_saksiam_policy_createAt AS createAt,
            int_saksiam_policy_createname AS createname,
            int_saksiam_policy_updateAt AS updateAt,
            int_saksiam_policy_updatename AS updatename,
            int_saksiam_policy_approvedDate AS approvedDate,
            int_saksiam_policy_approvedDate AS approvedate,
            int_saksiam_policy_approvedName AS approvedName,
            int_saksiam_policy_approvedName AS approvename,
            int_saksiam_policy_changetime AS changetime,
            int_saksiam_policy_changename AS changename,
            int_saksiam_policy_note AS note,
            int_saksiam_policy_improvement AS improvement,
            int_saksiam_policy_improvement AS improvement_text,
            int_saksiam_policy_improvement AS improvementText,
            int_saksiam_policy_cancellation AS cancellation,
            int_saksiam_policy_order AS policyorder
        ');
    }
}
