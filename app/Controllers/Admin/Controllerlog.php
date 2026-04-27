<?php

namespace App\Controllers\Admin;
use CodeIgniter\Controller;


use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\LogActionsModel;


class Controllerlog extends BaseController
{
      protected $LogActionsModel;
 public function __construct()
    {
        $this->LogActions = new LogActionsModel();
    }

        protected function getUserIp(): string
    {
        $ip = $this->request->getServer('HTTP_X_FORWARDED_FOR');
        return $ip ?: $this->request->getIPAddress();
    }

    protected function getDeviceFromUserAgent($agent): string
    {
        if ($agent->isBrowser()) {
            $browser = $agent->getBrowser() . ' ' . $agent->getVersion();
            if ($agent->isMobile()) {
                return $browser . ' on ' . $agent->getMobile();
            }
            return $browser;
        } elseif ($agent->isRobot()) {
            return $agent->getRobot();
        } else {
            return 'Unidentified User Agent';
        }
    }


    public function uplogdata()
    {
        $jsonData = $this->request->getJSON(true);
        $actionType = $jsonData['actionType'] ?? null;
        $actionDetail = $jsonData['actionDetail'] ?? null;
        $typeUser = $jsonData['typeUser'] ?? null;
        $datatype = $jsonData['datatype'] ?? null;
        $dataID = $jsonData['dataID'] ?? null;
        $datatypeID = $jsonData['datatypeID'] ?? null;
        $dataname = $jsonData['dataname'] ?? null;
        $FullNamePer = $jsonData['FullPer'] ?? null;

        if (!$actionType || !$actionDetail || !$typeUser || !$datatype || !$FullNamePer) {
            return $this->response->setJSON(['error' => 'กรุณากรอกข้อมูลที่จำเป็น'])
                ->setStatusCode(400);
        }

        try {
            $logData = [
                'int_saksiam_log_ActionType' => $actionType,
                'int_saksiam_log_ActionDetail' => $actionDetail,
                'int_saksiam_log_TypeUser' => $typeUser,
                'int_saksiam_log_datatype' => $datatype,
                'int_saksiam_log_dataID' => $dataID,
                'int_saksiam_log_datatypeID' => $datatypeID,
                'int_saksiam_log_dataname' => $dataname,
                'int_saksiam_log_FullNamePer' => $FullNamePer,
                'int_saksiam_log_IPAddress' => $this->getUserIp(),
                'int_saksiam_log_Device' => $this->getDeviceFromUserAgent($this->request->getUserAgent()),
            ];
            $this->LogActions->insert_data($logData);
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data inserted successfully',
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }

    }

}
