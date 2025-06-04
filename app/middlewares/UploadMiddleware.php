<?php

namespace App\middlewares;

class UploadMiddleware
{
    public static function handleUpload($fieldName)
    {
        $uploadDir = '/var/www/html/uploads';


        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload failed or file not received. _FILES: " . print_r($_FILES, true));
            return null;
        }

        if ($_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(["message" => "File too large. Max 5MB."]);
            exit;
        }

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = basename($_FILES[$fieldName]['name']);
        $fileName = time() . '-' . $originalName;
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
            return $fileName;
        } else {
            http_response_code(500);
            echo json_encode([
                "message" => "Failed to upload file",
                "error" => $_FILES[$fieldName]['error'],
                "targetPath" => $targetPath
            ]);
            exit;
        }
    }
}
