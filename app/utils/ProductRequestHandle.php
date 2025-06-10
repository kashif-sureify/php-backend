<?php

namespace App\utils;

use App\controllers\ProductController;
use App\middlewares\UploadMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductRequestHandle
{
    public static function handlePost(ServerRequestInterface $request, array $data): ResponseInterface
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
                $imageFileName = $request->getAttribute('uploadedFileName');

                if ($imageFileName !== null) {
                    $this->data['image'] = '/uploads/' . $imageFileName;
                }
                return ProductController::createProduct($request,$this->data);
            }
        };

        return $uploadMiddleware->process($request, $uploadHandler);
    }

    public static function handlePatch(ServerRequestInterface $request, int $id, array $data): ResponseInterface
    {
        $uploadMiddleware = new UploadMiddleware('image', false);
        $uploadHandler = new class($id, $data) implements RequestHandlerInterface {
            private int $id;
            private array $data;

            public function __construct(int $id, array $data)
            {
                $this->id = $id;
                $this->data = $data;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $imageFileName = $request->getAttribute('uploadedFileName', null);

                if ($imageFileName !== null) {
                    $this->data['image'] = '/uploads/' . $imageFileName;
                }

                return ProductController::updateProduct($request,$this->id, $this->data);
            }
        };

        return $uploadMiddleware->process($request, $uploadHandler);
    }
}
