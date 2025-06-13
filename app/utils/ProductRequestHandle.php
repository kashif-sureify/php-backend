<?php

namespace App\utils;

use App\controllers\ProductController;
use App\Logger\FileLogger;
use App\middlewares\UploadMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductRequestHandle
{
    public static function handlePost(
        ServerRequestInterface $request,
        array $data,
        FileLogger $logger
    ): ResponseInterface {
        $uploadMiddleware = new UploadMiddleware('image', false);
        $uploadHandler = new class ($data, $logger) implements RequestHandlerInterface {
            private array $data;
            private FileLogger $logger;

            public function __construct(array $data, FileLogger $logger)
            {
                $this->data = $data;
                $this->logger = $logger;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $imageFileName = $request->getAttribute('uploadedFileName');

                if ($imageFileName !== null) {
                    $this->data['image'] = '/uploads/' . $imageFileName;
                }
                $productController = new ProductController($this->logger);
                return $productController->createProduct($request, $this->data);
            }
        };

        return $uploadMiddleware->process($request, $uploadHandler);
    }

    public static function handlePatch(
        ServerRequestInterface $request,
        int $id,
        array $data,
        FileLogger $logger
    ): ResponseInterface {
        $uploadMiddleware = new UploadMiddleware('image', false);
        $uploadHandler = new class ($id, $data, $logger) implements RequestHandlerInterface {
            private int $id;
            private array $data;
            private FileLogger $logger;

            public function __construct(int $id, array $data, FileLogger $logger)
            {
                $this->id = $id;
                $this->data = $data;
                $this->logger = $logger;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $imageFileName = $request->getAttribute('uploadedFileName', null);

                if ($imageFileName !== null) {
                    $this->data['image'] = '/uploads/' . $imageFileName;
                }

                $productController = new ProductController($this->logger);
                return $productController->updateProduct($request, $this->id, $this->data);
            }
        };

        return $uploadMiddleware->process($request, $uploadHandler);
    }
}
