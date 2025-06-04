<?php

namespace App\controllers;


class UploadController
{
    public static function imageUpload($imagePath)
    {
        try {
            if (!$imagePath) {
                http_response_code(400);
                echo json_encode(["message" => "No file uploded"]);
                return;
            }
            $filename = basename($imagePath);
            http_response_code(200);
            echo json_encode(["filename" => $filename]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }
}
