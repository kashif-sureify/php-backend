<?php

namespace App\controllers;

use App\services\AuthService;
use App\utils\Cookie;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AuthController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function signup(array $data): ResponseInterface
    {
        if (
            !isset($data['username']) || trim($data['username']) === '' ||
            !isset($data['email']) || trim($data['email']) === '' ||
            !isset($data['password']) || trim($data['password']) === ''
        ) {
            $this->logger->warning("Signup Validation failed for {data}", ["data" => $data]);
            return JsonResponse::badRequest();
        }

        try {
            $result = AuthService::signup($data['username'], $data['email'], $data['password']);
            if (!$result) {
                $this->logger->info(
                    "Invalid Credentials for this {email}",
                    ["email" => $data['email']]
                );
                return JsonResponse::unauthorized(["message" => "Signup failed"]);
            }

            $this->logger->notice(
                "User signed up successfully: {user}",
                ["user" => $result['user']['email'] ?? "unknown"]
            );
            $response = JsonResponse::createdResponse(["user" => $result['user']]);
            return $response->withAddedHeader(
                'Set-Cookie',
                Cookie::generateCookie($result['token'], 3600)
            );
        } catch (Exception $e) {
            $this->logger->error("Signup exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
    public function login(array $data): ResponseInterface
    {
        if (
            !isset($data['email']) || trim($data['email']) === '' ||
            !isset($data['password']) || trim($data['password']) === ''
        ) {
            $this->logger->warning("Login Validation failed for {data}", ["data" => $data]);
            return JsonResponse::badRequest();
        }

        try {
            $result = AuthService::login($data['email'], $data['password']);
            if (!$result) {
                $this->logger->info("Invalid Credentials for this user: {email}", ["email" => $data['email']]);
                return JsonResponse::unauthorized(["message" => "Invalid credentials"]);
            }

            $this->logger->notice(
                "User logged in successfully: {user}",
                ["user" => $result['user']['email'] ?? "unknown"]
            );
            $response = JsonResponse::okResponse(["user" => $result['user']]);
            return $response->withAddedHeader('Set-Cookie', Cookie::generateCookie($result['token'], 3600));
        } catch (Exception $e) {
            $this->logger->error("Login exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
    public function logout(): ResponseInterface
    {
        $this->logger->info("User logged out successfully");
        $response = JsonResponse::okResponse(["message" => "Logout successfully!"]);
        return $response->withAddedHeader('Set-Cookie', Cookie::generateCookie('', -1));
    }
    public function authCheck(array $user): ResponseInterface
    {
        try {
            if (!$user) {
                $this->logger->warning("Unauthorized access attempt");
                return JsonResponse::unauthorized(["message" => "Unauthorized"]);
            }
            $this->logger->info("Auth check passed, user : {user}", ["user" => $user['email'] ?? "unknown"]);
            return JsonResponse::okResponse(["user" => $user]);
        } catch (Exception $e) {
            $this->logger->error("Auth check exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
