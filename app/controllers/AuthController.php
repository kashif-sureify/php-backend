<?php

namespace App\controllers;

use App\services\AuthService;
use App\utils\Cookie;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Exception;


class AuthController
{
    public static function signup(array $data): ResponseInterface
    {
        if (!isset($data['username']) || trim($data['username']) === '' || !isset($data['email']) || trim($data['email']) === '' || !isset($data['password']) || trim($data['password']) === '') {
            return JsonResponse::badRequest();
        }

        try {
            $result = AuthService::signup($data['username'], $data['email'], $data['password']);
            if (!$result) {
                return JsonResponse::unauthorized(["message" => "Signup failed"]);
            }

            $response = JsonResponse::createdResponse(["user" => $result['user']]);
            return $response->withAddedHeader('Set-Cookie', Cookie::generateCookie($result['token'], 3600));
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
    public static function login(array $data): ResponseInterface
    {
        if (!isset($data['email']) || trim($data['email']) === '' || !isset($data['password']) || trim($data['password']) === '') {
            return JsonResponse::badRequest();
        }

        try {
            $result = AuthService::login($data['email'], $data['password']);
            if (!$result) {
                return JsonResponse::unauthorized(["message" => "Invalid credentials"]);
            }

            $response = JsonResponse::okResponse(["user" => $result['user']]);
            return $response->withAddedHeader('Set-Cookie', Cookie::generateCookie($result['token'], 3600));
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
    public static function logout(): ResponseInterface
    {
        $response = JsonResponse::okResponse(["message" => "Logout successfully!"]);
        return $response->withAddedHeader('Set-Cookie', Cookie::generateCookie('', -1));
    }
    public static function authCheck(array $user): ResponseInterface
    {
        try {
            if (!$user) {
                return JsonResponse::unauthorized(["message" => "Unauthorized"]);
            }
            return JsonResponse::okResponse(["user" => $user]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
