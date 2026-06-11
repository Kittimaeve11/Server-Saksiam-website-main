<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\BannerModel;

class ControlleBanner extends BaseController
{
    protected $BranderModel;

    public function __construct()
    {
        $this->Banner = new BannerModel();
    }

    public function showBannerData()
    {
        $activeFilter = $this->request->getGet('active');
        $selectedType = $this->request->getGet('type');
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

        if ($selectedType === 'all' || $selectedType === '' || $selectedType === null) {
            $selectedType = null;
        }

        $banner = $this->Banner->getBanner($selectedType, $offset, $limit, $activeFilter);

        if ($banner) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $banner,
            ]);
        } else {
            return $this->response->setJSON(0);
        }

    }

     public function createdataBannerAPI()
    {
        $bannername = $this->request->getVar('bannername');
        $picturePC = $this->request->getFile('picturePC');
        $pictureMoblie = $this->request->getFile('pictureMoblie');
        $typename = $this->request->getVar('typename');
        $linkpage = $this->request->getVar('linkpage') ?? '';
        $active = $this->request->getVar('active');
        $savename = $this->request->getVar('savename');
        

        if (!$picturePC || !$picturePC->isValid()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'picturePC invalid'
            ]);
        }

        if (!$pictureMoblie || !$pictureMoblie->isValid()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'pictureMoblie invalid'
            ]);
        }

        $allowedTypes = ['jpg', 'jpeg'];
        if (
            !in_array($picturePC->getExtension(), $allowedTypes) ||
            !in_array($pictureMoblie->getExtension(), $allowedTypes)
        ) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid file type. Only JPG and JPEG are allowed.'
            ])->setStatusCode(400);
        }

        $uploadDirPC = WRITEPATH . '../public/picturePC/';
        $uploadDirMobile = WRITEPATH . '../public/pictureMoblie/';

        if (!is_dir($uploadDirPC))
            mkdir($uploadDirPC, 0777, true);
        if (!is_dir($uploadDirMobile))
            mkdir($uploadDirMobile, 0777, true);

        // 🔎 ตรวจรูปซ้ำ (หลัง compress)
        if ($this->isDuplicateImageAfterCompress($uploadDirPC, $picturePC->getTempName())) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'รูปแบนเนอร์ขนาดเดสก์ท็อปนี้มีอยู่ในระบบแล้ว'
            ])->setStatusCode(400);
        }

        if ($this->isDuplicateImageAfterCompress($uploadDirMobile, $pictureMoblie->getTempName())) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'รูปแบนเนอร์ขนาดโทรศัพท์นี้มีอยู่ในระบบแล้ว'
            ])->setStatusCode(400);
        }

        $newFileNamePC = uniqid('PicturePC_') . '.' . $picturePC->getExtension();
        $newFileNameMobile = uniqid('PictureMobile_') . '.' . $pictureMoblie->getExtension();

        // 👉 move + compress PC
        $picturePC->move($uploadDirPC, $newFileNamePC);
        \Config\Services::image()
            ->withFile($uploadDirPC . $newFileNamePC)
            ->save($uploadDirPC . $newFileNamePC, 70);

        // 👉 move + compress Mobile
        $pictureMoblie->move($uploadDirMobile, $newFileNameMobile);
        \Config\Services::image()
            ->withFile($uploadDirMobile . $newFileNameMobile)
            ->save($uploadDirMobile . $newFileNameMobile, 70);

        $data = [
            'int_saksiam_banner_name' => $bannername,
            'int_saksiam_banner_type' => $typename,
            'int_saksiam_banner_link' => $linkpage,
            'int_saksiam_banner_active' => $active,
            'int_saksiam_banner_savename' => $savename,
            'int_saksiam_banner_picturePC' => 'picturePC/' . $newFileNamePC,
            'int_saksiam_banner_pictureMoblie' => 'pictureMoblie/' . $newFileNameMobile,
        ];

        try {
          $bannerID = $this->Banner->createBannerData($data);
          $data['int_saksiam_banner_ID'] = $bannerID;

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Banner data created successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
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


    public function updateBannerDAta($banID = null)
    {
        if (!$banID) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Banner ID is required'
            ])->setStatusCode(400);
        }

        $existingEvent = $this->Banner->find($banID);
        if (!$existingEvent) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Banner not found'
            ])->setStatusCode(404);
        }

        $bannerDataToUpdate = [];

        $picturePC = $this->request->getFile('picturePC');
        $pictureMoblie = $this->request->getFile('pictureMoblie');

        $allowedTypes = ['jpg', 'jpeg'];

        // ===== PC =====
        if ($picturePC && $picturePC->isValid()) {
            if (!in_array($picturePC->getExtension(), $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid PC image type'
                ])->setStatusCode(400);
            }

            $oldPath = WRITEPATH . '../public/' . $existingEvent['int_saksiam_banner_picturePC'];
            if (file_exists($oldPath))
                unlink($oldPath);

            $newFileNamePC = uniqid('PicturePC_') . '.' . $picturePC->getExtension();
            $uploadDirPC = WRITEPATH . '../public/picturePC/';

            $picturePC->move($uploadDirPC, $newFileNamePC);
            \Config\Services::image()
                ->withFile($uploadDirPC . $newFileNamePC)
                ->save($uploadDirPC . $newFileNamePC, 70);

            $bannerDataToUpdate['int_saksiam_banner_picturePC'] = 'picturePC/' . $newFileNamePC;
        }

        // ===== Mobile =====
        if ($pictureMoblie && $pictureMoblie->isValid()) {
            if (!in_array($pictureMoblie->getExtension(), $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid Mobile image type'
                ])->setStatusCode(400);
            }

            // 🔥 FIX PATH: normalize PictureMoblie → pictureMoblie
            $oldMobilePath = $existingEvent['int_saksiam_banner_pictureMoblie'] ?? '';
            $oldMobilePath = str_ireplace(
                'PictureMoblie/',
                'pictureMoblie/',
                $oldMobilePath
            );

            $oldPath = WRITEPATH . '../public/' . $oldMobilePath;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            $newFileNameMobile = uniqid('PictureMoblie_') . '.' . $pictureMoblie->getExtension();
            $uploadDirMobile = WRITEPATH . '../public/pictureMoblie/';

            $pictureMoblie->move($uploadDirMobile, $newFileNameMobile);

            \Config\Services::image()
                ->withFile($uploadDirMobile . $newFileNameMobile)
                ->save($uploadDirMobile . $newFileNameMobile, 70);

            // 🔥 บังคับบันทึก path ให้เป็นมาตรฐาน
            $bannerDataToUpdate['int_saksiam_banner_pictureMoblie']
                = 'pictureMoblie/' . $newFileNameMobile;
        }


        // ===== field อื่น ๆ =====
        if ($this->request->getVar('bannername') !== null)
            $bannerDataToUpdate['int_saksiam_banner_name'] = $this->request->getVar('bannername');

        if ($this->request->getVar('linkpage') !== null)
            $bannerDataToUpdate['int_saksiam_banner_link'] = $this->request->getVar('linkpage');

        if ($this->request->getVar('active') !== null)
            $bannerDataToUpdate['int_saksiam_banner_active'] = $this->request->getVar('active');

        try {
            if (!empty($bannerDataToUpdate)) {
                $this->Banner->updateData($banID, $bannerDataToUpdate);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Banner updated successfully',
                'data' => $bannerDataToUpdate
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function showBannerDataID($banID = null)
    {
        try {
            $banData = $this->Banner->showBannerID($banID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $banData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function deleteBanner($branID)
    {
        try {
            // 1. ตรวจ Brander ID
            if (empty($banID) || !is_numeric($banID)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid Bander ID'
                ])->setStatusCode(400);
            }

            // 2. ดึงข้อมูลเดิมก่อน (เพื่อเอา path รูป)
            $bander = $this->Banner->find($banID);
            if (!$bander) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Brander not found'
                ])->setStatusCode(404);
            }

            // 3. เตรียม path รูป
            $pcPath = null;
            $mobilePath = null;

            if (!empty($brander['int_saksiam_banner_picturePC'])) {
                $pcPath = WRITEPATH . '../public/' . $brander['int_saksiam_banner_picturePC'];
            }

            if (!empty($brander['int_saksiam_banner_pictureMoblie'])) {
                $mobilePath = WRITEPATH . '../public/' . $brander['int_saksiam_banner_pictureMoblie'];
            }

            // 4. ลบข้อมูลในฐานข้อมูล
            $deleted = $this->Banner->delete($banID);

            if (!$deleted) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Failed to delete Brander'
                ])->setStatusCode(500);
            }

            // 5. ลบไฟล์รูป (ถ้ามี และไฟล์อยู่จริง)
            if ($pcPath && file_exists($pcPath)) {
                @unlink($pcPath); // ใช้ @ เพื่อกัน warning ถ้าลบไม่ได้
            }

            if ($mobilePath && file_exists($mobilePath)) {
                @unlink($mobilePath);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Banner deleted successfully'
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'An unexpected error occurred',
            ])->setStatusCode(500);
        }
    }


    public function getBannerDataMove()
    {
        $showBanner = $this->Banner->getBannerMoveData();
        if ($showBanner) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data Brander retrieved successfully',
                'result' => $showBanner
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found'
            ])->setStatusCode(404);
        }
    }

    public function updateBannerMove()
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
                'message' => 'Invalid or missing data'
            ])->setStatusCode(code: 400);
        }
        $this->Banner->updateBannerMove($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Banner order updated successfully'
        ])->setStatusCode(200);
    }


     public function bannerData()
    {
        $showData = $this->Banner->getBannerWebsite();
        if ($showData) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data Brander retrieved successfully',
                'data' => $showData
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

     public function bannerDataID($banID = null)
    {
        try {
            $banData = $this->Banner->showBannerID($banID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $banData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

}
