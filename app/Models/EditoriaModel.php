<?php

namespace App\Models;

use CodeIgniter\Model;

class EditoriaModel extends Model
{
    protected $table = 'int_saksiam_editoria';
    protected $primaryKey = 'int_saksiam_editoria_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_editoria_num',
        'int_saksiam_editoria_typeID',
        'int_saksiam_editoria_titieTH',
        'int_saksiam_editoria_titieEN',
        'int_saksiam_editoria_descriptionTH',
        'int_saksiam_editoria_descriptionEN',
        'int_saksiam_editoria_gallary',
        'int_saksiam_editoria_pin',
        'int_saksiam_editoria_active',
        'int_saksiam_editoria_createname',
        'int_saksiam_editoria_updatename',
        'int_saksiam_editoria_approvedate',
        'int_saksiam_editoria_approvename',
        'int_saksiam_editoria_note',
        'int_saksiam_editoria_changetime',
        'int_saksiam_editoria_chagename',
        'int_saksiam_editoria_improvement',
        'int_saksiam_editoria_cancellation',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_editoria_creacteAt';
    protected $updatedField = 'int_saksiam_editoria_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_editoria_creacteAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_editoria_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasActiveChange = array_key_exists('int_saksiam_editoria_active', $data['data']);
        $hasChangeName = !empty($data['data']['int_saksiam_editoria_chagename'] ?? null);

