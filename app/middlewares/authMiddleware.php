<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function protectRoute()
{
    if (!isset($_COOKIE['token'])) {
        http_response_code(401);
        echo json_encode(["status" => 401, "success" => false, "message" => "Not Authorized, No token !"]);
        exit;
    }
    $token = $_COOKIE['token'];

    try {
        $secret = $_ENV['JWT_SECRET'] ?? null;
        if (!$secret) {
            throw new Exception("JWT_SECRET not configured in environment");
        }

        $decoded = (array) JWT::decode($token, new Key($secret, 'HS256'));
        unset($decoded['password']);

        return $GLOBALS['user'] = $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["status" => 401, "success" => false, "message" => "Not Authorized, invalid token"]);
        exit;
    }
}
