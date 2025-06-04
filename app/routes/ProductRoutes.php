<?php

namespace App\routes;

use App\controllers\ProductController;
use App\middlewares\UploadMiddleware;



switch ($reqMethod) {
    case 'GET':
        $id ? ProductController::getProduct($id) : ProductController::getProducts();
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        ProductController::createProduct($data);
        break;
    case 'PATCH':
        $data = json_decode(file_get_contents("php://input"), true);
        $imagePath = UploadMiddleware::handleUpload('image');
        ProductController::updateProduct($id, $data, $imagePath);
        break;
    case 'DELETE':
        ProductController::deleteProduct($id);
        break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
