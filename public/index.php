<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

use App\config\ProductDB;
use App\config\UserDB;
use App\middlewares\CorsMiddleware;
use App\routes\AuthRoutes;
use App\routes\ProductRoutes;
use App\routes\UploadRoutes;
use App\utils\JsonResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

ProductDB::initProductTable();
UserDB::initUserTable();

$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

$request = $creator->fromGlobals();

$corsMiddleware = new CorsMiddleware();
$response = $corsMiddleware->process($request, new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(200);
    }
});

$uri = $request->getUri()->getPath();
if (str_starts_with($uri, '/api/uploads')) {
    $filename = basename($uri);
    $filePath = dirname(__DIR__) . '/uploads/' . $filename;
    if (file_exists($filePath)) {
        header('Content-Type:' . mime_content_type($filePath));
        readfile($filePath);
    } else {
        JsonResponse::notFound(["message" => "File not found"]);
    }
    exit;
}

$segments = explode('/', trim($uri, '/'));

if (isset($segments[0]) && $segments[0] === 'api') {
    if (isset($segments[1]) && $segments[1] === "v1" && isset($segments[2])) {
        $resource = $segments[2];

        switch ($resource) {
            case 'auth':
                $response = AuthRoutes::handle($request);
                break;
            case 'products':
                $response = ProductRoutes::handle($request, isset($segments[3]) ? (int)$segments[3] : null);
                break;
            case 'upload':
                $response = UploadRoutes::handle($request);
                break;

            default:
                $response = JsonResponse::notFound(["message" => "Route not found"]);
        }
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $key => $value) {
            foreach ($value as $value) {
                header("$key: $value", false);
            }
        }
        echo $response->getBody();
        exit;
    }
}

JsonResponse::notFound(["message" => "Invalid Url"]);
exit;
