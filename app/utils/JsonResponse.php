<?php

namespace App\utils;

use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

final class JsonResponse
{
    public static function sendJsonResponse(int $status, array $body): ResponseInterface
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    public static function okResponse(?array $error = null): ResponseInterface
    {
        $body = [
            "status" => 200,
            "success" => true,
        ];
        return self::sendJsonResponse(200, array_merge($body, $error ?? []));
    }

    public static function createdResponse(?array $error = null): ResponseInterface
    {
        $body = [
            "status" => 201,
            "success" => true,
        ];
        return self::sendJsonResponse(201, array_merge($body, $error ?? []));
    }

    public static function badRequest(?array $error = null): ResponseInterface
    {
        $body = ["status" => 400, "success" => false, "message" => "All fields are required"];
        return self::sendJsonResponse(400, array_merge($body, $error ?? []));
    }

    public static function unauthorized(?array $error = null): ResponseInterface
    {
        $body = ["status" => 401, "success" => false];
        return self::sendJsonResponse(401, array_merge($body, $error ?? []));
    }

    public static function notFound(?array $error = null): ResponseInterface
    {
        $body = ["status" => 404, "success" => false];
        return self::sendJsonResponse(404, array_merge($body, $error ?? []));
    }

    public static function methodNotAllowed(?array $error = null): ResponseInterface
    {
        $body = ["status" => 405, "success" => false, "message" => "Method not allowed"];
        return self::sendJsonResponse(405, array_merge($body, $error ?? []));
    }


    public static function internalServerError(?array $error = null): ResponseInterface
    {
        $body = ["status" => 500, "success" => false, "message" => "Internal Server Error"];
        return self::sendJsonResponse(500, array_merge($body, $error ?? []));
    }
}
