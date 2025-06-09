<?php

namespace App\middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

use App\utils\JsonResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        if (!isset($cookies['token'])) {
            return JsonResponse::unauthorized(["message" => "Not Authorized, No token !"]);
        }
        $token = $cookies['token'];
        $secret = getenv('JWT_SECRET') ?? null;
        if (!$secret) {
            return JsonResponse::internalServerError(["message" => "JWT_SECRET not configured"]);
        }

        try {
            $decoded = (array) JWT::decode($token, new Key($secret, 'HS256'));
            unset($decoded['password']);

            $request = $request->withAttribute('user', $decoded);
            return $handler->handle($request);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
