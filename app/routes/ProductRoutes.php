<?php

namespace App\routes;

use App\controllers\ProductController;
use App\Logger\FileLogger;
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
            private FileLogger $logger;

            public function __construct(ServerRequestInterface $request, ?int $id)
            {
                $this->request = $request;
                $this->id = $id;
                $this->logger = new FileLogger(dirname(__DIR__) . './../storage/log/app.log');
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $method = $request->getMethod();
                $contentType = $request->getHeaderLine('Content-Type');

                $data = (str_contains($contentType, 'application/json') ? json_decode((string) $request->getBody(), true) ?? [] : $request->getParsedBody() ?? []);

                $productController = new ProductController($this->logger);

                return match ($method) {
                    'GET' => $this->id ? $productController->getProduct($request, $this->id) : $productController->getProducts($request),
                    'POST' => ProductRequestHandle::handlePost($request, $data, $this->logger),
                    'PATCH' => ProductRequestHandle::handlePatch($request, $this->id, $data, $this->logger),
                    'DELETE' => $productController->deleteProduct($request, $this->id),

                    default => JsonResponse::methodNotAllowed(),
                };
            }
        };



        return $authMiddleware->process($request, $handler);
    }
}
