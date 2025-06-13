<?php

namespace App\middlewares;

use App\utils\JsonResponse;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        try {
            $origin = $request->getHeaderLine('Origin');
            $clientOrigin = getenv('CLIENT_ORIGIN') ?? '';

            if ($request->getMethod() === "OPTIONS") {
                $response = new Response(200);
            } else {
                $response = $handler->handle($request);
            }

            $response = $response->withHeader('Content-Type', 'application/json');

            if ($origin === $clientOrigin) {
                $response = $response->withHeader(
                    'Access-Control-Allow-Origin',
                    $origin
                )
                    ->withHeader(
                        'Access-Control-Allow-Credentials',
                        'true'
                    )
                    ->withHeader(
                        'Access-Control-Allow-Methods',
                        'GET, POST, PUT, DELETE, OPTIONS'
                    )
                    ->withHeader(
                        'Access-Control-Allow-Headers',
                        'Origin, Content-Type, Accept, Authorization,
                     X-Requested-With'
                    );
            }
            return $response;
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
