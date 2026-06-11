<?php

namespace App\Models;

use CodeIgniter\Model;

class MissionModel extends Model
{
    protected $table = 'int_saksiam_mission';
    protected $primaryKey = 'int_saksiam_mission_ID';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_mission_titleTH',
        'int_saksiam_mission_titleEN',
        'int_saksiam_mission_topicTH',
        'int_saksiam_mission_topicEN',
        'int_saksiam_mission_active',
        'int_saksiam_mission_createAt',
        'int_saksiam_mission_savename',
        'int_saksiam_mission_updateAt',
        'int_saksiam_mission_updatename',
        'int_saksiam_mission_changetime',
        'int_saksiam_mission_changename',
        'int_saksiam_mission_picture',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_mission_createAt';
    protected $updatedField = 'int_saksiam_mission_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_mission_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_mission_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_mission_updatename'])) {
            $data['data']['int_saksiam_mission_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_mission_changetime']);
        }

        if (isset($data['data']['int_saksiam_mission_changename'])) {
            $data['data']['int_saksiam_mission_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_mission_updateAt']);
        }

        unset($data['data']['int_saksiam_mission_createAt']);

        return $data;
    }

    public function getMissionData($activeFilter = null, $offset = 0, $limit = 50)
    {
        $builder = $this->baseSelect()
            ->orderBy('int_saksiam_mission_ID', 'DESC');

        if ($activeFilter !== null) {
            $builder->where('int_saksiam_mission_active', $activeFilter);
        }

        $query = $builder->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($activeFilter !== null) {
            $countBuilder->where('int_saksiam_mission_active', $activeFilter);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'missions' => $query,
        ];
    }

    public function createMissionData(array $data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required for insertion');
        }

        $data['int_saksiam_mission_createAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function getMissionTotalCount(): int
    {
        return (int) $this->builder()->countAllResults();
    }

    public function updateMissionData($missionID, array $data)
    {
        if (!$this->find($missionID)) {
            return false;
        }

        return $this->update($missionID, $data);
    }

    public function showMissionID($missionID = null)
    {
        $this->baseSelect();

        if ($missionID !== null) {
            return $this->find($missionID);
        }

        return $this
            ->orderBy('int_saksiam_mission_ID', 'ASC')
            ->findAll();
    }

    public function getMissionWebsite()
    {
        return $this->select('
            int_saksiam_mission_ID AS mission_ID,
            int_saksiam_mission_titleTH AS titleTH,
            int_saksiam_mission_titleEN AS titleEN,
            int_saksiam_mission_topicTH AS topicTH,
            int_saksiam_mission_topicEN AS topicEN,
            int_saksiam_mission_picture AS picture
        ')
            ->where('int_saksiam_mission_active', 1)
            ->groupBy('int_saksiam_mission_ID')
            ->orderBy('int_saksiam_mission_ID', 'ASC')
            ->findAll();
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_mission_ID,
            int_saksiam_mission_titleTH,
            int_saksiam_mission_titleEN,
            int_saksiam_mission_topicTH,
            int_saksiam_mission_topicEN,
            int_saksiam_mission_active,
            int_saksiam_mission_createAt,
            int_saksiam_mission_savename,
            int_saksiam_mission_updateAt,
            int_saksiam_mission_updatename,
            int_saksiam_mission_changetime,
            int_saksiam_mission_changename,
            int_saksiam_mission_picture,
            int_saksiam_mission_ID AS mission_ID,
            int_saksiam_mission_ID AS id,
            int_saksiam_mission_titleTH AS titleTH,
            int_saksiam_mission_titleEN AS titleEN,
            int_saksiam_mission_topicTH AS topicTH,
            int_saksiam_mission_topicEN AS topicEN,
            int_saksiam_mission_active AS active,
            int_saksiam_mission_createAt AS createAt,
            int_saksiam_mission_savename AS savename,
            int_saksiam_mission_updateAt AS updateAt,
            int_saksiam_mission_updatename AS updatename,
            int_saksiam_mission_changetime AS changetime,
            int_saksiam_mission_changename AS changename,
            int_saksiam_mission_picture AS picture
        ');
    }
}
