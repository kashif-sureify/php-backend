<?php

require_once dirname(__DIR__) . '/services/authService.php';


class AuthController
{
    public static function signup($data)
    {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["status" => 400, "success" => false, "message" => "All field required"]);
            return;
        }

        try {
            $result = AuthService::signup($data['username'], $data['email'], $data['password']);
            if (!$result) {
                http_response_code(401);
                echo json_encode(["status" => 401, "success" => false, "message" => "Signup failed"]);
                return;
            }

            setcookie('token', $result['token'], [
                'expires' => time() + 3600,
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => $_ENV['PHP_ENV'] === 'production',
                'path' => '/'
            ]);

            http_response_code(201);
            echo json_encode(["status" => 201, "success" => true, "user" => $result['user']]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => 500, "success" => false, "message" => "Internal Server Error", "error" => $e->getMessage()]);
        }
    }
    public static function login($data)
    {
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["status" => 400, "success" => false, "message" => "All field required"]);
            return;
        }

        try {
            $result = AuthService::login($data['email'], $data['password']);
            if (!$result) {
                http_response_code(401);
                echo json_encode(["status" => 401, "success" => false, "message" => "Invalid credentials"]);
                return;
            }

            setcookie('token', $result['token'], [
                'expires' => time() + 3600,
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => $_ENV['PHP_ENV'] === 'production',
                'path' => '/'
            ]);

            http_response_code(200);
            echo json_encode(["status" => 200, "success" => true, "user" => $result['user']]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => 500, "success" => false, "message" => "Internal Server Error"]);
        }
    }
    public static function logout()
    {
        setcookie('token', '', time() - 3600, '/');
        http_response_code(200);
        echo json_encode(["status" => 200, "success" => true, "message" => "Logout successfully!"]);
    }
    public static function authCheck()
    {
        global $user;
        try {
            if (!$user) {
                http_response_code(401);
                echo json_encode(["status" => 401, "success" => false, "message" => "Unauthorized"]);
                return;
            }

            http_response_code(200);
            echo json_encode(["status" => 200, "success" => true, "user" => $user]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => 500, "success" => false, "message" => "Internal Server Error"]);
        }
    }
}
