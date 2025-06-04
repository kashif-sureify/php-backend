<?php

namespace App\routes;

use App\controllers\AuthController;
use App\middlewares\AuthMiddleware;

switch ($reqMethod) {
    case "POST":
        $data = json_decode(file_get_contents('php://input'), true);
        if ($reqURL === "/api/v1/auth/signup") {
            AuthController::signup($data);
        } else if ($reqURL === "/api/v1/auth/login") {
            AuthController::login($data);
        } else if ($reqURL === "/api/v1/auth/logout") {
            AuthController::logout();
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Route not found"]);
        }
        break;
    case "GET":
        if ($reqURL === "/api/v1/auth/authCheck") {
            if (!AuthMiddleware::protectRoute()) return;
            AuthController::authCheck();
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Route not found"]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}
