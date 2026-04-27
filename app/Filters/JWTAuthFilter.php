<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JWTAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
      $authorizationHeader =
            $request->getHeaderLine('Authorization')
            ?: $request->getServer('HTTP_AUTHORIZATION')
            ?: $request->getServer('REDIRECT_HTTP_AUTHORIZATION');

        if (!$authorizationHeader) {
            return Services::response()->setStatusCode(401)->setJSON([
                "status" => false,
                "message" => "Missing Authorization Header"
            ]);
        }

         // ------------------------------
        // 1) ตรวจรูปแบบ Bearer Token
        // ------------------------------
        $parts = explode(" ", $authorizationHeader);

        if (count($parts) !== 2 || strtolower($parts[0]) !== "bearer") {
            return Services::response()->setStatusCode(401)->setJSON([
                "status" => false,
                "message" => "Invalid Authorization Header"
            ]);
        }

        $encodedToken = $parts[1];
// ------------------------------
        // 2) ถอด base64 3 ชั้น
        // ------------------------------
        try {
            $level1 = base64_decode($encodedToken, true);
            $level2 = base64_decode($level1, true);
            $jwtToken = base64_decode($level2, true);

            // ถ้า decode ชั้นใดชั้นหนึ่ง error → token ผิดรูปแบบ
            if ($level1 === false || $level2 === false || $jwtToken === false) {
                return Services::response()->setStatusCode(401)->setJSON([
                    "status" => false,
                    "message" => "Invalid token encoding"
                ]);
            }

        } catch (\Throwable $e) {
            return Services::response()->setStatusCode(401)->setJSON([
                "status" => false,
                "message" => "Invalid token encoding"
            ]);
        }

        $key = getenv('JWT_KEY');
 // ------------------------------
        // 3) Decode JWT
        // ------------------------------
        try {
            $decoded = JWT::decode($jwtToken, new Key($key, 'HS256'));

        } catch (ExpiredException $e) {
            return Services::response()->setStatusCode(401)->setJSON([
                "status" => false,
                "message" => "Token expired"
            ]);

        } catch (\Exception $e) {
            return Services::response()->setStatusCode(401)->setJSON([
                "status" => false,
                "message" => "Invalid token"
            ]);
        }
        // ------------------------------
        // 4) แนบข้อมูล user เข้า request
        // ------------------------------
        $request->decodedToken = (array) $decoded;

        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
