<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use App\Models\ContactModel;

class ControlleContact extends BaseController
{
    protected $ContactModel;

    public function __construct()
    {
        $this->contact = new ContactModel();
    }

     public function createdataContactAPI()
    {
        $addressTH = $this->request->getVar('addressTH');
        $addressEN = $this->request->getVar('addressEN');
        $officehoursTH = $this->request->getVar('officehoursTH');
        $officehoursEN = $this->request->getVar('officehoursEN');
        $phonenumber = $this->request->getVar('phone');
        $callcenter = $this->request->getVar('callcenter');
        $fax = $this->request->getVar('fax');
        $email = $this->request->getVar('email');
        $subemail = $this->request->getVar('subemail');
        $googlemap = $this->request->getVar('map');
        $facbook = $this->request->getVar('facbook');
        $line = $this->request->getVar('line');
        $instagram = $this->request->getVar('instagram');
        $youtube = $this->request->getVar('youtube');
        $tikkok = $this->request->getVar('tikkok');
        $savename = $this->request->getVar('savename');
        $locationPhoto = $this->request->getFile('locationPhoto');

        $allowedTypes = ['jpg', 'jpeg'];
        $fileExtensionLocationPhoto = $locationPhoto->getExtension();

        if (!in_array($fileExtensionLocationPhoto, $allowedTypes)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid file type. Only JPG and JPEG are allowed.'
            ])->setStatusCode(400);
        }


        $uploadDirLocationPhoto = WRITEPATH . '../public/locationPhoto/';
        if (!is_dir($uploadDirLocationPhoto)) {
            mkdir($uploadDirLocationPhoto, 0777, true);
        }

        $fileHashlocationPhoto = md5_file($locationPhoto->getTempName());

        $existingFileslocationPhoto = scandir($uploadDirLocationPhoto);

        foreach ($existingFileslocationPhoto as $file) {
            if ($file !== '.' && $file !== '..') {
                if (md5_file($uploadDirLocationPhoto . $file) === $fileHashlocationPhoto) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'มีรูปที่ชื่อนี้อยู่ในระบบแล้ว!'
                    ])->setStatusCode(400);
                }
            }
        }

        $newFileLocationPhoto = uniqid('LocationPhoto') . '.' . $fileExtensionLocationPhoto;

        try {
            $locationPhoto->move($uploadDirLocationPhoto, $newFileLocationPhoto);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save file: ' . $e->getMessage()
            ])->setStatusCode(500);
        }

        $data = [
            'int_saksiam_contact_addressTH' => $addressTH,
            'int_saksiam_contact_addressEN' => $addressEN,
            'int_saksiam_contact_officehoursTH' => $officehoursTH,
            'int_saksiam_contact_officehoursEN' => $officehoursEN,
            'int_saksiam_contact_phonenumber' => $phonenumber,
            'int_saksiam_contact_callcenter' => $callcenter,
            'int_saksiam_contact_fax' => $fax,
            'int_saksiam_contact_emailmain' => $email,
            'int_saksiam_contact_emailsub' => $subemail,
            'int_saksiam_contact_googlemap' => $googlemap,
            'int_saksiam_contact_Facbook' => $facbook,
            'int_saksiam_contact_line' => $line,
            'int_saksiam_contact_IG' => $instagram,
            'int_saksiam_contact_youtube' => $youtube,
            'int_saksiam_contact_tikkok' => $tikkok,
            'int_saksiam_contact_savename' => $savename,
            'int_saksiam_contact_locationphoto' => 'LocationPhoto/' . $newFileLocationPhoto
        ];

        try {
            $contactID = $this->contact->createContactDATA($data);
            $data['int_saksiam_contact_id'] = $contactID;

            return $this->response->setJSON([
                'status' => true,
                'message' => 'contact data created successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

     public function showContactDaTaID($contactID = null)
    {
        try {
            $contactData = $this->contact->showContactIDData($contactID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $contactData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

     public function updateContactData($contactID = null)
    {
        if (is_null($contactID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'contact ID is required'
            ])->setStatusCode(400);
        }
        ;
        $existingEvent = $this->contact->find($contactID);
        if (!$existingEvent) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'contact not found'
            ])->setStatusCode(404);
        }
        $contactDataToUpdate = [];
        $addressTH = $this->request->getVar('addressTH');
        $addressEN = $this->request->getVar('addressEN');
        $officehoursTH = $this->request->getVar('officehoursTH');
        $officehoursEN = $this->request->getVar('officehoursEN');
        $phonenumber = $this->request->getVar('phone');
        $callcenter = $this->request->getVar('callcenter');
        $taxpayer = $this->request->getVar('taxpayer');
        $fax = $this->request->getVar('fax');
        $email = $this->request->getVar('email');
        $subemail = $this->request->getVar('subemail');
        $googlemap = $this->request->getVar('map');
        $facbook = $this->request->getVar('facbook');
        $line = $this->request->getVar('line');
        $instagram = $this->request->getVar('instagram');
        $youtube = $this->request->getVar('youtube');
        $tikkok = $this->request->getVar('tikkok');
        $updatename = $this->request->getPost('updatename');
        $locationPhoto = $this->request->getFile('locationPhoto');

        if ($addressTH !== null) {
            $contactDataToUpdate['int_saksiam_contact_addressTH'] = $addressTH;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($addressEN !== null) {
            $contactDataToUpdate['int_saksiam_contact_addressEN'] = $addressEN;
            $updatename = $updatename ?? 'Default Name';
        }
        if ($officehoursTH !== null) {
            $contactDataToUpdate['int_saksiam_contact_officehoursTH'] = $officehoursTH;
            $updatename = $updatename ?? 'Default Name';
        }
        if ($officehoursEN !== null) {
            $contactDataToUpdate['int_saksiam_contact_officehoursEN'] = $officehoursEN;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($phonenumber !== null) {
            $contactDataToUpdate['int_saksiam_contact_phonenumber'] = $phonenumber;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($callcenter !== null) {
            $contactDataToUpdate['int_saksiam_contact_callcenter'] = $callcenter;
            $updatename = $updatename ?? 'Default Name';
        }
        if ($taxpayer !== null) {
            $contactDataToUpdate['int_saksiam_contact_taxpayer'] = $taxpayer;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($fax !== null) {
            $contactDataToUpdate['int_saksiam_contact_fax'] = $fax;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($email !== null) {
            $contactDataToUpdate['int_saksiam_contact_emailmain'] = $email;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($subemail !== null) {
            $contactDataToUpdate['int_saksiam_contact_emailsub'] = $subemail;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($googlemap !== null) {
            $contactDataToUpdate['int_saksiam_contact_googlemap'] = $googlemap;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($facbook !== null) {
            $contactDataToUpdate['int_saksiam_contact_Facbook'] = $facbook;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($line !== null) {
            $contactDataToUpdate['int_saksiam_contact_line'] = $line;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($instagram !== null) {
            $contactDataToUpdate['int_saksiam_contact_IG'] = $instagram;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($youtube !== null) {
            $contactDataToUpdate['int_saksiam_contact_youtube'] = $youtube;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($tikkok !== null) {
            $contactDataToUpdate['int_saksiam_contact_tikkok'] = $tikkok;
            $updatename = $updatename ?? 'Default Name';
        }

        if ($locationPhoto && $locationPhoto->isValid()) {
            $allowedTypes = ['jpg', 'jpeg'];
            $fileExtension = $locationPhoto->getExtension();
            if (!in_array($fileExtension, $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid file type. Only JPG and JPEG are allowed.'
                ])->setStatusCode(400);
            }

            $oldFilePath = WRITEPATH . '../public/' . $existingEvent['int_saksiam_contact_locationphoto'];
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            $newFileName = uniqid('locationPhoto') . '.' . $fileExtension;
            $locationPhoto->move(WRITEPATH . '../public/locationPhoto/', $newFileName);

            $contactDataToUpdate['int_saksiam_contact_locationphoto'] = 'locationPhoto/' . $newFileName;
        }


        if ($updatename !== null) {
            $contactDataToUpdate['int_saksiam_contact_updatename'] = $updatename;
        }

        try {
            if (!empty($contactDataToUpdate)) {
                $this->contact->updateContactDATA($contactID, $contactDataToUpdate);
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Contact data updated successfully',
                    'data' => $contactDataToUpdate
                ])->setStatusCode(200);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to update data: ' . $e->getMessage()
            ])->setStatusCode(400);
        }
    }

}