        if ($hasActiveChange || $hasChangeName) {
            $data['data']['int_saksiam_editoria_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_editoria_updateAt']);
        } elseif (!empty($data['data']['int_saksiam_editoria_updatename'] ?? null)) {
            $data['data']['int_saksiam_editoria_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_editoria_changetime']);
        }

        unset($data['data']['int_saksiam_editoria_creacteAt']);

        return $data;
    }

    public function getEditoriaData($activeFilter = null, $offset = 0, $limit = 50, $typeFilter = null)
    {
        $builder = $this->baseSelect()
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_id', 'DESC');

        if ($activeFilter !== null) {
            $builder->where('int_saksiam_editoria.int_saksiam_editoria_active', $activeFilter);
        }

        if ($typeFilter !== null) {
            $builder->where('int_saksiam_editoria.int_saksiam_editoria_typeID', $typeFilter);
        }

        $query = $builder->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            $countBuilder->where('int_saksiam_editoria_active', $activeFilter);
        }

        if ($typeFilter !== null) {
            $countBuilder->where('int_saksiam_editoria_typeID', $typeFilter);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'editorias' => $query,
            'articles' => $query,
        ];
    }

    public function createEditoria($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        if (empty($data['int_saksiam_editoria_num'])) {
            $data['int_saksiam_editoria_num'] = $this->generateEditoriaNumber();
        }

        $data['int_saksiam_editoria_creacteAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updateEditoria($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showEditoria($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            if (is_numeric($id)) {
                return $this->find($id);
            }

            return $this->where('int_saksiam_editoria.int_saksiam_editoria_num', $id)->first();
        }

        return $this
            ->where('int_saksiam_editoria.int_saksiam_editoria_active', 1)
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_id', 'DESC')
            ->findAll();
    }

    public function getPinnedLatestEditoria($limit = 10, $typeFilter = null)
    {
        $limit = max(1, (int) $limit);

        $pinnedBuilder = $this->baseSelect()
            ->where('int_saksiam_editoria.int_saksiam_editoria_active', 1)
            ->where('int_saksiam_typeeditoria.int_saksiam_Typeeditoria_active', 1)
            ->where('int_saksiam_editoria.int_saksiam_editoria_pin', 1);

        if ($typeFilter !== null) {
            $pinnedBuilder->where('int_saksiam_editoria.int_saksiam_editoria_typeID', $typeFilter);
        }

        $pinnedRows = $pinnedBuilder
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_approvedate', 'DESC')
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_creacteAt', 'DESC')
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_id', 'DESC')
            ->findAll($limit, 0);

        $remaining = $limit - count($pinnedRows);
        if ($remaining <= 0) {
            return $pinnedRows;
        }

        $latestBuilder = $this->baseSelect()
            ->where('int_saksiam_editoria.int_saksiam_editoria_active', 1)
            ->where('int_saksiam_typeeditoria.int_saksiam_Typeeditoria_active', 1)
            ->groupStart()
                ->where('int_saksiam_editoria.int_saksiam_editoria_pin !=', 1)
                ->orWhere('int_saksiam_editoria.int_saksiam_editoria_pin', null)
            ->groupEnd();

        if ($typeFilter !== null) {
            $latestBuilder->where('int_saksiam_editoria.int_saksiam_editoria_typeID', $typeFilter);
        }

        $latestRows = $latestBuilder
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_approvedate', 'DESC')
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_creacteAt', 'DESC')
            ->orderBy('int_saksiam_editoria.int_saksiam_editoria_id', 'DESC')
            ->findAll($remaining, 0);

        return array_merge($pinnedRows, $latestRows);
    }

    public function generateEditoriaNumber()
    {
        $prefix = 'ED' . date('ymd');
        $latest = $this->select('int_saksiam_editoria_num')
            ->like('int_saksiam_editoria_num', $prefix, 'after')
            ->orderBy('int_saksiam_editoria_id', 'DESC')
            ->first();

        $nextNumber = 1;
        if (!empty($latest['int_saksiam_editoria_num'])) {
            $nextNumber = ((int) substr($latest['int_saksiam_editoria_num'], -4)) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function baseSelect()
    {
        return $this->select('
                int_saksiam_editoria.*,
                int_saksiam_typeeditoria.int_saksiam_Typeeditoria_nameTH AS typeNameTH,
                int_saksiam_typeeditoria.int_saksiam_Typeeditoria_nameEN AS typeNameEN,
                int_saksiam_editoria.int_saksiam_editoria_id AS id,
                int_saksiam_editoria.int_saksiam_editoria_num AS editoriaNum,
                int_saksiam_editoria.int_saksiam_editoria_typeID AS typeID,
                int_saksiam_editoria.int_saksiam_editoria_titieTH AS titleTH,
                int_saksiam_editoria.int_saksiam_editoria_titieEN AS titleEN,
                int_saksiam_editoria.int_saksiam_editoria_descriptionTH AS descriptionTH,
                int_saksiam_editoria.int_saksiam_editoria_descriptionEN AS descriptionEN,
                int_saksiam_editoria.int_saksiam_editoria_gallary AS gallary,
                int_saksiam_editoria.int_saksiam_editoria_gallary AS gallery,
                int_saksiam_editoria.int_saksiam_editoria_pin AS pin,
                int_saksiam_editoria.int_saksiam_editoria_active AS active,
                int_saksiam_editoria.int_saksiam_editoria_createname AS createname,
                int_saksiam_editoria.int_saksiam_editoria_updatename AS updatename,
                int_saksiam_editoria.int_saksiam_editoria_approvedate AS approvedate,
                int_saksiam_editoria.int_saksiam_editoria_approvename AS approvename,
                int_saksiam_editoria.int_saksiam_editoria_note AS note,
                int_saksiam_editoria.int_saksiam_editoria_changetime AS changetime,
                int_saksiam_editoria.int_saksiam_editoria_chagename AS changename,
                int_saksiam_editoria.int_saksiam_editoria_improvement AS improvement,
                int_saksiam_editoria.int_saksiam_editoria_improvement AS improvement_text,
                int_saksiam_editoria.int_saksiam_editoria_improvement AS improvementText,
                int_saksiam_editoria.int_saksiam_editoria_cancellation AS cancellation,
                int_saksiam_editoria.int_saksiam_editoria_creacteAt AS createAt,
                int_saksiam_editoria.int_saksiam_editoria_updateAt AS updateAt
            ')
            ->join(
                'int_saksiam_typeeditoria',
                'int_saksiam_typeeditoria.int_saksiam_Typeeditorial_id = int_saksiam_editoria.int_saksiam_editoria_typeID',
                'left'
            );
    }
}
