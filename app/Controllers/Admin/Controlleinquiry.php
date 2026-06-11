<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CustomersModel;
use App\Models\TicketModel;

class Controlleinquiry extends BaseController
{
    protected $CustomersModel;
    protected $TicketModel;

   
    public function __construct()
    {
        // ✅ ชื่อ class ต้องตรง
        $this->customer = new CustomersModel();

        $this->tickets = new TicketModel();
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
    public function createdatacmcAPI()
    {
        try {
            $json = $this->request->getJSON(assoc: true);
            $fullname = $json['name'] ?? null;
            $phone = $json['phone'] ?? null;
            $email = $json['email'] ?? null;
            $subject = $json['subject'] ?? null;
            $detail = $json['detail'] ?? null;
            $type = $json['type'] ?? null;
            $solce = $json['solce'] ?? 'Website';
            $status = $json['status'] ?? 1;
            $savename = $json['savename'] ?? 'Website';

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
                    'int_saksiam_customers_email' => $email,
                    'int_saksiam_customers_IPAddress' => $this->request->getIPAddress(),
                    'int_saksiam_customers_device' => substr($this->request->getUserAgent()->getAgentString(), 0, 20),
                    'int_saksiam_customers_savename' => $savename,
                    
                ];

                $this->customer->insert($customerData);
                $customerID = $this->customer->getInsertID();
            }
            // ✅ 4. สร้าง ticket ใหม่ทุกครั้งที่สมัคร
            $ticketData = [
                'int_saksiam_ticket_customerid' => $customerID,
                'int_saksiam_ticket_subject' => $subject,
                'int_saksiam_ticket_message' => $detail,
                'int_saksiam_ticket_type' => $type ?? 1,
                'int_saksiam_ticket_solce' => $solce,
                'int_saksiam_ticket_createname' => $savename,
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
