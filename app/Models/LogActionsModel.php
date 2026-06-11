<?php

namespace App\Models;

use CodeIgniter\Model;

class LogActionsModel extends Model
{
    protected $table            = 'int_saksiam_log';
    protected $primaryKey       = 'int_saksiam_log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_log_id',
        'int_saksiam_log_ActionType',
        'int_saksiam_log_ActionDetail',
        'int_saksiam_log_TypeUser',
        'int_saksiam_log_IPAddress',
        'int_saksiam_log_datatype',
        'int_saksiam_log_Device',
        'int_saksiam_log_dataID',
        'int_saksiam_log_datatypeID',
        'int_saksiam_log_dataname',
        'int_saksiam_log_FullNamePer',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_log_DatetimeActions';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function insert_data($data)
    {
        $this->save($data);
        return $this->getInsertID();
    }

    public function search_data($typeFilter = null, $limit, $offset, $searchdetail = null, $month = null, $year = null)
    {
         $query = $this->select('
         int_saksiam_log_id,
         int_saksiam_log_ActionType,
         int_saksiam_log_ActionDetail,
         int_saksiam_log_TypeUser,
         int_saksiam_log_IPAddress,
         int_saksiam_log_datatype,
         int_saksiam_log_Device,
         int_saksiam_log_dataID,
         int_saksiam_log_datatypeID,
         int_saksiam_log_dataname,
         int_saksiam_log_FullNamePer,
         int_saksiam_log_DatetimeActions
         ')
            ->orderBy('int_saksiam_log_id', 'DESC');
             if ($typeFilter) {
            $query->where('int_saksiam_log_ActionType', $typeFilter);
        }

        if ($searchdetail) {
            $query->groupStart()
                ->orLike('int_saksiam_log_FullNamePer', $searchdetail)
                ->orLike('int_saksiam_log_IPAddress', $searchdetail)
                ->groupEnd();
        }
         if ($month) {
            $query->where('MONTH(int_saksiam_log_DatetimeActions)', $month);
        }
        if ($year) {
            $query->where('YEAR(int_saksiam_log_DatetimeActions)', $year);
        }
        $resultQuery = $query->findAll($limit, $offset);
        $countQuery = $this->select('COUNT(int_saksiam_log_id) as total', false);
        if ($typeFilter) {
            $countQuery->where('int_saksiam_log_ActionType', $typeFilter);
        }

        if ($searchdetail) {
            $countQuery->groupStart()
                ->like('int_saksiam_log_dataname', $searchdetail)
                ->orLike('int_saksiam_log_FullNamePer', $searchdetail)
                ->orLike('int_saksiam_log_IPAddress', $searchdetail)
                ->groupEnd();
        }

        if ($month) {
            $countQuery->where('MONTH(int_saksiam_log_DatetimeActions)', $month);
        }
        if ($year) {
            $countQuery->where('YEAR(int_saksiam_log_DatetimeActions)', $year);
        }

          $logCount = (int) $countQuery->get()->getRow()->total;
        $result = [];
        foreach ($resultQuery as $row) {
            $rowData = [
                'id' => $row['int_saksiam_log_id'],
                'actionType' => $row['int_saksiam_log_ActionType'],
                'actionDetail' => $row['int_saksiam_log_ActionDetail'],
                'typeUser' => $row['int_saksiam_log_TypeUser'],
                'IPAddress' => $row['int_saksiam_log_IPAddress'],
                'datatype' => $row['int_saksiam_log_datatype'],
                'device' => $row['int_saksiam_log_Device'],
                'dataID' => $row['int_saksiam_log_dataID'],
                'datatypeID' => $row['int_saksiam_log_datatypeID'],
                'dataname' => $row['int_saksiam_log_dataname'],
                'FullNamePer' => $row['int_saksiam_log_FullNamePer'],
                'datetimeActions' => $row['int_saksiam_log_DatetimeActions']
            ];
            $result[] = $rowData;
        }
        return [
            'log' => $logCount,
            'logs' => $result
        ];
    }

    public function getLogData($typeFilter = null, $datatype = null, $dataID = null, $searchdetail = null, $month = null, $year = null, $limit = 0, $offset = 0)
    {
        $query = $this->select('
            int_saksiam_log_id,
            int_saksiam_log_ActionType,
            int_saksiam_log_ActionDetail,
            int_saksiam_log_TypeUser,
            int_saksiam_log_IPAddress,
            int_saksiam_log_datatype,
            int_saksiam_log_Device,
            int_saksiam_log_dataID,
            int_saksiam_log_datatypeID,
            int_saksiam_log_dataname,
            int_saksiam_log_FullNamePer,
            int_saksiam_log_DatetimeActions
        ')
            ->orderBy('int_saksiam_log_id', 'DESC');

        if ($typeFilter !== null && $typeFilter !== '') {
            $query->where('int_saksiam_log_ActionType', $typeFilter);
        }

        if ($datatype !== null && $datatype !== '') {
            $query->where('int_saksiam_log_datatype', $datatype);
        }

        if ($dataID !== null && $dataID !== '') {
            $query->where('int_saksiam_log_dataID', $dataID);
        }

        if ($searchdetail !== null && $searchdetail !== '') {
            $query->groupStart()
                ->like('int_saksiam_log_dataID', $searchdetail)
                ->orLike('int_saksiam_log_dataname', $searchdetail)
                ->orLike('int_saksiam_log_FullNamePer', $searchdetail)
                ->orLike('int_saksiam_log_IPAddress', $searchdetail)
                ->groupEnd();
        }

        if ($month !== null && $month !== '') {
            $query->where('MONTH(int_saksiam_log_DatetimeActions)', $month);
        }

        if ($year !== null && $year !== '') {
            $query->where('YEAR(int_saksiam_log_DatetimeActions)', $year);
        }

        $rows = ((int) $limit > 0)
            ? $query->findAll((int) $limit, (int) $offset)
            : $query->findAll();

        return array_map(function ($row) {
            return [
                'id' => $row['int_saksiam_log_id'],
                'actionType' => $row['int_saksiam_log_ActionType'],
                'actionDetail' => $row['int_saksiam_log_ActionDetail'],
                'typeUser' => $row['int_saksiam_log_TypeUser'],
                'IPAddress' => $row['int_saksiam_log_IPAddress'],
                'datatype' => $row['int_saksiam_log_datatype'],
                'device' => $row['int_saksiam_log_Device'],
                'dataID' => $row['int_saksiam_log_dataID'],
                'datatypeID' => $row['int_saksiam_log_datatypeID'],
                'dataname' => $row['int_saksiam_log_dataname'],
                'FullNamePer' => $row['int_saksiam_log_FullNamePer'],
                'datetimeActions' => $row['int_saksiam_log_DatetimeActions'],
                'int_saksiam_log_DatetimeActions' => $row['int_saksiam_log_DatetimeActions'],
            ];
        }, $rows);
    }

    public function viewLoanGraph( $limit, $offset, $startDate = null, $endDate = null)
    {
        $currentYear = date('Y');
        if (!$startDate || !$endDate) {
            $startDate = "$currentYear-01-01";
            $endDate = "$currentYear-12-31";
        }
        $limit = (int) $limit;
        $offset = (int) $offset;
          $startDateObj = new \DateTime($startDate);
        $endDateObj = new \DateTime($endDate);
        $interval = $startDateObj->diff($endDateObj);
        $monthsDifference = $interval->y * 12 + $interval->m;

        $previousStartDate = $startDateObj->sub(new \DateInterval("P{$monthsDifference}M"))->format('Y-m-d');
        $previousEndDate = $endDateObj->sub(new \DateInterval("P{$monthsDifference}M"))->format('Y-m-d');

        $query = $this->selete('
        int_saksiam_loan.int_saksiam_loan_id As loan_id,
        int_saksiam_loan.int_saksiam_loan_titleTH As loan_titleTH,
        int_saksiam_loan.int_saksiam_loan_imagelarge,
        int_saksiam_loan.int_saksiam_loan_minamount,
        int_saksiam_loan.int_saksiam_loan_maxamount,
        int_saksiam_loan.int_saksiam_loan_active,
          COUNT(CASE WHEN int_saksiam_log.int_saksiam_log_ActionType = 1 THEN 1 ELSE NULL END) as coutloan
        ')
          ->join('int_saksiam_loan', 'int_saksiam_loan.int_saksiam_loan_id = int_saksiam_log.int_saksiam_log_id', 'left')
          ->where('int_saksiam_loan.int_saksiam_log_DatetimeActions >=', $startDate)
          ->where('int_saksiam_loan.int_saksiam_log_DatetimeActions <=', $endDate . ' 23:59:59')
          ->having('int_saksiam_loan.int_saksiam_loan_titleTH IS NOT NULL')
          ->groupBy('int_saksiam_loan.int_saksiam_loan_id')
          ->orderBy('coutloan', 'DESC')
          ->limit($limit, $offset);
        $currentData = $query->asObject()->findAll();

        $countQuery = $this->select('COUNT(DISTINCT int_saksiam_loan.int_saksiam_loan_id) as total')
             ->join('int_saksiam_loan', 'int_saksiam_loan.int_saksiam_loan_id = int_saksiam_log.int_saksiam_log_id', 'left')
            ->where('int_saksiam_log.int_saksiam_log_DatetimeActions >=', $startDate)
            ->where('int_saksiam_log.int_saksiam_log_DatetimeActions <=', $endDate . ' 23:59:59');

        $loanCountResult = $countQuery->get()->getRow();
        $loanCount = $loanCountResult ? (int) $loanCountResult->total : 0;
        $previousQuery = clone $query;
        $previousQuery->where('int_saksiam_log.int_saksiam_log_DatetimeActions  >=', $previousStartDate)
            ->where('int_saksiam_log.int_saksiam_log_DatetimeActions  <=', $previousEndDate);

        $previousData = $previousQuery->asObject()->findAll();

        $previousStats = [];
        foreach ($previousData as $prev) {
            if (isset($prev->loan_id)) {
                $previousStats[$prev->loan_id] = [
                    'coutloan' => $prev->coutloan ?? 0,
                ];
            }
        }
        foreach ($currentData as &$current) {
            $prevloanViews = $previousStats[$current->loan_id]['coutloan'] ?? 0;

            $current->loan_growth = ($prevloanViews > 0)
                ? (($current->coutloan - $prevloanViews) / $prevloanViews) * 100
                : ($current->coutloan > 0 ? 100 : 0);


            $current->loan_growth = round($current->loan_growth, 2);

            $chartQuery = $this->select('
                COUNT(CASE WHEN int_saksiam_log.int_saksiam_log_DatetimeActions = 1 THEN 1 END) AS total_views')
                ->where('int_saksiam_log.int_saksiam_log_DatetimeActions', $current->loan_id);

            if ($interval->days == 0) {
                $chartQuery->select('HOUR(int_saksiam_log.int_saksiam_log_DatetimeActions) as time_group')
                    ->groupBy('HOUR(int_saksiam_log.int_saksiam_log_DatetimeActions)');
                $labelsType = 'hourly';
            } elseif ($interval->days < 30) {
                $chartQuery->select('DAY(int_saksiam_log.int_saksiam_log_DatetimeActions) as time_group')
                    ->groupBy('DAY(int_saksiam_log.int_saksiam_log_DatetimeActions)');
                $labelsType = 'daily';
            } elseif ($interval->m < 4) {
                $chartQuery->select('WEEK(int_saksiam_log.int_saksiam_log_DatetimeActions) as time_group')
                    ->groupBy('WEEK(int_saksiam_log.int_saksiam_log_DatetimeActions)');
                $labelsType = 'weekly';
            } else {
                $chartQuery->select('MONTH(int_saksiam_log.int_saksiam_log_DatetimeActions) as time_group')
                    ->groupBy('MONTH(int_saksiam_log.int_saksiam_log_DatetimeActions)');
                $labelsType = 'monthly';
            }

            $chartQuery->where('int_saksiam_log.int_saksiam_log_DatetimeActions >=', $startDate)
                ->where('int_saksiam_log.int_saksiam_log_DatetimeActions <=', $endDate . ' 23:59:59');

            $chartResult = $chartQuery->findAll();

            $formattedLabels = [];
            foreach ($chartResult as $data) {
                if ($labelsType === 'hourly') {
                    $formattedLabels[] = $data['time_group'] . ':00';
                } elseif ($labelsType === 'daily') {
                    $formattedLabels[] = 'วันที่ ' . $data['time_group'];
                } elseif ($labelsType === 'weekly') {
                    $formattedLabels[] = 'สัปดาห์ที่ ' . $data['time_group'];
                } elseif ($labelsType === 'monthly') {
                    $monthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                    $formattedLabels[] = $monthNames[$data['time_group'] - 1];
                }
            }

            $current->chartData = [
                'labels' => $formattedLabels,
                'labels_type' => $labelsType,
                'total_views' => array_column($chartResult, 'total_views'),

            ];
        }
        return ['totalloan' => $loanCount, 'currentData' => $currentData];
    }


}
