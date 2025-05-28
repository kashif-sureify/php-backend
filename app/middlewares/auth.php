<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function protectRoute()
{
    $token = $_COOKIE['token'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(["status" => 401, "success" => false, "message" => "Not Authorized, No token !"]);
        return;
    }

    try {
        $secret = $_ENV['JWT_SECRET'];
        if (!$secret) {
            throw new Exception("JWT secret not set");
        }

        $decoded = (array) JWT::decode($token, new Key($secret, 'HS256'));
        unset($decoded['password']);

        $_SERVER['user'] = $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["status" => 401, "success" => false, "message" => "Not Authorized, invalid token"]);
        return;
    }
}
