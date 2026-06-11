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

            $collator = new \Collator('th_TH'); // 👈 ภาษาไทย

            usort($showData, function ($a, $b) use ($collator) {
                return $collator->compare($a['name'], $b['name']);
            });

            return $this->response->setJSON([
            'status' => true,
            'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }
    
   public function showBranchlistdataapi()
    {
        $showData = $this->area->getbranch(); // ✔ เปลี่ยน Role → role

        if ($showData) {

            $collator = new \Collator('th_TH'); // 👈 ภาษาไทย

            usort($showData, function ($a, $b) use ($collator) {
                return $collator->compare($a['name'], $b['name']);
            });

            return $this->response->setJSON([
            'status' => true,
            'result' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }


    public function upBranchDataAPI()
    {
        $jsonData = $this->request->getJSON(true);

        $branchType = $jsonData['branchType'] ?? null;
        $branchname = $jsonData['branchname'] ?? null;
        $businessSector = $jsonData['businessSector'] ?? null;
        $region = $jsonData['region'] ?? null;
        $address = $jsonData['address'] ?? null;

        $districtID = $jsonData['districtID'] ?? null;
        $district = $jsonData['district'] ?? null;
        $amphoeID = $jsonData['amphoeID'] ?? null;
        $amphoe = $jsonData['amphoe'] ?? null;
        $provinceID = $jsonData['provinceID'] ?? null;
        $province = $jsonData['province'] ?? null;

        $zipcode = $jsonData['zipcode'] ?? null;
        $landmark = $jsonData['landmark'] ?? null;
        $phone = $jsonData['phone'] ?? null;
        $lat = $jsonData['lat'] ?? null;
        $lag = $jsonData['lag'] ?? null;

        $savename = $jsonData['savename'] ?? null;
        $active = $jsonData['active'] ?? 1; // default = 1

        // ✅ validation แบบถูก
        if (
            $branchType === null ||
            $branchname === null ||
            $businessSector === null ||
            $region === null ||
            $address === null ||
            $district === null ||
            $amphoe === null ||
            $province === null ||
            $zipcode === null ||
            $landmark === null ||
            $phone === null ||
            $lat === null ||
            $lag === null
        ) {
            return $this->response->setJSON([
                'error' => 'กรุณากรอกข้อมูลที่จำเป็น'
            ])->setStatusCode(400);
        }

        // ✅ duplicate check (เช็คชื่อพอ)
         $duplicate = $this->branch
            ->where('int_saksiam_branch_name', $branchname)
            ->first();

        if ($duplicate) {
            return $this->response->setJSON([
            'status' => false,
            'message' => 'ชื่อสาขา/หน่วย/สำนักงานนี้มีอยู่ในระบบแล้ว'
            ])->setStatusCode(400);
         }

         try {
            $newBranchID = $this->branch->generateBranchID();
            $branchData = [
                'int_saksiam_branch_id' => $newBranchID,
                'int_saksiam_branch_type' => $branchType,
                'int_saksiam_branch_name' => $branchname,
                'int_saksiam_branch_areaid' => $region,
                'int_saksiam_branch_regionid' => $businessSector,
                'int_saksiam_branch_address' => $address,
                'int_saksiam_branch_DISTRICTID' => $districtID,
                'int_saksiam_branch_DISTRICTNAME' => $district,
                'int_saksiam_branch_AMPHURID' => $amphoeID,
                'int_saksiam_branch_AMPHURNAME' => $amphoe,
                'int_saksiam_branch_PROVINCEID' => $provinceID,
                'int_saksiam_branch_PROVINCENAME' => $province,
                'int_saksiam_branch_zipcode' => $zipcode,
                'int_saksiam_branch_detail' => $landmark,
                'int_saksiam_branch_tel' => $phone,
                'int_saksiam_branch_lat' => $lat,
                'int_saksiam_branch_lng' => $lag,
                'int_saksiam_branch_status' => $active,
                'int_saksiam_branch_savename' => $savename,
            ];

            $branchID = $this->branch->createBranchData($branchData);

            return $this->response->setJSON([
                'message' => 'created successfully',
                'int_saksiam_branch_id' => $branchID,
                'branch_data' => $branchData
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            return $this->response->setJSON([
            'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

   public function showBranchData()
   {
        $activeFilter = $this->request->getGet('active');
        $selectedType = $this->request->getVar('selectedType');
        $selectedRegion = $this->request->getVar('selectedRegion');
        $selectedBusinessSector = $this->request->getVar('selectedBusinessSector');
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

        $branchs = $this->branch->getbranchData($activeFilter,$selectedType, $selectedRegion, $selectedBusinessSector, $offset, $limit);

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
    
    public function checkDuplicate()
    {
        $input = $this->request->getJSON(true);
        $data = $input['data'] ?? [];

        if (empty($data)) {
            return $this->response->setJSON([]);
        }

        // เอา branchname ทั้งหมด
        $names = array_map(function ($row) {
            return trim($row['branchname']);
        }, $data);

         // query ทีเดียว (เร็วกว่า loop)
        $existing = $this->branch
            ->select('int_saksiam_branch_name')
            ->whereIn('int_saksiam_branch_name', $names)
            ->findAll();

        // แปลงเป็น array lookup
        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[$row['int_saksiam_branch_name']] = true;
        }

        // response map
        $result = [];
        foreach ($names as $name) {
            $result[$name] = isset($existingMap[$name]);
        }

        return $this->response->setJSON($result);
    }



public function checkEditDuplicate()
{
    $input = $this->request->getJSON(true);
    $data = $input['data'] ?? [];

    if (empty($data)) {
        return $this->response->setJSON([]);
    }

    // 🔥 เอา id จาก CSV
    $ids = array_map(fn($row) => trim($row['branchID']), $data);

    // 🔥 SELECT เฉพาะ field + alias ตามที่ต้องการ
    $existing = $this->branch
        ->select([
            'int_saksiam_branch.int_saksiam_branch_id AS id',
            'int_saksiam_branch.int_saksiam_branch_regionid AS region',
            'int_saksiam_branch.int_saksiam_branch_areaid AS area',
            'int_saksiam_branch.int_saksiam_branch_type AS type',
            'int_saksiam_branch.int_saksiam_branch_name AS name',
            'int_saksiam_branch.int_saksiam_branch_address AS address',
            'int_saksiam_branch.int_saksiam_branch_DISTRICTID AS districtid',
            'int_saksiam_branch.int_saksiam_branch_DISTRICTNAME AS districtname',
            'int_saksiam_branch.int_saksiam_branch_AMPHURID AS amphurid',
            'int_saksiam_branch.int_saksiam_branch_AMPHURNAME AS amphurname',
            'int_saksiam_branch.int_saksiam_branch_PROVINCEID AS provinceid',
            'int_saksiam_branch.int_saksiam_branch_PROVINCENAME AS provincename',
            'int_saksiam_branch.int_saksiam_branch_zipcode AS zipcode',
            'int_saksiam_branch.int_saksiam_branch_detail AS detail',
            'int_saksiam_branch.int_saksiam_branch_tel AS tel',
            'int_saksiam_branch.int_saksiam_branch_lat AS lat',
            'int_saksiam_branch.int_saksiam_branch_lng AS lng',
            'int_saksiam_branch.int_saksiam_branch_status AS status',
            'int_saksiam_branch.int_saksiam_branch_savename AS savename',
            'int_saksiam_branch.int_saksiam_branch_createAt AS createAt',
            'int_saksiam_branch.int_saksiam_branch_updateAt AS updateAt'
        ])
        ->whereIn('int_saksiam_branch.int_saksiam_branch_id', $ids)
        ->findAll();

    // 🔥 map ข้อมูลเก่า
    $existingMap = [];

    foreach ($existing as $row) {
        $existingMap[$row['id']] = [
            'exists' => true,
            'old' => $row
        ];
    }

    // 🔥 ใส่ id ที่ไม่เจอ
    foreach ($ids as $id) {
        if (!isset($existingMap[$id])) {
            $existingMap[$id] = [
                'exists' => false,
                'old' => null
            ];
        }
    }

    // 🔥 (OPTIONAL) แนบข้อมูลใหม่จาก CSV กลับไปด้วย
    foreach ($data as $row) {
        $id = trim($row['branchID']);

        if (isset($existingMap[$id])) {
            $existingMap[$id]['new'] = $row; // 🔥 ตัวใหม่จาก CSV
        }
    }

    return $this->response->setJSON($existingMap);
}
    public function importCSV()
    {
        $input = $this->request->getJSON(true);
        $data = $input['data'] ?? [];

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ไม่มีข้อมูล'
            ])->setStatusCode(400);
        }

        if (count($data) > 100) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'อัปโหลดได้ไม่เกิน 100 รายการ'
            ])->setStatusCode(400);
        }

        $insertData = [];
        $duplicate = [];

        $names = array_map(function ($row) {
            return trim($row['branchname'] ?? '');
         }, $data);

        $existing = $this->branch
            ->select('int_saksiam_branch_name')
            ->whereIn('int_saksiam_branch_name', $names)
            ->findAll();

        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[$row['int_saksiam_branch_name']] = true;
        }

        // หา ID ล่าสุดครั้งเดียว
        $last = $this->branch
            ->select('int_saksiam_branch_id')
            ->orderBy('int_saksiam_branch_id', 'DESC')
            ->first();

        $nextId = $last ? ((int) $last['int_saksiam_branch_id']) + 1 : 1;

        foreach ($data as $row) {
            $name = trim($row['branchname'] ?? '');

            if ($name === '') {
                continue;
            }

            if (isset($existingMap[$name])) {
                $duplicate[] = $name;
                continue;
            }

            $insertData[] = [
                'int_saksiam_branch_id' => str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                'int_saksiam_branch_type' => $row['branchType'] ?? null,
                'int_saksiam_branch_name' => $name,
                'int_saksiam_branch_areaid' => $row['region'] ?? null,
                'int_saksiam_branch_regionid' => $row['businessSector'] ?? null,
                'int_saksiam_branch_address' => $row['address'] ?? null,
                'int_saksiam_branch_DISTRICTID' => $row['districtID'] ?? null,
                'int_saksiam_branch_DISTRICTNAME' => $row['district'] ?? null,
                'int_saksiam_branch_AMPHURID' => $row['amphoeID'] ?? null,
                'int_saksiam_branch_AMPHURNAME' => $row['amphoe'] ?? null,
                'int_saksiam_branch_PROVINCEID' => $row['provinceID'] ?? null,
                'int_saksiam_branch_PROVINCENAME' => $row['province'] ?? null,
                'int_saksiam_branch_zipcode' => $row['zipcode'] ?? null,
                'int_saksiam_branch_detail' => $row['landmark'] ?? null,
                'int_saksiam_branch_tel' => $row['phone'] ?? null,
                'int_saksiam_branch_lat' => $row['lat'] ?? null,
                'int_saksiam_branch_lng' => $row['lag'] ?? null,
                'int_saksiam_branch_status' => $row['active'],
                'int_saksiam_branch_savename' => $row['savename']
            ];

            $nextId++;
        }

        if (!empty($insertData)) {
            $this->branch->insertBatch($insertData);
         }

        return $this->response->setJSON([
            'status' => true,
            'inserted' => count($insertData),
            'duplicate' => $duplicate
        ]);
    }

    public function showBranchDataID($branchID = null)
    {
         if (is_null($branchID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Branch ID is required'
            ])->setStatusCode(400);
        }
        try {
            $folder = $this->branch->showBranchID($branchID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $folder,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function updateBranchDetail($branchID = null)
    {
        if (is_null($branchID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Branch ID  is required'
            ])->setStatusCode(400);
        }

        try {

            $requestData = $this->request->getJSON(true);
            if (empty($requestData)) {
                return $this->response->setJSON([
                        'status' => false,
                        'message' => 'No data received'
                ])->setStatusCode(400);
            }
             $branchData = $this->branch->find($branchID);
            if (!$branchData) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Branch data not found'
                ])->setStatusCode(404);
            }
            $updateData = [];
             /* ===================== CHECK DUPLICATE ===================== */

            if (isset($requestData['branchType']) && $requestData['branchType'] !== $branchData['int_saksiam_branch_type']) {
                $updateData['int_saksiam_branch_type'] = $requestData['branchType'];
            }
            if (isset($requestData['branchname']) && $requestData['branchname'] !== $branchData['int_saksiam_branch_name']) {
                $updateData['int_saksiam_branch_name'] = $requestData['branchname'];
            }
            if (isset($requestData['businessSector']) && $requestData['businessSector'] !== $branchData['int_saksiam_branch_regionid']) {
                $updateData['int_saksiam_branch_regionid'] = $requestData['businessSector'];
            }
            if (isset($requestData['region']) && $requestData['region'] !== $branchData['int_saksiam_branch_areaid']) {
                $updateData['int_saksiam_branch_areaid'] = $requestData['region'];
            }
            if (isset($requestData['address']) && $requestData['address'] !== $branchData['int_saksiam_branch_address']) {
                $updateData['int_saksiam_branch_address'] = $requestData['address'];
            }
            if (isset($requestData['districtID']) && $requestData['districtID'] !== $branchData['int_saksiam_branch_DISTRICTID']) {
                $updateData['int_saksiam_branch_DISTRICTID'] = $requestData['districtID'];
            }
            if (isset($requestData['district']) && $requestData['district'] !== $branchData['int_saksiam_branch_DISTRICTNAME']) {
                $updateData['int_saksiam_branch_DISTRICTNAME'] = $requestData['district'];
            }
            if (isset($requestData['amphoeID']) && $requestData['amphoeID'] !== $branchData['int_saksiam_branch_AMPHURID']) {
                $updateData['int_saksiam_branch_AMPHURID'] = $requestData['amphoeID'];
            }
            if (isset($requestData['amphoe']) && $requestData['amphoe'] !== $branchData['int_saksiam_branch_AMPHURNAME']) {
                $updateData['int_saksiam_branch_AMPHURNAME'] = $requestData['amphoe'];
            }
            if (isset($requestData['provinceID']) && $requestData['provinceID'] !== $branchData['int_saksiam_branch_PROVINCEID']) {
                $updateData['int_saksiam_branch_PROVINCEID'] = $requestData['provinceID'];
            }
            if (isset($requestData['province']) && $requestData['province'] !== $branchData['int_saksiam_branch_PROVINCENAME']) {
                $updateData['int_saksiam_branch_PROVINCENAME'] = $requestData['province'];
            }
            if (isset($requestData['zipcode']) && $requestData['zipcode'] !== $branchData['int_saksiam_branch_zipcode']) {
                $updateData['int_saksiam_branch_zipcode'] = $requestData['zipcode'];
            }
            if (isset($requestData['landmark']) && $requestData['landmark'] !== $branchData['int_saksiam_branch_detail']) {
                $updateData['int_saksiam_branch_detail'] = $requestData['landmark'];
            }
            if (isset($requestData['phone']) && $requestData['phone'] !== $branchData['int_saksiam_branch_tel']) {
                $updateData['int_saksiam_branch_tel'] = $requestData['phone'];
            }
            if (isset($requestData['lat']) && $requestData['lat'] !== $branchData['int_saksiam_branch_lat']) {
                $updateData['int_saksiam_branch_lat'] = $requestData['lat'];
            }
            if (isset($requestData['lag']) && $requestData['lag'] !== $branchData['int_saksiam_branch_lng']) {
                $updateData['int_saksiam_branch_lng'] = $requestData['lag'];
            }
            if (isset($requestData['active']) && $requestData['active'] !== $branchData['int_saksiam_branch_status']) {
                $updateData['int_saksiam_branch_status'] = $requestData['active'];
            }
            if (isset($requestData['updatename']) && $requestData['updatename'] !== $branchData['int_saksiam_branch_updatename']) {
                $updateData['int_saksiam_branch_updatename'] = $requestData['updatename'];
            }
            if (isset($requestData['changename']) && $requestData['changename'] !== $branchData['int_saksiam_branch_changename']) {
                $updateData['int_saksiam_branch_changename'] = $requestData['changename'];
            }

             if (!empty($updateData)) {
                $this->branch->updateData($branchID, $updateData);
            }
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Branch details updated successfully'
            ])->setStatusCode(200);

       } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
public function updateCSV()
{
    $input = $this->request->getJSON(true);
    $data = $input['data'] ?? [];

    if (empty($data)) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'ไม่มีข้อมูลสำหรับแก้ไข'
        ])->setStatusCode(400);
    }

    if (count($data) > 100) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'อัปโหลดได้ไม่เกิน 100 รายการ'
        ])->setStatusCode(400);
    }

    $updated = 0;
    $notFound = [];

    foreach ($data as $row) {

        // 🔥 format id เหมือนเดิม
        $branchID = trim($row['branchID'] ?? '');
        $branchID = str_replace('"', '', $branchID);
        $branchID = str_pad($branchID, 6, '0', STR_PAD_LEFT);

        if (empty($branchID)) {
            continue;
        }

        // 🔥 หา data เดิม
        $branchData = $this->branch->find($branchID);

        if (!$branchData) {
            $notFound[] = $branchID;
            continue;
        }

        $updateData = [];

        /* ===================== CHECK DUPLICATE ===================== */

        if (isset($row['branchType']) && $row['branchType'] !== $branchData['int_saksiam_branch_type']) {
            $updateData['int_saksiam_branch_type'] = $row['branchType'];
        }

        if (isset($row['branchname']) && $row['branchname'] !== $branchData['int_saksiam_branch_name']) {
            $updateData['int_saksiam_branch_name'] = $row['branchname'];
        }

        if (isset($row['businessSector']) && $row['businessSector'] !== $branchData['int_saksiam_branch_regionid']) {
            $updateData['int_saksiam_branch_regionid'] = $row['businessSector'];
        }

        if (isset($row['region']) && $row['region'] !== $branchData['int_saksiam_branch_areaid']) {
            $updateData['int_saksiam_branch_areaid'] = $row['region'];
        }

        if (isset($row['address']) && $row['address'] !== $branchData['int_saksiam_branch_address']) {
            $updateData['int_saksiam_branch_address'] = $row['address'];
        }

        if (isset($row['districtID']) && $row['districtID'] !== $branchData['int_saksiam_branch_DISTRICTID']) {
            $updateData['int_saksiam_branch_DISTRICTID'] = $row['districtID'];
        }

        if (isset($row['district']) && $row['district'] !== $branchData['int_saksiam_branch_DISTRICTNAME']) {
            $updateData['int_saksiam_branch_DISTRICTNAME'] = $row['district'];
        }

        if (isset($row['amphoeID']) && $row['amphoeID'] !== $branchData['int_saksiam_branch_AMPHURID']) {
            $updateData['int_saksiam_branch_AMPHURID'] = $row['amphoeID'];
        }

        if (isset($row['amphoe']) && $row['amphoe'] !== $branchData['int_saksiam_branch_AMPHURNAME']) {
            $updateData['int_saksiam_branch_AMPHURNAME'] = $row['amphoe'];
        }

        if (isset($row['provinceID']) && $row['provinceID'] !== $branchData['int_saksiam_branch_PROVINCEID']) {
            $updateData['int_saksiam_branch_PROVINCEID'] = $row['provinceID'];
        }

        if (isset($row['province']) && $row['province'] !== $branchData['int_saksiam_branch_PROVINCENAME']) {
            $updateData['int_saksiam_branch_PROVINCENAME'] = $row['province'];
        }

        if (isset($row['zipcode']) && $row['zipcode'] !== $branchData['int_saksiam_branch_zipcode']) {
            $updateData['int_saksiam_branch_zipcode'] = $row['zipcode'];
        }

        if (isset($row['landmark']) && $row['landmark'] !== $branchData['int_saksiam_branch_detail']) {
            $updateData['int_saksiam_branch_detail'] = $row['landmark'];
        }

        if (isset($row['phone']) && $row['phone'] !== $branchData['int_saksiam_branch_tel']) {
            $updateData['int_saksiam_branch_tel'] = $row['phone'];
        }

        if (isset($row['lat']) && $row['lat'] !== $branchData['int_saksiam_branch_lat']) {
            $updateData['int_saksiam_branch_lat'] = $row['lat'];
        }

        if (isset($row['lag']) && $row['lag'] !== $branchData['int_saksiam_branch_lng']) {
            $updateData['int_saksiam_branch_lng'] = $row['lag'];
        }

        if (isset($row['updatename'])) {
            $updateData['int_saksiam_branch_updatename'] = $row['updatename'];
        }

        // 🔥 update เฉพาะมีการเปลี่ยน
        if (!empty($updateData)) {
            $this->branch->updateData($branchID, $updateData);
            $updated++;
        }
    }

    return $this->response->setJSON([
        'status' => true,
        'updated' => $updated,
        'notFound' => $notFound
    ]);
}

}