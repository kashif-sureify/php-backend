<?php

namespace App\routes;

use App\controllers\AuthController;
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
        if ($method === "GET" && $path == "/api/v1/auth/authCheck") {
            $middleware = new AuthMiddleware();

            $handler = new class implements RequestHandlerInterface {

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    $user = $request->getAttribute('user');
                    return AuthController::authCheck($user);
                }
            };

            return $middleware->process($request, $handler);
        }

        if ($method === "POST") {
            return match ($path) {
                '/api/v1/auth/signup' => AuthController::signup($data),
                '/api/v1/auth/login' => AuthController::login($data),
                '/api/v1/auth/logout' => AuthController::logout(),

                default => JsonResponse::notFound(["message" => "Route not found"]),
            };
        }

        return JsonResponse::methodNotAllowed();
    }
}
