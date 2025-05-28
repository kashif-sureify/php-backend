<?php
require_once dirname(__DIR__) . '/middlewares/upload.php';
require_once dirname(__DIR__) . '/controllers/uploadController.php';


switch ($reqMethod) {

    case 'POST':
    case 'PATCH':
        $imagePath = handleUpload('image');
        UploadController::imageUpload($imagePath);
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
