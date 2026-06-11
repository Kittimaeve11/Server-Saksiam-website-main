<?php

namespace App\Models;

use CodeIgniter\Model;

class FaqTypeModel extends Model
{
    protected $table = 'int_saksiam_typefqa';
    protected $primaryKey = 'int_saksiam_typefqa_id';

    protected $allowedFields = [
        'int_saksiam_typefqa_nameTH',
        'int_saksiam_typefqa_nameEN',
        'int_saksiam_typefqa_active',
        'int_saksiam_typefqa_savename',
        'int_saksiam_typefqa_updatename',
        'int_saksiam_typefqa_changename',
        'int_saksiam_typefqa_order',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_typefqa_createAt';
    protected $updatedField = 'int_saksiam_typefqa_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    /* ======================================================
        HANDLE INSERT TIME
    ====================================================== */
    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_typefqa_createAt'] = date('Y-m-d H:i:s');

        unset($data['data']['int_saksiam_typefqa_updateAt']);

        return $data;
    }

    /* ======================================================
        HANDLE UPDATE TIME
    ====================================================== */
    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_typefqa_updatename'])) {
            $data['data']['int_saksiam_typefqa_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_typefqa_changetime']);
        }

        if (isset($data['data']['int_saksiam_typefqa_changename'])) {
            $data['data']['int_saksiam_typefqa_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_typefqa_updateAt']);
        }

        unset($data['data']['int_saksiam_typefqa_createAt']);

        return $data;
    }

    /* ======================================================
        GET DATA (MANAGER)
    ====================================================== */
    public function getFaqTypeData($activeFilter = null, $offset = 0, $limit = 10)
    {
        $this->select('
            int_saksiam_typefqa_id,
            int_saksiam_typefqa_nameTH,
            int_saksiam_typefqa_nameEN,
            int_saksiam_typefqa_active,
            int_saksiam_typefqa_savename,
            int_saksiam_typefqa_createAt,
            int_saksiam_typefqa_updateAt,
            int_saksiam_typefqa_order
        ')
        ->orderBy('int_saksiam_typefqa_order', 'ASC');

        if ($activeFilter !== null) {
            $this->where('int_saksiam_typefqa_active', $activeFilter);
        }

        $query = $this->findAll($limit, $offset);

        $countQuery = $this->select('COUNT(int_saksiam_typefqa_id) as total', false);
        if ($activeFilter !== null) {
            $countQuery->where('int_saksiam_typefqa_active', $activeFilter);
        }

        $sizeCount = (int) $countQuery->get()->getRow()->total;

        return [
            'counts' => $sizeCount,
            'faqtypes' => $query
        ];
    }

    /* ======================================================
        CREATE
    ====================================================== */
    public function createFaqType($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        $data['int_saksiam_typefqa_createAt'] = date('Y-m-d H:i:s');

        $this->insert($data);

        return $this->getInsertID();
    }

    /* ======================================================
        UPDATE
    ====================================================== */
    public function updateFaqType($id, $data)
    {
        $existingData = $this->find($id);

        if (!$existingData) {
            return false;
        }

        return $this->update($id, $data);
    }

    /* ======================================================
        SHOW BY ID
    ====================================================== */
    public function showFaqType($id = null)
    {
        $this->select('
            int_saksiam_typefqa_id AS id,
            int_saksiam_typefqa_id AS faqtypeID,
            int_saksiam_typefqa_id AS typefaqID,
            int_saksiam_typefqa_nameTH AS nameTH,
            int_saksiam_typefqa_nameEN AS nameEN,
            int_saksiam_typefqa_nameTH AS faqtypenameTH,
            int_saksiam_typefqa_nameEN AS faqtypenameEN,
            int_saksiam_typefqa_active AS active,
            int_saksiam_typefqa_savename AS savename,
            int_saksiam_typefqa_createAt AS createAt,
            int_saksiam_typefqa_updateAt AS updateAt,
            int_saksiam_typefqa_order AS faqtypeorder,
            int_saksiam_typefqa_order AS typefaqorder
        ');

        if ($id !== null) {
            return $this->find($id);
        }

        return $this->orderBy('int_saksiam_typefqa_order', 'ASC')->findAll();
    }

    /* ======================================================
        MOVE ORDER
    ====================================================== */
    public function updateFaqTypeOrder($orderData)
    {
        if (empty($orderData)) return false;

        $allSuccess = true;

        foreach ($orderData as $item) {
            if (!isset($item['int_saksiam_typefqa_id'], $item['int_saksiam_typefqa_order'])) {
                continue;
            }

            $update = $this->update(
                $item['int_saksiam_typefqa_id'],
                ['int_saksiam_typefqa_order' => $item['int_saksiam_typefqa_order']]
            );

            if (!$update) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }
}
