<?php

namespace App\Models;

use CodeIgniter\Model;

class FqaModel extends Model
{
    protected $table = 'int_saksiam_fqa';
    protected $primaryKey = 'int_saksiam_fqa_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_fqa_id',
        'int_saksiam_fqa_questionTH',
        'int_saksiam_fqa_questionEN',
        'int_saksiam_fqa_answersTH',
        'int_saksiam_fqa_answersEN',
        'int_saksiam_fqa_type',
        'int_saksiam_fqa_active',
        'int_saksiam_fqa_savename',
        'int_saksiam_fqa_updatename',
        'int_saksiam_fqa_changname',
        'int_saksiam_fqa_changetime',
        'int_saksiam_fqa_order',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'int_saksiam_fqa_createAt';
    protected $updatedField = 'int_saksiam_fqa_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_fqa_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_fqa_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasActiveChange = array_key_exists('int_saksiam_fqa_active', $data['data']);
        $hasContentChange =
            array_key_exists('int_saksiam_fqa_type', $data['data']) ||
            array_key_exists('int_saksiam_fqa_questionTH', $data['data']) ||
            array_key_exists('int_saksiam_fqa_questionEN', $data['data']) ||
            array_key_exists('int_saksiam_fqa_answersTH', $data['data']) ||
            array_key_exists('int_saksiam_fqa_answersEN', $data['data']);
        $hasUpdateName = !empty($data['data']['int_saksiam_fqa_updatename'] ?? null);

        if ($hasActiveChange) {
            $data['data']['int_saksiam_fqa_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_fqa_updateAt']);
        } elseif ($hasContentChange || $hasUpdateName) {
            $data['data']['int_saksiam_fqa_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_fqa_changetime']);
        }

        unset($data['data']['int_saksiam_fqa_createAt']);

        return $data;
    }

    public function getFqaData($activeFilter = null, $offset = 0, $limit = 50, $typeFilter = null)
    {
        $builder = $this->baseSelect()
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_order', 'ASC')
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_id', 'DESC');

        if ($activeFilter !== null) {
            $builder->where('int_saksiam_fqa.int_saksiam_fqa_active', $activeFilter);
        }

        if ($typeFilter !== null) {
            $builder->where('int_saksiam_fqa.int_saksiam_fqa_type', $typeFilter);
        }

        $query = $builder->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            $countBuilder->where('int_saksiam_fqa_active', $activeFilter);
        }

        if ($typeFilter !== null) {
            $countBuilder->where('int_saksiam_fqa_type', $typeFilter);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'fqas' => $query,
            'faqquestions' => $query,
            'faqQuestions' => $query,
            'questions' => $query,
        ];
    }

    public function createFqa($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        $data['int_saksiam_fqa_createAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updateFqa($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showFqa($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            return $this->find($id);
        }

        return $this
            ->where('int_saksiam_fqa.int_saksiam_fqa_active', 1)
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_order', 'ASC')
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_id', 'ASC')
            ->findAll();
    }

    public function getFqaMove()
    {
            return $this->select('
                int_saksiam_fqa.int_saksiam_fqa_id AS fqaID,
                int_saksiam_fqa.int_saksiam_fqa_type AS faqtypeID,
                int_saksiam_fqa.int_saksiam_fqa_questionTH AS questionTH,
                int_saksiam_fqa.int_saksiam_fqa_questionEN AS questionEN,
                int_saksiam_fqa.int_saksiam_fqa_active AS active,
                int_saksiam_fqa.int_saksiam_fqa_order AS fqaorder
            ')
            ->whereIn('int_saksiam_fqa.int_saksiam_fqa_active', [0, 1])
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_order', 'ASC')
            ->orderBy('int_saksiam_fqa.int_saksiam_fqa_id', 'ASC')
            ->findAll();
    }

    public function updateFqaOrder($orderData)
    {
        if (empty($orderData)) {
            return false;
        }

        $allSuccess = true;

        foreach ($orderData as $item) {
            if (!isset($item['int_saksiam_fqa_id'], $item['int_saksiam_fqa_order'])) {
                continue;
            }

            if (!$this->update($item['int_saksiam_fqa_id'], [
                'int_saksiam_fqa_order' => $item['int_saksiam_fqa_order'],
            ])) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    private function baseSelect()
    {
        return $this->select('
                int_saksiam_fqa.int_saksiam_fqa_id,
                int_saksiam_fqa.int_saksiam_fqa_type,
                int_saksiam_fqa.int_saksiam_fqa_questionTH,
                int_saksiam_fqa.int_saksiam_fqa_questionEN,
                int_saksiam_fqa.int_saksiam_fqa_answersTH,
                int_saksiam_fqa.int_saksiam_fqa_answersEN,
                int_saksiam_fqa.int_saksiam_fqa_active,
                int_saksiam_fqa.int_saksiam_fqa_savename,
                int_saksiam_fqa.int_saksiam_fqa_createAt,
                int_saksiam_fqa.int_saksiam_fqa_updateAt,
                int_saksiam_fqa.int_saksiam_fqa_order,
                int_saksiam_typefqa.int_saksiam_typefqa_nameTH,
                int_saksiam_typefqa.int_saksiam_typefqa_nameEN,
                int_saksiam_fqa.int_saksiam_fqa_id AS fqaID,
                int_saksiam_fqa.int_saksiam_fqa_id AS faqQuestionID,
                int_saksiam_fqa.int_saksiam_fqa_id AS id,
                int_saksiam_fqa.int_saksiam_fqa_type AS faqtypeID,
                int_saksiam_fqa.int_saksiam_fqa_type AS typeID,
                int_saksiam_typefqa.int_saksiam_typefqa_nameTH AS faqtypeNameTH,
                int_saksiam_typefqa.int_saksiam_typefqa_nameEN AS faqtypeNameEN,
                int_saksiam_typefqa.int_saksiam_typefqa_nameTH AS typeNameTH,
                int_saksiam_typefqa.int_saksiam_typefqa_nameEN AS typeNameEN,
                int_saksiam_fqa.int_saksiam_fqa_questionTH AS questionTH,
                int_saksiam_fqa.int_saksiam_fqa_questionEN AS questionEN,
                int_saksiam_fqa.int_saksiam_fqa_answersTH AS answerTH,
                int_saksiam_fqa.int_saksiam_fqa_answersEN AS answerEN,
                int_saksiam_fqa.int_saksiam_fqa_answersTH AS answersTH,
                int_saksiam_fqa.int_saksiam_fqa_answersEN AS answersEN,
                int_saksiam_fqa.int_saksiam_fqa_active AS active,
                int_saksiam_fqa.int_saksiam_fqa_savename AS savename,
                int_saksiam_fqa.int_saksiam_fqa_createAt AS createAt,
                int_saksiam_fqa.int_saksiam_fqa_updateAt AS updateAt,
                int_saksiam_fqa.int_saksiam_fqa_order AS fqaorder
            ')
            ->join(
                'int_saksiam_typefqa',
                'int_saksiam_typefqa.int_saksiam_typefqa_id = int_saksiam_fqa.int_saksiam_fqa_type',
                'left'
            );
    }
}
