<?php

namespace App\routes;

use App\controllers\UploadController;
use App\middlewares\UploadMiddleware;


switch ($reqMethod) {


    case 'POST':
    case 'PATCH':
        $imagePath = UploadMiddleware::handleUpload('image');
        UploadController::imageUpload($imagePath);
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
