<?php

namespace App\routes;

use App\controllers\UploadController;
use App\middlewares\UploadMiddleware;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadRoutes
{
    public static function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();

        return match ($method) {
            'POST', 'PATCH' => self::handleUpload($request),
            default => JsonResponse::methodNotAllowed(),
        };
    }

    private static function handleUpload(ServerRequestInterface $request): ResponseInterface
    {
        $uploadMiddleware = new UploadMiddleware('image');
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $imagePath = $request->getAttribute('uploadedFileName', null);
                return UploadController::imageUpload($imagePath);
            }
        };

        return $uploadMiddleware->process($request, $handler);
    }
}
