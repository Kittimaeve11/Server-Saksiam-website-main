<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\LoanModel;

class ControlleLoan extends BaseController
{
    protected $LoanModel;
    public function __construct()
    {
        $this->loan = new LoanModel();
    }

    public function showLoanData()
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


        $loandata = $this->loan->getloan($offset, $limit, $activeFilter);

        if ($loandata) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $loandata,
            ]);
        } else {
            return $this->response->setJSON(0);
        }

    }


 public function createdataLoanAPI()
{
    $nameTH = $this->request->getVar('nameTH');
    $nameEN = $this->request->getVar('nameEN');

    $imagelarge = $this->request->getFile('imagelarge');
    $imagesmall = $this->request->getFile('imagesmall');

    $highlight = $this->request->getVar('highlight');
    $qualifications = $this->request->getVar('qualifications');
    $documens = $this->request->getVar('documens');

    $vehicleType = $this->request->getVar('vehicleType');
    $dose = $this->request->getVar('dose');

    $minamount = $this->request->getVar('minamount');
    $maxamount = $this->request->getVar('maxamount');

    $active = $this->request->getVar('active');
    $isopen = $this->request->getVar('isopen');
    $savename = $this->request->getVar('savename');

    // =========================
    // VALIDATE IMAGE
    // =========================
    if (!$imagelarge || !$imagelarge->isValid()) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'imagelarge invalid'
        ]);
    }

    if (!$imagesmall || !$imagesmall->isValid()) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'imagesmall invalid'
        ]);
    }

    // =========================
    // VALIDATE FILE TYPE
    // =========================
    $allowedTypes = ['jpg', 'jpeg', 'png'];

    if (
        !in_array($imagelarge->getExtension(), $allowedTypes) ||
        !in_array($imagesmall->getExtension(), $allowedTypes)
    ) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Invalid file type. Only JPG, JPEG and PNG are allowed.'
        ])->setStatusCode(400);
    }

    // =========================
    // UPLOAD DIR
    // =========================
    $uploadDirlarge = WRITEPATH . '../public/imagelarge/';
    $uploadDirsmall = WRITEPATH . '../public/imagesmall/';

    if (!is_dir($uploadDirlarge)) {
        mkdir($uploadDirlarge, 0777, true);
    }

    if (!is_dir($uploadDirsmall)) {
        mkdir($uploadDirsmall, 0777, true);
    }

    // =========================
    // CHECK DUPLICATE IMAGE
    // =========================
    if (
        $this->isDuplicateImageAfterCompress(
            $uploadDirlarge,
            $imagelarge->getTempName()
        )
    ) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'รูปแบนเนอร์ขนาดเดสก์ท็อปนี้มีอยู่ในระบบแล้ว'
        ])->setStatusCode(400);
    }

    if (
        $this->isDuplicateImageAfterCompress(
            $uploadDirsmall,
            $imagesmall->getTempName()
        )
    ) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'รูปแบนเนอร์ขนาดโทรศัพท์นี้มีอยู่ในระบบแล้ว'
        ])->setStatusCode(400);
    }

    // =========================
    // CREATE FILE NAME
    // =========================
    $newFileNamePC =
        uniqid('imagelarge_') .
        '.' .
        $imagelarge->getExtension();

    $newFileNameMobile =
        uniqid('imagesmall_') .
        '.' .
        $imagesmall->getExtension();

    // =========================
    // MOVE + COMPRESS PC
    // =========================
    $imagelarge->move(
        $uploadDirlarge,
        $newFileNamePC
    );

    \Config\Services::image()
        ->withFile(
            $uploadDirlarge . $newFileNamePC
        )
        ->save(
            $uploadDirlarge . $newFileNamePC,
            70
        );

    // =========================
    // MOVE + COMPRESS MOBILE
    // =========================
    $imagesmall->move(
        $uploadDirsmall,
        $newFileNameMobile
    );

    \Config\Services::image()
        ->withFile(
            $uploadDirsmall . $newFileNameMobile
        )
        ->save(
            $uploadDirsmall . $newFileNameMobile,
            70
        );

    // =========================
    // DATA
    // =========================
    $data = [
        'int_saksiam_loan_titleTH' => $nameTH,
        'int_saksiam_loan_titleEN' => $nameEN,

        'int_saksiam_loan_imagelarge' =>
            'imagelarge/' . $newFileNamePC,

        'int_saksiam_loan_small' =>
            'imagesmall/' . $newFileNameMobile,

        'int_saksiam_loan_highlight' =>
            json_encode(
                $highlight,
                JSON_UNESCAPED_UNICODE
            ),

        'int_saksiam_loan_qualifications' =>
            json_encode(
                $qualifications,
                JSON_UNESCAPED_UNICODE
            ),

        'int_saksiam_loan_documens' =>
            json_encode(
                $documens,
                JSON_UNESCAPED_UNICODE
            ),

        'int_saksiam_loan_vehicleType' =>
            $vehicleType,

        'int_saksiam_loan_dose' =>
            $dose,

        'int_saksiam_loan_minamount' =>
            $minamount,

        'int_saksiam_loan_maxamount' =>
            $maxamount,

        'int_saksiam_loan_active' =>
            $active,

        'int_saksiam_loan_isopen' =>
            $isopen,

        'int_saksiam_loan_savename' =>
            $savename,
    ];

    // =========================
    // SAVE
    // =========================
    try {

        $loanID = $this->loan->createLoanData($data);

        $data['int_saksiam_loan_id'] = $loanID;

        return $this->response->setJSON([
            'status' => true,
            'message' =>
                'Loan data created successfully',
            'data' => $data
        ]);

    } catch (\Exception $e) {

        return $this->response->setJSON([
            'status' => false,
            'message' =>
                'Failed to save data: ' .
                $e->getMessage()
        ])->setStatusCode(500);
    }
}
    private function isDuplicateImageAfterCompress(
        string $uploadDir,
        string $tmpPath,
        ?string $ignorePath = null
    ): bool {
        if (!is_dir($uploadDir)) {
            return false;
        }

        $tempCompressed = WRITEPATH . 'temp_check_' . uniqid() . '.jpg';

        \Config\Services::image()
            ->withFile($tmpPath)
            ->save($tempCompressed, 70);

        $newHash = md5_file($tempCompressed);

        foreach (scandir($uploadDir) as $file) {
            if ($file === '.' || $file === '..')
                continue;

            $existingFile = $uploadDir . $file;

            // 🔥 ข้ามไฟล์เดิมของ record นี้
            if ($ignorePath && realpath($existingFile) === realpath($ignorePath)) {
                continue;
            }

            if (is_file($existingFile) && md5_file($existingFile) === $newHash) {
                unlink($tempCompressed);
                return true;
            }
        }

        unlink($tempCompressed);
        return false;
    }

    public function updateLoanData($loanID = null)
    {
         if (!$loanID) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Loan ID is required'
            ])->setStatusCode(400);
        }

        $existingEvent = $this->loan->find($loanID);
        if (!$existingEvent) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Loan not found'
            ])->setStatusCode(404);
        }

        $loanDataToUpdate = [];

        $imagelarge = $this->request->getFile('imagelarge');
        $imagesmall = $this->request->getFile('imagesmall');
        $allowedTypes = ['jpg', 'jpeg','png'];


         // ===== PC =====
        if ($imagelarge && $imagelarge->isValid()) {
            if (!in_array($imagelarge->getExtension(), $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid large image type'
                ])->setStatusCode(400);
            }

            $oldPath = WRITEPATH . '../public/' . $existingEvent['int_saksiam_loan_imagelarge'];
            if (file_exists($oldPath))
                unlink($oldPath);

            $newFileNamePC = uniqid('imagelarge_') . '.' . $imagelarge->getExtension();
            $uploadDirPC = WRITEPATH . '../public/imagelarge/';

            $imagelarge->move($uploadDirPC, $newFileNamePC);
            \Config\Services::image()
                ->withFile($uploadDirPC . $newFileNamePC)
                ->save($uploadDirPC . $newFileNamePC, 70);

            $loanDataToUpdate['int_saksiam_loan_imagelarge'] = 'imagelarge/' . $newFileNamePC;
        }

        // ===== Mobile =====
        if ($imagesmall && $imagesmall->isValid()) {
            if (!in_array($imagesmall->getExtension(), $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid Mobile image type'
                ])->setStatusCode(400);
            }

            // 🔥 FIX PATH: normalize PictureMoblie → pictureMoblie
            $oldMobilePath = $existingEvent['int_saksiam_loan_small'] ?? '';
            $oldMobilePath = str_ireplace(
                'imagesmall/',
                'imagesmall/',
                $oldMobilePath
            );

            $oldPath = WRITEPATH . '../public/' . $oldMobilePath;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            $newFileNameMobile = uniqid('imagesmall_') . '.' . $imagesmall->getExtension();
            $uploadDirMobile = WRITEPATH . '../public/imagesmall/';

            $imagesmall->move($uploadDirMobile, $newFileNameMobile);

            \Config\Services::image()
                ->withFile($uploadDirMobile . $newFileNameMobile)
                ->save($uploadDirMobile . $newFileNameMobile, 70);

            // 🔥 บังคับบันทึก path ให้เป็นมาตรฐาน
            $loanDataToUpdate['int_saksiam_loan_small']
                = 'imagesmall/' . $newFileNameMobile;
        }

         // ===== field อื่น ๆ =====
        if ($this->request->getVar('nameEN') !== null)
            $loanDataToUpdate['int_saksiam_loan_titleTH'] = $this->request->getVar('nameEN');

        if ($this->request->getVar('nameEN') !== null)
            $loanDataToUpdate['int_saksiam_loan_titleEN'] = $this->request->getVar('nameEN');
        if ($this->request->getVar('highlight') !== null)
            $loanDataToUpdate['int_saksiam_loan_highlight'] =
                json_encode($this->request->getVar('highlight'), JSON_UNESCAPED_UNICODE);

        if ($this->request->getVar('qualifications') !== null)
            $loanDataToUpdate['int_saksiam_loan_qualifications'] =
                json_encode($this->request->getVar('qualifications'), JSON_UNESCAPED_UNICODE);

        if ($this->request->getVar('documens') !== null)
            $loanDataToUpdate['int_saksiam_loan_documens'] =
                json_encode($this->request->getVar('documens'), JSON_UNESCAPED_UNICODE);

        if ($this->request->getVar('vehicleType') !== null)
            $loanDataToUpdate['int_saksiam_loan_vehicleType'] = $this->request->getVar('vehicleType');

        if ($this->request->getVar('minamount') !== null)
            $loanDataToUpdate['int_saksiam_loan_minamount'] = $this->request->getVar('minamount');
        if ($this->request->getVar('maxamount') !== null)
            $loanDataToUpdate['int_saksiam_loan_maxamount'] = $this->request->getVar('maxamount');
        if ($this->request->getVar('active') !== null)
            $loanDataToUpdate['int_saksiam_loan_active'] = $this->request->getVar('active');
        if ($this->request->getVar('isopen') !== null)
            $loanDataToUpdate['int_saksiam_loan_isopen'] = $this->request->getVar('isopen');
        if ($this->request->getVar('updatename') !== null)
            $loanDataToUpdate['int_saksiam_loan_updatename'] = $this->request->getVar('updatename');
        if ($this->request->getVar('changename') !== null)
            $loanDataToUpdate['int_saksiam_loan_changename'] = $this->request->getVar('changename');

         try {
            if (!empty($loanDataToUpdate)) {
                $this->loan->updateData($loanID, $loanDataToUpdate);
            }

                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Loan updated successfully',
                    'data' => $loanDataToUpdate
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Update failed: ' . $e->getMessage()
                ])->setStatusCode(500);
        }

    }
    public function approvedloamData($loanID = null)
    {
        $data = $this->request->getJSON(true);
        $active = $data['active'] ?? null;
        $note = $data['note'] ?? null;
        $improvement = $data['improvement'] ?? null;
        $approvedName = $data['approvedName'] ?? null;
        $this->loan->updateData($loanID, [
            'int_saksiam_loan_active' => $active,
            'int_saksiam_loan_note' => $note,
            'int_saksiam_loan_improvement' => $improvement,
            'int_saksiam_loan_approvename' => $approvedName
        ]);
        return $this->response->setJSON([
            'status' => true,
            'message' => 'approved successfully!',
        ]);


    }

    public function showLoanID($loanID = null)
    {
        try {
            $loamData = $this->loan->showLoanID($loanID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $loamData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function getLoanDataMove()
    {
        $showLoan = $this->loan->getLoanMoveData();
        if ($showLoan) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data loan retrieved successfully',
                'result' => $showLoan
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found'
            ])->setStatusCode(404);
        }
    }

      public function updateLoanMove()
    {
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'No data received'
            ])->setStatusCode(400);
        }
        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid or loan data'
            ])->setStatusCode(code: 400);
        }
        $this->loan->updateLoanMove($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Banner order updated successfully'
        ])->
        setStatusCode(200);
    }



    public function listloanData()
    {
        $showData = $this->loan->listshowheader();
        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data loan retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }
    public function listloanFormData()
    {
        $showData = $this->loan->listFormApp();
        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data loan retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }
    public function listloanappData()
    {
        $showData = $this->loan->listsdiagnosis();
        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data loan retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }
    public function loandetailData($loanID)
    {
        $showData = $this->loan->showLoanpageID($loanID);
        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data Product type retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }
}
