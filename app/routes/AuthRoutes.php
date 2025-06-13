<?php

namespace App\routes;

use App\controllers\AuthController;
use App\Logger\FileLogger;
use App\middlewares\AuthMiddleware;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthRoutes
{
    public static function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        $data = json_decode((string) $request->getBody(), true) ?? [];

        $logger = new FileLogger(dirname(__DIR__) . './../storage/log/app.log');
        $authController = new AuthController($logger);

        if ($method === "GET" && $path == "/api/v1/auth/authCheck") {
            $middleware = new AuthMiddleware();

            $handler = new class ($authController) implements RequestHandlerInterface {
                private AuthController $controller;

                public function __construct(AuthController $controller)
                {
                    $this->controller = $controller;
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    $user = $request->getAttribute('user');
                    return $this->controller->authCheck($user);
                }
            };

            return $middleware->process($request, $handler);
        }

        if ($method === "POST") {
            return match ($path) {
                '/api/v1/auth/signup' => $authController->signup($data),
                '/api/v1/auth/login' => $authController->login($data),
                '/api/v1/auth/logout' => $authController->logout($data),

                default => JsonResponse::notFound(["message" => "Route not found"]),
            };
        }

        return JsonResponse::methodNotAllowed();
    }
}
