<?php

namespace App\routes;

use App\controllers\ProductController;
use App\middlewares\AuthMiddleware;
use App\middlewares\UploadMiddleware;
use App\utils\JsonResponse;
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

                if (str_contains($contentType, 'application/json')) {
                    $data = json_decode((string) $request->getBody(), true) ?? [];
                } else {
                    $data = $request->getParsedBody() ?? [];
                }

                return match ($method) {
                    'GET' => $this->id ? ProductController::getProduct($this->id) : ProductController::getProducts(),
                    'POST' => $this->handlePostWithUpload($request, $data),
                    'PATCH' => $this->handlePatch($request, $data),
                    'DELETE' => ProductController::deleteProduct($this->id),

                    default => JsonResponse::methodNotAllowed(),
                };
            }

            private function handlePatch(ServerRequestInterface $request, array $data): ResponseInterface
            {
                $uploadMiddleware = new UploadMiddleware('image', false); // ← image not required now

                $uploadHandler = new class($this->id, $data) implements RequestHandlerInterface {
                    private ?int $id;
                    private array $data;

                    public function __construct(?int $id, array $data)
                    {
                        $this->id = $id;
                        $this->data = $data;
                    }

                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        $imagePath = $request->getAttribute('uploadedFileName', null);

                        if ($imagePath !== null) {
                            $this->data['image'] = '/uploads/' . $imagePath;
                        }

                        return ProductController::updateProduct($this->id, $this->data);
                    }
                };

                return $uploadMiddleware->process($request, $uploadHandler);
            }

            private function handlePostWithUpload(ServerRequestInterface $request, array $data): ResponseInterface
            {
                $uploadMiddleware = new UploadMiddleware('image', false);

                $uploadHandler = new class($data) implements RequestHandlerInterface {
                    private array $data;

                    public function __construct(array $data)
                    {
                        $this->data = $data;
                    }

                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        $imagePath = $request->getAttribute('uploadedFileName', null);

                        if ($imagePath !== null) {
                            $this->data['image'] = '/uploads/' . $imagePath;
                        }

                        return ProductController::createProduct($this->data);
                    }
                };

                return $uploadMiddleware->process($request, $uploadHandler);
            }
        };



        return $authMiddleware->process($request, $handler);
    }
}
