<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RolePermistion;
use App\Models\RoleModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use CodeIgniter\API\ResponseTrait;

class ControllerAuth extends BaseController
{
      use ResponseTrait;
    protected $Auther;
    protected $format = "json";

    public function __construct()
    {
        $this->auther = new UserModel();
        $this->rolepermistion = new RolePermistion();
        $this->role = new RoleModel();
    }
    public function regiterProsonal()
    {
        // ------ รับค่าจาก request แบบ getVar ------
        $pname = $this->request->getVar('pname');
        $fname = $this->request->getVar('fname');
        $lname = $this->request->getVar('lname');
        $nickname = $this->request->getVar('nickname');
        $birthday = $this->request->getVar('birthday');
        $IDCard = $this->request->getVar('IDCard');
        $address = $this->request->getVar('address');
        $district = $this->request->getVar('district');
        $amphoe = $this->request->getVar('amphoe');
        $province = $this->request->getVar('province');
        $zipcode = $this->request->getVar('zipcode');
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $phone = $this->request->getVar('phone');
        $phone6 = $this->request->getVar('phone6');
        $role = $this->request->getVar('role');
        $status = $this->request->getVar('status');
        $createby = $this->request->getVar('createby') ?? 'system';

        // ------ รับไฟล์รูป ------
        $photo = $this->request->getFile('photo');


        // ----------- VALIDATE -------------
        $rules = [
            'pname' => 'required',
            'fname' => 'required|min_length[2]',
            'lname' => 'required|min_length[2]',
            'nickname' => 'required',
            'birthday' => 'required',
            'IDCard' => 'required|min_length[13]|max_length[13]|is_unique[int_saksiam_personnel.int_saksiam_personnel_IDCard]',
            'address' => 'required',
            'district' => 'required',
            'amphoe' => 'required',
            'province' => 'required',
            'zipcode' => 'required|min_length[5]|max_length[5]',
            'email' => 'required|valid_email|is_unique[int_saksiam_personnel.int_saksiam_personnel_email]',
            'password' => 'required|min_length[6]',
            'phone' => 'required|min_length[9]|max_length[10]',
            'phone6' => 'required|min_length[6]|max_length[9]',
            'role' => 'required',
            'status' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                "status" => false,
                "message" => "ข้อมูลไม่ครบถ้วน",
            ], 400);
        }


        // ----------- ตรวจสอบไฟล์รูป (ถ้ามีส่งมา) ---------
        $photoNameToSave = null;

        if ($photo && $photo->isValid() && !$photo->hasMoved()) {

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExt = strtolower($photo->getExtension());

            if (!in_array($fileExt, $allowedTypes)) {
                return $this->respond([
                    "status" => false,
                    "message" => "ประเภทไฟล์รูปไม่ถูกต้อง (รองรับ JPG, JPEG, PNG, GIF เท่านั้น)"
                ], 400);
            }

            // directory สำหรับบันทึกรูป
            $uploadDir = WRITEPATH . '../public/Personnel/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // ตรวจว่ามีไฟล์ซ้ำหรือไม่
            $fileHash = md5_file($photo->getTempName());
            $existingFiles = scandir($uploadDir);

            foreach ($existingFiles as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (md5_file($uploadDir . $file) === $fileHash) {
                        return $this->respond([
                            'status' => false,
                            'message' => 'รูปนี้ถูกใช้งานแล้วในระบบ'
                        ], 400);
                    }
                }
            }

            // generate ชื่อใหม่
            $newFileName = uniqid('Personnel_') . "." . $fileExt;

            try {
                $photo->move($uploadDir, $newFileName);
                $photoNameToSave = 'Personnel/' . $newFileName;
            } catch (\Exception $e) {
                return $this->respond([
                    "status" => false,
                    "message" => "บันทึกรูปไม่สำเร็จ: " . $e->getMessage()
                ], 500);
            }
        }


        try {
            // 1) Generate personnel number
            $personnelNum = $this->auther->generatepersonnelNumber();

            // 2) เตรียมข้อมูลบันทึก
            $data = [
                'int_saksiam_personnel_num' => $personnelNum,
                'int_saksiam_personnel_pname' => $pname,
                'int_saksiam_personnel_fname' => $fname,
                'int_saksiam_personnel_lname' => $lname,
                'int_saksiam_personnel_nickname' => $nickname,
                'int_saksiam_personnel_birthday' => $birthday,
                'int_saksiam_personnel_IDCard' => $IDCard,
                'int_saksiam_personnel_address' => $address,
                'int_saksiam_personnel_district' => $district,
                'int_saksiam_personnel_amphoe' => $amphoe,
                'int_saksiam_personnel_province' => $province,
                'int_saksiam_personnel_zipcode' => $zipcode,
                'int_saksiam_personnel_email' => $email,
                'int_saksiam_personnel_phone' => $phone,
                'int_saksiam_personnel_phone6' => $phone6,
                'int_saksiam_personnel_role' => $role,
                'int_saksiam_personnel_status' => $status,
                'int_saksiam_personnel_password' => password_hash($password, PASSWORD_DEFAULT),
                'int_saksiam_personnel_createby' => $createby,
                'int_saksiam_personnel_photo' => $photoNameToSave
            ];

            // 3) Insert DB
            $insertID = $this->auther->createpersonnelData($data);

            return $this->respond([
                "status" => true,
                "message" => "สมัครสมาชิกสำเร็จ",
                "personnel_id" => $insertID,
                "personnel_num" => $personnelNum,
            ], 201);

        } catch (\Throwable $e) {
            return $this->respond([
                "status" => false,
                "message" => "เกิดข้อผิดพลาด",
                "error" => $e->getMessage()
            ], 500);
        }
    }



    public function loginProsonal()
    {
        // -------------------- VALIDATE --------------------
        $validationRules = [
            "email" => "required|valid_email",
            "password" => "required"
        ];

        if (!$this->validate($validationRules)) {
            return $this->respond([
                "status" => false,
                "message" => "กรุณากรอกข้อมูลให้ครบถ้วน",
                "errors" => $this->validator->getErrors()
            ], status: 422);
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // -------------------- FIND USER --------------------
        $user = $this->auther
            ->where('int_saksiam_personnel_email', $email)
            ->first();

        if (!$user) {
            return $this->respond([
                "status" => false,
                "message" => "อีเมล หรือ รหัสผ่านไม่ถูกต้อง"
            ], 422);
        }

        // -------------------- VERIFY PASSWORD --------------------
        if (!password_verify($password, $user['int_saksiam_personnel_password'])) {
            return $this->respond([
                "status" => false,
                "message" => "อีเมล หรือ รหัสผ่านไม่ถูกต้อง"
            ], 422);
        }

        // -------------------- CHECK USER STATUS --------------------
        if ($user['int_saksiam_personnel_status'] != 1) {
            return $this->respond([
                "status" => false,
                "message" => "บัญชีผู้ใช้งานถูกระงับ"
            ], 403);
        }

        // -------------------- CREATE JWT --------------------
        $key = getenv('JWT_KEY');

        $payload = [
            "iss" => "saksiam-system",
            "aud" => "saksiam-users",
            "iat" => time(),
            "exp" => time() + 36000,
            "user" => [
                "id" => $user['int_saksiam_personnel_id'],
                "num" => $user['int_saksiam_personnel_num'],
                "pname" => $user['int_saksiam_personnel_pname'],
                "fname" => $user['int_saksiam_personnel_fname'],
                "lname" => $user['int_saksiam_personnel_lname'],
                "picture" => $user['int_saksiam_personnel_photo'],
                "email" => $user['int_saksiam_personnel_email'],
                "role_id" => $user['int_saksiam_personnel_role']
            ]
        ];

        $accessToken = JWT::encode($payload, $key, 'HS256');

        // -------------------- CREATE REFRESH TOKEN --------------------
        $refreshPayload = [
            "iss" => "saksiam-system",
            "aud" => "saksiam-users",
            "iat" => time(),
            "exp" => time() + 60 * 60 * 24 * 7, // 7 DAY
            "type" => "refresh",
            "sub" => $user['int_saksiam_personnel_id']
        ];
        $refreshToken = JWT::encode($refreshPayload, $key, 'HS256');
        // Set HttpOnly Cookie
        // Set HttpOnly Cookie
        $this->response->setCookie(
            'refresh_token',
            $this->tripleBase64Encode($refreshToken),
            60 * 60 * 24 * 7, // age
            "",               // domain
            "/",              // path
            "",               // prefix
            false,            // secure = false (เพราะไม่ได้ใช้ HTTPS)
            true,             // httponly
            "Lax"             // SameSite=Lax ปลอดภัยและไม่ต้อง HTTPS
        );


        // -------------------- RESPONSE --------------------
        return $this->respond([
            "status" => true,
            "message" => "เข้าสู่ระบบสำเร็จ",
            "access_token" => $this->tripleBase64Encode($accessToken),
        ]);
    }

    public function refreshTokenProsonal()
    {
        $refreshToken = $this->request->getCookie("refresh_token");

        if (!$refreshToken) {
            return $this->respond([
                "status" => false,
                "message" => "Missing refresh token"
            ], 400);
        }

        $refreshToken = base64_decode(base64_decode(base64_decode($refreshToken)));

        $key = getenv("JWT_KEY");

        try {
            $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));

            if (($decoded->type ?? '') !== 'refresh') {
                return $this->respond([
                    "status" => false,
                    "message" => "Invalid refresh token type"
                ], 401);
            }

            $user = $this->auther->find($decoded->sub);
            if (!$user) {
                return $this->respond([
                    "status" => false,
                    "message" => "User not found"
                ], 404);
            }

            if ($user["int_saksiam_personnel_status"] != 1) {
                return $this->respond([
                    "status" => false,
                    "message" => "บัญชีผู้ใช้งานถูกระงับ"
                ], 403);
            }

            // -------------------- ISSUE NEW ACCESS TOKEN --------------------
            $payload = [
                "iss" => "saksiam-system",
                "aud" => "saksiam-users",
                "iat" => time(),
                "exp" => time() + 36000,
                "type" => "access",
                "user" => [
                    "id" => $user['int_saksiam_personnel_id'],
                    "num" => $user['int_saksiam_personnel_num'],
                    "pname" => $user['int_saksiam_personnel_pname'],
                    "fname" => $user['int_saksiam_personnel_fname'],
                    "lname" => $user['int_saksiam_personnel_lname'],
                    "picture" => $user['int_saksiam_personnel_photo'],
                    "email" => $user['int_saksiam_personnel_email'],
                    "role_id" => $user['int_saksiam_personnel_role']
                ]
            ];

            $newAccessToken = JWT::encode($payload, $key, "HS256");

            return $this->respond([
                "status" => true,
                "access_token" => $this->tripleBase64Encode($newAccessToken)
            ]);

        } catch (ExpiredException $e) {
            return $this->respond([
                "status" => false,
                "message" => "Refresh token expired"
            ], 401);
        } catch (\Exception $e) {
            return $this->respond([
                "status" => false,
                "message" => "Invalid refresh token"
            ], 401);
        }
    }


    private function tripleBase64Encode($value)
    {
        return base64_encode(
            base64_encode(
                base64_encode($value)
            )
        );
    }
    public function logoutProsonal()
    {
        // ลบ cookie refresh_token โดยการ set expiry = time() - 3600
        return $this->response
            ->setCookie(
                'refresh_token',
                '',
                -3600,      // ⬅️ หมดอายุทันที
                "",
                "/",
                "",
                false,      // secure
                true,       // httpOnly
                "Lax"
            )
            ->setJSON([
                "status" => true,
                "message" => "ออกจากระบบสำเร็จ (Refresh Token ถูกลบแล้ว)"
            ]);
    }

    public function prosonalProfile()
    {
        $payload = $this->request->decodedToken ?? null;

        if (!$payload) {
            return $this->respond([
                "status" => false,
                "message" => "Unauthorized"
            ], 401);
        }

        // ✅ normalize user data
        $userRaw = $payload['user'] ?? null;

        if (is_object($userRaw)) {
            $userData = json_decode(json_encode($userRaw), true);
        } elseif (is_array($userRaw)) {
            $userData = $userRaw;
        } else {
            $userData = [];
        }

        if (empty($userData)) {
            return $this->respond([
                "status" => false,
                "message" => "Invalid token payload"
            ], 400);
        }

        // role
        $roleId = (int) ($userData['role_id'] ?? 0);
        $roleData = $this->role->find($roleId);
        $roleName = $roleData['int_saksiam_role_name'] ?? null;

        // permissions
        $permissions = $this->rolepermistion->getPermissionSlugsByRole($roleId);

        return $this->respond([
            "status" => true,
            "user" => [
                "id" => $userData["id"] ?? null,
                "num" => $userData["num"] ?? null,
                "pname" => $userData["pname"] ?? "",
                "fname" => $userData["fname"] ?? "",
                "lname" => $userData["lname"] ?? "",
                "picture" => $userData["picture"] ?? null, // ✅ มาแน่นอน
                "email" => $userData["email"] ?? "",
                "role_id" => $roleId,
                "role_name" => $roleName
            ],
            "permissions" => $permissions
        ]);
    }


    public function showUserDAta()
    {
        $roleID = $this->request->getVar('roleID');
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

        $userSDAta = $this->auther->getPersonalData($roleID, $activeFilter, $offset, $limit);
        if ($userSDAta) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $userSDAta,
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

    public function resetPassword()
    {
        $userId = $this->request->getVar('user_id');
        $updatename = $this->request->getVar('updatename');

        if (!$userId) {
            return $this->respond([
                "status" => false,
                "message" => "Missing user ID"
            ], 400);
        }

        $newPass = password_hash("123456", PASSWORD_DEFAULT);

        $updated = $this->auther->update($userId, [
            "int_saksiam_personnel_password" => $newPass,
            "int_saksiam_personnel_updateby" => $updatename
        ]);

        if (!$updated) {
            return $this->respond([
                "status" => false,
                "message" => "Reset password failed"
            ], 500);
        }

        return $this->respond([
            "status" => true,
            "message" => "รีเซ็ตรหัสผ่านสำเร็จ (รหัสใหม่: 123456)"
        ]);
    }

    public function changePassword()
    {
        $userID = $this->request->getVar('UserID');

        $oldPassword = $this->request->getVar("oldPassword");
        $newPassword = $this->request->getVar("newPassword");

        if (!$oldPassword || !$newPassword) {
            return $this->respond([
                "status" => false,
                "message" => "กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่"
            ], 422);
        }

        $user = $this->auther->find($userID);

        if (!$user) {
            return $this->respond([
                "status" => false,
                "message" => "ไม่พบผู้ใช้งาน"
            ], 404);
        }

        if (!password_verify($oldPassword, $user['int_saksiam_personnel_password'])) {
            return $this->respond([
                "status" => false,
                "message" => "รหัสผ่านเดิมไม่ถูกต้อง"
            ], 400);
        }

        $updated = $this->auther->update($userID, [
            "int_saksiam_personnel_password" => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
        if (!$updated) {
            return $this->respond([
                "status" => false,
                "message" => "อัปเดตไม่สำเร็จ"
            ], 500);
        }
        return $this->respond([
            "status" => true,
            "message" => "เปลี่ยนรหัสผ่านสำเร็จ"
        ]);
    }
    public function showUserDAtaID($userID = null)
    {
        try {
            $UserData = $this->auther->showUserID($userID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $UserData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function updateUserDAtaID($userID = null)
    {
        if (is_null($userID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ])->setStatusCode(400);
        }

        $existingUser = $this->auther->find($userID);
        if (!$existingUser) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        $personalToUpdate = [];

        // ---------- รับค่าจาก request ----------
        $pname = $this->request->getVar('pname');
        $fname = $this->request->getVar('fname');
        $lname = $this->request->getVar('lname');
        $nickname = $this->request->getVar('nickname');
        $birthday = $this->request->getVar('birthday');
        $IDCard = $this->request->getVar('IDCard');
        $address = $this->request->getVar('address');
        $district = $this->request->getVar('district');
        $amphoe = $this->request->getVar('amphoe');
        $province = $this->request->getVar('province');
        $zipcode = $this->request->getVar('zipcode');
        $email = $this->request->getVar('email');
        $phone = $this->request->getVar('phone');
        $phone6 = $this->request->getVar('phone6');
        $role = $this->request->getVar('role'); // หรือ selectedRole
        $status = $this->request->getVar('status');
        $updatename = $this->request->getPost('updatename');
        $changename = $this->request->getPost('changename');

        // ---------- map field ----------
        if ($pname !== null)
            $personalToUpdate['int_saksiam_personnel_pname'] = $pname;
        if ($fname !== null)
            $personalToUpdate['int_saksiam_personnel_fname'] = $fname;
        if ($lname !== null)
            $personalToUpdate['int_saksiam_personnel_lname'] = $lname;
        if ($nickname !== null)
            $personalToUpdate['int_saksiam_personnel_nickname'] = $nickname;
        if ($birthday !== null)
            $personalToUpdate['int_saksiam_personnel_birthday'] = $birthday;
        if ($IDCard !== null)
            $personalToUpdate['int_saksiam_personnel_IDCard'] = $IDCard;
        if ($address !== null)
            $personalToUpdate['int_saksiam_personnel_address'] = $address;
        if ($district !== null)
            $personalToUpdate['int_saksiam_personnel_district'] = $district;
        if ($amphoe !== null)
            $personalToUpdate['int_saksiam_personnel_amphoe'] = $amphoe;
        if ($province !== null)
            $personalToUpdate['int_saksiam_personnel_province'] = $province;
        if ($zipcode !== null)
            $personalToUpdate['int_saksiam_personnel_zipcode'] = $zipcode;
        if ($email !== null)
            $personalToUpdate['int_saksiam_personnel_email'] = $email;

        // ✅ แก้ BUG เดิม: phone ห้ามไปเขียนทับ photo
        if ($phone !== null)
            $personalToUpdate['int_saksiam_personnel_phone'] = $phone;
        if ($phone6 !== null)
            $personalToUpdate['int_saksiam_personnel_phone6'] = $phone6;

        if ($role !== null)
            $personalToUpdate['int_saksiam_personnel_role'] = $role;
        if ($status !== null)
            $personalToUpdate['int_saksiam_personnel_status'] = $status;
        if ($updatename !== null)
            $personalToUpdate['int_saksiam_personnel_updateby'] = $updatename;
        if ($changename !== null)
            $personalToUpdate['int_saksiam_personnel_regisname'] = $changename;

        // ---------- จัดการอัปโหลดรูป ----------
        $photo = $this->request->getFile('photo');

        if ($photo && $photo->isValid() && !$photo->hasMoved()) {

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower($photo->getExtension());

            if (!in_array($ext, $allowedTypes)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid file type'
                ])->setStatusCode(400);
            }

            $uploadDir = WRITEPATH . '../public/Personnel/';

            // ✅ ถ้าโฟลเดอร์ยังไม่มี → สร้าง
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // ✅ unlink เฉพาะ "ไฟล์จริง"
            $oldPhoto = $existingUser['int_saksiam_personnel_photo'] ?? null;

            if (!empty($oldPhoto)) {
                $oldFilePath = WRITEPATH . '../public/' . $oldPhoto;

                if (is_file($oldFilePath)) {
                    @unlink($oldFilePath); // ไม่ throw error
                }
            }

            // ✅ upload ใหม่
            $newFileName = uniqid('Personnel_') . '.' . $ext;

            try {
                $photo->move($uploadDir, $newFileName);
                $personalToUpdate['int_saksiam_personnel_photo'] = 'Personnel/' . $newFileName;
            } catch (\Throwable $e) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Upload photo failed',
                    'error' => $e->getMessage()
                ])->setStatusCode(500);
            }
        }

        // ---------- Update DB ----------
        try {
            if (empty($personalToUpdate)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'No data to update'
                ])->setStatusCode(400);
            }

            $this->auther->updateuserData($userID, $personalToUpdate);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data updated successfully',
                'data' => $personalToUpdate
            ])->setStatusCode(200);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to update data',
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}