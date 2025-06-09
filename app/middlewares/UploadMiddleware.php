<?php

namespace App\middlewares;

use App\utils\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Exception;

class UploadMiddleware implements MiddlewareInterface
{
    private string $fieldName;
    private bool $isRequired;
    private string $uploadDir = '/var/www/html/uploads';

    public function __construct(string $fieldName, bool $isRequired = true)
    {
        $this->fieldName = $fieldName;
        $this->isRequired = $isRequired;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();

        if (!isset($uploadedFiles[$this->fieldName])) {
            if ($this->isRequired) {
                return JsonResponse::badRequest(["message" => "File '{$this->fieldName}' not uploaded"]);
            }
            return $handler->handle($request);
        }

        $uploadedFile = $uploadedFiles[$this->fieldName];

        if ($uploadedFile->getError() === UPLOAD_ERR_NO_FILE) {
            if ($this->isRequired) {
                return JsonResponse::badRequest(["message" => "No file uploaded"]);
            }
            return $handler->handle($request);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::badRequest(["message" => "Upload error code: {$uploadedFile->getError()}"]);
        }

        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return JsonResponse::badRequest(["message" => "File too large. Max 5MB."]);
        }

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $originalName = pathinfo($uploadedFile->getClientFilename(), PATHINFO_BASENAME);
        $fileName = time() . '-' . $originalName;
        $targetPath = $this->uploadDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            $uploadedFile->moveTo($targetPath);
            $request = $request->withAttribute('uploadedFileName', $fileName);
            return $handler->handle($request);
        } catch (Exception $e) {
            return JsonResponse::unauthorized(["message" => "Failed to upload file"]);
        }
    }
}
