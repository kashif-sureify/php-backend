<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


require_once dirname(__DIR__) . '/app/config/productDB.php';
require_once dirname(__DIR__) . '/app/config/userDB.php';


require_once dirname(__DIR__) . '/app/utils/cors.php';

// Parse request URL and method
$rawURL = $_SERVER["REQUEST_URI"];
$reqURL = parse_url($rawURL, PHP_URL_PATH);
$segments = explode("/", trim($reqURL, "/"));
$reqMethod = $_SERVER["REQUEST_METHOD"];


$routes = [
    'auth' => dirname(__DIR__) . '/app/routes/authRoutes.php',
    'products' => dirname(__DIR__) . '/app/routes/productRoutes.php',
    'upload' => dirname(__DIR__) . '/app/routes/uploadRoutes.php',
];

if (strpos($reqURL, '/api/uploads') === 0) {
    $filename = basename($reqURL);
    $filePath = dirname(__DIR__) . '/uploads/' . $filename;
    if (file_exists($filePath)) {
        header('Content-Type:' . mime_content_type($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(["status" => 404, "success" => false, "message" => "File not found"]);
        exit;
    }
}

if (isset($segments[0]) && $segments[0] === "api") {

    if (isset($segments[1]) && $segments[1] === "v1" && isset($segments[2])) {
        $resource = $segments[2];
        if ($resource === "auth" && isset($routes[$resource])) {
            require_once $routes[$resource];
            exit;
        }
    }

    if (isset($segments[1])) {
        $resource = $segments[1];
        $id = $segments[2] ?? null;
        if (array_key_exists($resource, $routes)) {

            if ($resource === "products") {
                require_once dirname(__DIR__) . '/app/middlewares/authMiddleware.php';
                if (!protectRoute()) {
                    http_response_code(401);
                            echo json_encode(["error" => "Unauthorized"]);
                    exit;
                }
            }

            require_once $routes[$resource];
            exit;
        }
    }
}

http_response_code(404);
echo json_encode(["message" => "Invalid Url"]);
exit;
