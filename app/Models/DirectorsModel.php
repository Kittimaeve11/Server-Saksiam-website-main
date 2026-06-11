<?php

namespace App\Models;

use CodeIgniter\Model;

class DirectorsModel extends Model
{
    protected $table = 'int_saksiam_directors';
    protected $primaryKey = 'int_saksiam_directors_ID';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_directors__picture',
        'int_saksiam_directors_nameTH',
        'int_saksiam_directors_nameEN',
        'int_saksiam_directors_positionTH',
        'int_saksiam_directors_positionEN',
        'int_saksiam_directors_active',
        'int_saksiam_directors_createAt',
        'int_saksiam_directors_savename',
        'int_saksiam_directors_tag',
        'int_saksiam_directors_updateAt',
        'int_saksiam_directors_updatename',
        'int_saksiam_directors_changetime',
        'int_saksiam_directors_changename',
        'int_saksiam_directors_order',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_directors_createAt';
    protected $updatedField = 'int_saksiam_directors_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_directors_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_directors_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $hasStatusChange =
            array_key_exists('int_saksiam_directors_active', $data['data']) ||
            !empty($data['data']['int_saksiam_directors_changename'] ?? null);

        if ($hasStatusChange) {
            $data['data']['int_saksiam_directors_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_directors_updateAt']);
        } elseif (!empty($data['data']['int_saksiam_directors_updatename'] ?? null)) {
            $data['data']['int_saksiam_directors_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_directors_changetime']);
        }

        unset($data['data']['int_saksiam_directors_createAt']);

        return $data;
    }

    public function getDirectorsData($activeFilter = null, $offset = 0, $limit = 50, $tagFilter = null)
    {
        $builder = $this->baseSelect()
            ->orderBy('int_saksiam_directors_ID', 'DESC');

        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $builder->whereIn('int_saksiam_directors_active', $activeFilter);
            } else {
                $builder->where('int_saksiam_directors_active', $activeFilter);
            }
        }

        if ($tagFilter !== null) {
            $builder->where('int_saksiam_directors_tag', $tagFilter);
        }

        $query = $builder->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $countBuilder->whereIn('int_saksiam_directors_active', $activeFilter);
            } else {
                $countBuilder->where('int_saksiam_directors_active', $activeFilter);
            }
        }

        if ($tagFilter !== null) {
            $countBuilder->where('int_saksiam_directors_tag', $tagFilter);
        }

        $rows = [];
        foreach ($query as $row) {
            $rows[] = [
                'team_ID' => $row['int_saksiam_directors_ID'],
                'team_nameTH' => $row['int_saksiam_directors_nameTH'],
                'team_nameEN' => $row['int_saksiam_directors_nameEN'],
                'team_picture' => $row['int_saksiam_directors__picture'],
                'team_positionTH' => $row['int_saksiam_directors_positionTH'],
                'team_positionEN' => $row['int_saksiam_directors_positionEN'],
                'team_active' => $row['int_saksiam_directors_active'],
                'team_createAt' => $row['int_saksiam_directors_createAt'],
                'team_savename' => $row['int_saksiam_directors_savename'],
                'team_updateAt' => $row['int_saksiam_directors_updateAt'],
                'team_updatename' => $row['int_saksiam_directors_updatename'],
                'team_order' => $row['int_saksiam_directors_order'],
            ];
        }

        $count = (int) $countBuilder->countAllResults();

        return [
            'counts' => $count,
            'directors' => $query,
            'committees' => $query,
            'companydirectorcount' => $count,
            'companydirectorcounts' => $rows,
        ];
    }

    public function createDirectors($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        $data['int_saksiam_directors_createAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updateDirectors($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showDirectors($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            return $this->find($id);
        }

        return $this
            ->where('int_saksiam_directors_active', 1)
            ->orderBy('int_saksiam_directors_order', 'ASC')
            ->orderBy('int_saksiam_directors_ID', 'ASC')
            ->findAll();
    }

    public function updateDirectorsOrder($orderData)
    {
        if (empty($orderData)) {
            return false;
        }

        $allSuccess = true;

        foreach ($orderData as $item) {
            $id = $item['int_saksiam_directors_ID'] ?? $item['id'] ?? null;
            $order = $item['int_saksiam_directors_order'] ?? $item['order'] ?? null;

            if ($id === null || $order === null) {
                continue;
            }

            if (!$this->update($id, ['int_saksiam_directors_order' => $order])) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function getTeamsMove()
    {
        return $this->select('
            int_saksiam_directors_ID AS companydirectorID,
            int_saksiam_directors__picture AS companydirector_picture,
            int_saksiam_directors_nameTH AS companydirector_nameTH,
            int_saksiam_directors_nameEN AS companydirector_nameEN,
            int_saksiam_directors_active AS companydirector_active
        ')
            ->whereIn('int_saksiam_directors_active', [0, 1])
            ->orderBy('int_saksiam_directors_order', 'ASC')
            ->orderBy('int_saksiam_directors_ID', 'ASC')
            ->findAll();
    }

    public function getTeamWebsite()
    {
        return $this->select('
            int_saksiam_directors_ID AS teamsID,
            int_saksiam_directors__picture AS teams_picture,
            int_saksiam_directors_nameTH AS teams_nameTH,
            int_saksiam_directors_nameEN AS teams_nameEN,
            int_saksiam_directors_positionTH AS teams_positionTH,
            int_saksiam_directors_positionEN AS teams_positionEN,
            int_saksiam_directors_active AS active,
            int_saksiam_directors_createAt AS createAt,
            int_saksiam_directors_savename AS savename,
            int_saksiam_directors_tag AS tag,
            int_saksiam_directors_updateAt AS updateAt,
            int_saksiam_directors_updatename AS updatename,
            int_saksiam_directors_changetime AS changetime,
            int_saksiam_directors_changename AS changename,
            int_saksiam_directors_order AS teams_order
        ')
            ->where('int_saksiam_directors_active', 1)
            ->groupBy('int_saksiam_directors_ID, int_saksiam_directors_order')
            ->orderBy('int_saksiam_directors_order', 'ASC')
            ->orderBy('int_saksiam_directors_ID', 'ASC')
            ->findAll();
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_directors.*,
            int_saksiam_directors_ID AS id,
            int_saksiam_directors_ID AS directorsID,
            int_saksiam_directors_ID AS directorID,
            int_saksiam_directors__picture AS picture,
            int_saksiam_directors__picture AS directors_picture,
            int_saksiam_directors_nameTH AS nameTH,
            int_saksiam_directors_nameEN AS nameEN,
            int_saksiam_directors_positionTH AS positionTH,
            int_saksiam_directors_positionEN AS positionEN,
            int_saksiam_directors_active AS active,
            int_saksiam_directors_createAt AS createAt,
            int_saksiam_directors_savename AS savename,
            int_saksiam_directors_tag AS tag,
            int_saksiam_directors_updateAt AS updateAt,
            int_saksiam_directors_updatename AS updatename,
            int_saksiam_directors_changetime AS changetime,
            int_saksiam_directors_changename AS changename,
            int_saksiam_directors_order AS `order`,
            int_saksiam_directors_order AS directorsorder
        ');
    }
}
