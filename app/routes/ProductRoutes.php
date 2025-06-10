<?php

namespace App\routes;

use App\controllers\ProductController;
use App\middlewares\AuthMiddleware;
use App\utils\JsonResponse;
use App\utils\ProductRequestHandle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductRoutes
{
    public static function handle(ServerRequestInterface $request, ?int $id = null): ResponseInterface
    {
        $authMiddleware = new AuthMiddleware();

        $handler = new class($request, $id) implements RequestHandlerInterface {
            private ServerRequestInterface $request;
            private ?int $id;

            public function __construct(ServerRequestInterface $request, ?int $id)
            {
                $this->request = $request;
                $this->id = $id;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $method = $request->getMethod();
                $contentType = $request->getHeaderLine('Content-Type');

                $data = (str_contains($contentType, 'application/json') ? json_decode((string) $request->getBody(), true) ?? [] : $request->getParsedBody() ?? []);

                return match ($method) {
                    'GET' => $this->id ? ProductController::getProduct($request, $this->id) : ProductController::getProducts($request),
                    'POST' => ProductRequestHandle::handlePost($request, $data),
                    'PATCH' => ProductRequestHandle::handlePatch($request, $this->id, $data),
                    'DELETE' => ProductController::deleteProduct($request,$this->id),

                    default => JsonResponse::methodNotAllowed(),
                };
            }
        };



        return $authMiddleware->process($request, $handler);
    }
}
