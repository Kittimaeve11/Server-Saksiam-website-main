<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Controller;
use App\Models\BranchModel;
use App\Models\AreaModel;

class ControlleBranchlocations extends BaseController
{
    protected $branch;

    public function __construct()
    {
        $this->branch = new BranchModel();
        $this->area = new AreaModel();
    }
    
    public function showBranchlistdata()
    {
         $showData = $this->area->getbranch(); // ✔ เปลี่ยน Role → role

        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data Product brand retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

   public function showBranchData()
   {
        $activeFilter = $this->request->getGet('active');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        if ($activeFilter === "") {
            $activeFilter = null;
        } else {
            // Otherwise, cast it to integer for safety
            $activeFilter = (int) $activeFilter;
            // Ensure the activ7eFilter is either 0 or 1
            if (!in_array($activeFilter, [0, 1])) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Invalid active filter value'
                ])->setStatusCode(400);
            }
        }

        $branchs = $this->branch->getbranchData($activeFilter, $offset, $limit);

        if ($branchs) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $branchs,
            ]);
        } else {
            return $this->response->setJSON(0);
        }
   }

    public function showBranchWebsite()
    {
        $PROVINCEID = $this->request->getGet('provinceid');
        $LAT = $this->request->getGet('lat');           
        $LNG = $this->request->getGet('lng');

     // ✅ แปลงเป็น float หรือ null
        $LAT = is_numeric($LAT) ? (float)$LAT : null;
        $LNG = is_numeric($LNG) ? (float)$LNG : null;
        $PROVINCEID = $PROVINCEID ?: null;

        $branchs = $this->branch->getbranchWebsite($PROVINCEID, $LAT, $LNG);

        return $this->response->setJSON([
            'status' => 200,
            'data' => $branchs,
        ]);
    }


}