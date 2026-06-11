<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CustomersModel;
use App\Models\TicketModel;
use App\Models\LoanapprovalsModel;

class ControllerApplication extends BaseController
{
    protected $CustomersModel;
    protected $TicketModel;
    protected $LoanapprovalsModel;

    public function __construct()
    {
        // ✅ ชื่อ class ต้องตรง
        $this->customer = new CustomersModel();

        $this->tickets = new TicketModel();
        $this->applicationsapproval = new LoanapprovalsModel();
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

    // public function createdataapplicationsAPI()
    // {
    //     try {

    //         $jsonData = $this->request->getJSON(true);

    //         if (!$jsonData) {
    //             return $this->response->setJSON([
    //                 'status' => false,
    //                 'message' => 'ไม่พบข้อมูล JSON'
    //             ])->setStatusCode(400);
    //         }

    //         $required = [
    //             'partner',
    //             'name',
    //             'phone',
    //             'type',
    //             'loan',
    //             'preferred',
    //             'amount',
    //             'subdistrict',
    //             'district',
    //             'province',
    //             'zipcode',
    //             'subdistricID',
    //             'loantype',
    //             'product'
    //         ];

    //         $missing = [];

    //         foreach ($required as $field) {

    //             if (
    //                 !isset($jsonData[$field]) ||
    //                 $jsonData[$field] === '' ||
    //                 $jsonData[$field] === null
    //             ) {
    //                 $missing[] = $field;
    //             }
    //         }

    //         if (!empty($missing)) {
    //             return $this->response->setJSON([
    //                 'status' => false,
    //                 'message' => 'กรุณากรอกข้อมูลที่จำเป็น',
    //                 'missing_fields' => $missing
    //             ])->setStatusCode(400);
    //         }

    //         $application = [
    //             'partner_id' => $jsonData['partner'],
    //             'customer_name' => $jsonData['name'],
    //             'customer_phone' => $jsonData['phone'],
    //             'customer_type' => $jsonData['type'],
    //             'has_loan_history' => $jsonData['loan'],
    //             'preferred_loan_date' => $jsonData['preferred'],
    //             'loan_amount' => $jsonData['amount'],
    //             'subdistrict_name' => $jsonData['subdistrict'],
    //             'district_name' => $jsonData['district'],
    //             'province_name' => $jsonData['province'],
    //             'postal_code' => $jsonData['zipcode'],
    //             'subdistrict_id' => $jsonData['subdistricID'],
    //         ];

    //         $this->applications->insert($application);

    //         $applicationsID = $this->applications->insertID();

    //         if (!$applicationsID) {
    //             return $this->response->setJSON([
    //                 'status' => false,
    //                 'message' => 'ไม่สามารถสร้างใบสมัครได้'
    //             ])->setStatusCode(500);
    //         }

    //         $applicationsitemData = [
    //             'loan_application_id' => $applicationsID,
    //             'loan_type_id' => $jsonData['loantype'],
    //             'product_name' => $jsonData['product'],
    //         ];

    //         $this->applicationsitem->insert($applicationsitemData);

    //         return $this->response->setJSON([
    //             'status' => true,
    //             'message' => 'บันทึกข้อมูลเรียบร้อย',
    //             'application_id' => $applicationsID
    //         ])->setStatusCode(201);

    //     } catch (\Throwable $e) {

    //         return $this->response->setJSON([
    //             'status' => false,
    //             'message' => 'เกิดข้อผิดพลาด',
    //             'error' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile(),
    //         ])->setStatusCode(500);
    //     }
    // }

    public function createdataapplicationsAPI()
    {
        try {
            $json = $this->request->getJSON(assoc: true);
            $fullname = $json['name'] ?? null;
            $phone = $json['phone'] ?? null;
            $subdistrict = $json['subdistrict'] ?? null;
            $district = $json['district'] ?? null;
            $province = $json['province'] ?? null;
            $zipcode = $json['zipcode'] ?? null;
            $subdistricID = $json['subdistricID'] ?? null;

            $type = $json['type'] ?? null;
            $loanhistory = $json['loan'] ?? null;
            $contedtime = $json['preferred'] ?? null;
            $cradit = $json['amount'] ?? null;
            $loanname = $json['loanname'] ?? null;
            $typecar = $json['product'] ?? null;
            $detail = $json['detail'] ?? null;
            $project = $json['project'] ?? null;
            $solce = $json['solce'] ?? null;
            $status = $json['status'] ?? null;

            $savename = $json['savename'] ?? null;

            if (!$fullname || !$phone ) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'กรุณากรอกชื่อ และ เบอร์โทร'
                    ])->setStatusCode(400);
                }
            $customer = $this->customer
                ->where('int_saksiam_customers_fullname', $fullname)
                ->where('int_saksiam_customers_tell', $phone)
                ->first();

            if ($customer) {
                $customerID = $customer['int_saksiam_customers_id'];
            } else {
                // ✅ 3. ถ้ายังไม่มี สร้างข้อมูลลูกค้าใหม่
                $customerData = [
                    'int_saksiam_customers_fullname' => $fullname,
                    'int_saksiam_customers_tell' => $phone,
                    'int_saksiam_customers_subdistrictid' => $subdistricID,
                    'int_saksiam_customers_subdistrictname' => $subdistrict,
                    'int_saksiam_customers_districtname' => $district,
                    'int_saksiam_customers_provincename' => $province,
                    'int_saksiam_customers_code' => $zipcode,
                    'int_saksiam_customers_IPAddress' => $this->request->getIPAddress(),
                    'int_saksiam_customers_device' => $this->request->getUserAgent()->getAgentString(),
                    'int_saksiam_customers_savename' => $savename,
                    
                ];

                $this->customer->insert($customerData);
                $customerID = $this->customer->getInsertID();
            }
            // ✅ 4. สร้าง ticket ใหม่ทุกครั้งที่สมัคร
            $ticketData = [
                'int_saksiam_ticket_customerid' => $customerID,
                'int_saksiam_ticket_loanhistory' => $loanhistory,
                'int_saksiam_ticket_subject' => $loanname,
                'int_saksiam_ticket_typeloan' => $typecar,
                 'int_saksiam_ticket_message' => $detail,
                'int_saksiam_ticket_cradit' => $cradit,
                'int_saksiam_ticket_contedtime' => $contedtime,
                'int_saksiam_ticket_type' => $type,
                'int_saksiam_ticket_solce' => $solce,
                'int_saksiam_ticket_createname' => $savename,
                'int_saksiam_ticket_projectname' => $project,
                'int_saksiam_ticket_status' => $status,
            ];

            $this->tickets->insert($ticketData);
            $ticketID = $this->tickets->getInsertID();
            return $this->response->setJSON([
                            'status' => true,
                            'message' => 'บันทึกข้อมูลเรียบร้อย',
                            'customer_id' => $customerID
                        ])->setStatusCode(201);
          
        } catch (\Throwable $e) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'เกิดข้อผิดพลาด',
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ])->setStatusCode(500);
            }
    }



}