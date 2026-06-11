<?php

namespace App\Models;

use CodeIgniter\Model;

class VedioModel extends Model
{
    protected $table = 'int_saksiam_vedio';
    protected $primaryKey = 'int_saksiam_vedio_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_vedio_id',
        'int_saksiam_vedio_nameTH',
        'int_saksiam_vedio_link',
        'int_saksiam_vedio_youtubeID',
        'int_saksiam_vedio_active',
        'int_saksiam_vedio_createAt',
        'int_saksiam_vedio_updateAt',
        'int_saksiam_vedio_updatename',
        'int_saksiam_vedio_createname',
        'int_saksiam_vedio_approvedDate',
        'int_saksiam_vedio_approvedname',
        'int_saksiam_vedio_note',
        'int_saksiam_vedio_changname',
        'int_saksiam_vedio_changetime',
        'int_saksiam_vedio_improvement',
        'int_saksiam_vedio_creationdate',
        'int_saksiam_vedio_cancellation',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_vedio_createAt';
    protected $updatedField = 'int_saksiam_vedio_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_vedio_createAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_vedio_updateAt']);
        unset($data['data']['int_saksiam_vedio_approvedDate']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_vedio_updatename'])) {
            $data['data']['int_saksiam_vedio_updateAt'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_vedio_approvedDate']);
            unset($data['data']['int_saksiam_vedio_changetime']);
        }

        if (isset($data['data']['int_saksiam_vedio_approvedname'])) {
            $data['data']['int_saksiam_vedio_approvedDate'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_vedio_updateAt']);
            unset($data['data']['int_saksiam_vedio_changetime']);
        }

        if (isset($data['data']['int_saksiam_vedio_changname'])) {
            $data['data']['int_saksiam_vedio_changetime'] = date('Y-m-d H:i:s');
            unset($data['data']['int_saksiam_vedio_updateAt']);
            unset($data['data']['int_saksiam_vedio_approvedDate']);
        }

        unset($data['data']['int_saksiam_vedio_createAt']);

        return $data;
    }

    public function getVedioDateSearch($offset = 0, $limit = 50, $activeFilter = null, $startDate = null, $endDate = null)
    {
        $query = $this->baseSelect()
            ->where('int_saksiam_vedio_id >', 0)
            ->orderBy('int_saksiam_vedio_id', 'DESC');

        $this->applyFilters($query, $activeFilter, $startDate, $endDate);
        $resultQuery = $query->findAll($limit, $offset);

        $countQuery = $this->select('COUNT(int_saksiam_vedio_id) as total', false)
            ->where('int_saksiam_vedio_id >', 0);
        $this->applyFilters($countQuery, $activeFilter, $startDate, $endDate);
        $vedioCount = (int) ($countQuery->get()->getRow()->total ?? 0);

        $result = [];
        foreach ($resultQuery as $row) {
            $result[] = $this->formatRow($row);
        }

        return [
            'vedioCount' => $vedioCount,
            'vedio' => $result,
        ];
    }

    public function createVedioData(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data is required for insertion.');
        }

        if (empty($data[$this->primaryKey])) {
            $data[$this->primaryKey] = $this->getNextVedioId();
        }

        if (!$this->insert($data, false)) {
            throw new \RuntimeException('Failed to insert vedio data.');
        }

        return (int) $data[$this->primaryKey];
    }

    public function updateVedioData($vedioID, array $data)
    {
        if (!$this->find($vedioID)) {
            return false;
        }

        return $this->update($vedioID, $data);
    }

    public function approvedVedioData($vedioID, array $data)
    {
        if (!$this->find($vedioID)) {
            return false;
        }

        return $this->update($vedioID, $data);
    }

    public function showVedioDataID($vedioID = null)
    {
        $this->baseSelect();

        if ($vedioID !== null) {
            $row = $this->find($vedioID);
            return $row ? $this->formatRow($row) : null;
        }

        $rows = $this->where('int_saksiam_vedio_id >', 0)
            ->orderBy('int_saksiam_vedio_id', 'ASC')
            ->findAll();

        return array_map(fn ($row) => $this->formatRow($row), $rows);
    }

    public function checkDuplicateLink(string $link)
    {
        return $this->where('int_saksiam_vedio_link', $link)
            ->where('int_saksiam_vedio_id >', 0)
            ->countAllResults() > 0;
    }

    public function countActiveWaitVedios()
    {
        return $this->where('int_saksiam_vedio_active', 2)->countAllResults();
    }

    public function getReviewWebsite($offset = 0, $limit = 50)
    {
        $rows = $this->select('
            int_saksiam_vedio_id AS id,
            int_saksiam_vedio_nameTH AS nameTH,
            int_saksiam_vedio_link AS link,
            int_saksiam_vedio_youtubeID AS youtubeID,
            int_saksiam_vedio_creationdate AS creationdate
        ')
            ->where('int_saksiam_vedio_active', 1)
            ->where('int_saksiam_vedio_id >', 0)
            ->orderBy('int_saksiam_vedio_creationdate', 'DESC')
            ->findAll($limit, $offset);

        $count = (int) $this->where('int_saksiam_vedio_active', 1)
            ->where('int_saksiam_vedio_id >', 0)
            ->countAllResults();

        return [
            'total_count' => $count,
            'data' => $rows,
        ];
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_vedio_id,
            int_saksiam_vedio_nameTH,
            int_saksiam_vedio_link,
            int_saksiam_vedio_youtubeID,
            int_saksiam_vedio_active,
            int_saksiam_vedio_createAt,
            int_saksiam_vedio_updateAt,
            int_saksiam_vedio_updatename,
            int_saksiam_vedio_createname,
            int_saksiam_vedio_approvedDate,
            int_saksiam_vedio_approvedname,
            int_saksiam_vedio_note,
            int_saksiam_vedio_changname,
            int_saksiam_vedio_changetime,
            int_saksiam_vedio_improvement,
            int_saksiam_vedio_creationdate,
            int_saksiam_vedio_cancellation
        ');
    }

    private function applyFilters($query, $activeFilter = null, $startDate = null, $endDate = null): void
    {
        if ($activeFilter !== null) {
            if (is_array($activeFilter)) {
                $query->whereIn('int_saksiam_vedio_active', $activeFilter);
            } else {
                $query->where('int_saksiam_vedio_active', $activeFilter);
            }
        }

        if ($startDate && $endDate) {
            $query->where('int_saksiam_vedio_approvedDate >=', $startDate)
                ->where('int_saksiam_vedio_approvedDate <=', $endDate . ' 23:59:59');
        }
    }

    private function formatRow(array $row): array
    {
        return [
            'vedio_id' => $row['int_saksiam_vedio_id'],
            'nameTH_Vedio' => $row['int_saksiam_vedio_nameTH'],
            'vedio_link' => $row['int_saksiam_vedio_link'],
            'vedio_youtubeID' => $row['int_saksiam_vedio_youtubeID'],
            'vedio_active' => $row['int_saksiam_vedio_active'],
            'vedio_updatename' => $row['int_saksiam_vedio_updatename'],
            'vedio_createname' => $row['int_saksiam_vedio_createname'],
            'vedio_approvedname' => $row['int_saksiam_vedio_approvedname'],
            'vedio_approvedDate' => $row['int_saksiam_vedio_approvedDate'],
            'approvedDate' => $row['int_saksiam_vedio_approvedDate'],
            'approvedate' => $row['int_saksiam_vedio_approvedDate'],
            'vedio_note' => $row['int_saksiam_vedio_note'],
            'note' => $row['int_saksiam_vedio_note'],
            'rejectReason' => $row['int_saksiam_vedio_note'],
            'reason' => $row['int_saksiam_vedio_note'],
            'vedio_changename' => $row['int_saksiam_vedio_changname'],
            'vedio_changeTime' => $row['int_saksiam_vedio_changetime'],
            'vedio_improvement' => $row['int_saksiam_vedio_improvement'],
            'improvement' => $row['int_saksiam_vedio_improvement'],
            'improvement_text' => $row['int_saksiam_vedio_improvement'],
            'improvementText' => $row['int_saksiam_vedio_improvement'],
            'vedio_cancellation' => $row['int_saksiam_vedio_cancellation'],
            'cancellation' => $row['int_saksiam_vedio_cancellation'],
            'vedio_creationdate' => $row['int_saksiam_vedio_creationdate'],
            'vedio_createAT' => $row['int_saksiam_vedio_createAt'],
            'vedio_updateAT' => $row['int_saksiam_vedio_updateAt'],
            'producttype_ID' => null,
            'producttype_name' => null,
        ];
    }

    private function getNextVedioId(): int
    {
        $row = $this->builder()
            ->selectMax($this->primaryKey, 'max_id')
            ->get()
            ->getRowArray();

        return ((int) ($row['max_id'] ?? 0)) + 1;
    }
}
