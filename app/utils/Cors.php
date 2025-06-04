<?php

namespace App\utils;

class Cors
{
    public static function handle(): void
    {
        header("Content-Type: application/json");

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin === getenv('CLIENT_ORIGIN')) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
        }
    }
}
