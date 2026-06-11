<?php

namespace App\Models;

use CodeIgniter\Model;

class EditorialTypeModel extends Model
{
    protected $table = 'int_saksiam_typeeditoria';
    protected $primaryKey = 'int_saksiam_Typeeditorial_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_Typeeditoria_nameTH',
        'int_saksiam_Typeeditoria_nameEN',
        'int_saksiam_Typeeditoria_active',
        'int_saksiam_Typeeditoria_savename',
        'int_saksiam_Typeeditoria_updatename',
        'int_saksiam_Typeeditoria_changename',
        'int_saksiam_Typeeditoria_changetime',
        'int_saksiam_Typeeditoria_order',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_Typeeditoria_createAt';
    protected $updatedField = 'int_saksiam_Typeeditoria_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_Typeeditoria_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_Typeeditoria_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_Typeeditoria_updatename'])) {
            $data['data']['int_saksiam_Typeeditoria_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_Typeeditoria_changetime']);
        }

        if (isset($data['data']['int_saksiam_Typeeditoria_changename'])) {
            $data['data']['int_saksiam_Typeeditoria_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_Typeeditoria_updateAt']);
        }

        unset($data['data']['int_saksiam_Typeeditoria_createAt']);

        return $data;
    }

    public function getEditorialTypeData($activeFilter = null, $offset = 0, $limit = 50)
    {
        $this->baseSelect()
            ->orderBy('int_saksiam_Typeeditoria_order', 'ASC')
            ->orderBy('int_saksiam_Typeeditorial_id', 'ASC');

        if ($activeFilter !== null) {
            $this->where('int_saksiam_Typeeditoria_active', $activeFilter);
        }

        $query = $this->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            $countBuilder->where('int_saksiam_Typeeditoria_active', $activeFilter);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'editorialtypes' => $query,
            'typeeditorias' => $query,
            'types' => $query,
        ];
    }

    public function createEditorialType($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        $data['int_saksiam_Typeeditoria_createAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updateEditorialType($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showEditorialType($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            return $this->find($id);
        }

        return $this
            ->orderBy('int_saksiam_Typeeditoria_order', 'ASC')
            ->orderBy('int_saksiam_Typeeditorial_id', 'ASC')
            ->findAll();
    }

    public function updateEditorialTypeOrder($orderData)
    {
        if (empty($orderData)) {
            return false;
        }

        $allSuccess = true;

        foreach ($orderData as $item) {
            $id = $item['int_saksiam_Typeeditorial_id'] ?? $item['id'] ?? null;
            $order = $item['int_saksiam_Typeeditoria_order'] ?? $item['order'] ?? null;

            if ($id === null || $order === null) {
                continue;
            }

            if (!$this->update($id, ['int_saksiam_Typeeditoria_order' => $order])) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_Typeeditorial_id,
            int_saksiam_Typeeditoria_nameTH,
            int_saksiam_Typeeditoria_nameEN,
            int_saksiam_Typeeditoria_active,
            int_saksiam_Typeeditoria_savename,
            int_saksiam_Typeeditoria_createAt,
            int_saksiam_Typeeditoria_updateAt,
            int_saksiam_Typeeditoria_order,
            int_saksiam_Typeeditorial_id AS editorialtypeID,
            int_saksiam_Typeeditorial_id AS typeeditoriaID,
            int_saksiam_Typeeditorial_id AS id,
            int_saksiam_Typeeditoria_nameTH AS editorialtypenameTH,
            int_saksiam_Typeeditoria_nameEN AS editorialtypenameEN,
            int_saksiam_Typeeditoria_nameTH AS nameTH,
            int_saksiam_Typeeditoria_nameEN AS nameEN,
            int_saksiam_Typeeditoria_active AS active,
            int_saksiam_Typeeditoria_savename AS savename,
            int_saksiam_Typeeditoria_createAt AS createAt,
            int_saksiam_Typeeditoria_updateAt AS updateAt,
            int_saksiam_Typeeditoria_order AS editorialtypeorder,
            int_saksiam_Typeeditoria_order AS typeeditoriaorder
        ');
    }
}
