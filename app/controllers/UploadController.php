<?php

namespace App\controllers;

use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Exception;

class UploadController
{
    public static function imageUpload(string $imagePath): ResponseInterface
    {
        try {
            if (!$imagePath) {
                return JsonResponse::badRequest(["message" => "No file uploded"]);
            }
            $filename = basename($imagePath);
            return JsonResponse::okResponse(["filename" => $filename]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
