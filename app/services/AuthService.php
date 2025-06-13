<?php

namespace App\services;

use Firebase\JWT\JWT;
use App\config\Database;
use PDO;
use PDOException;
use Exception;

class AuthService
{
    public static function signup(string $username, string $email, string $password): ?array
    {
        try {
            $pdo = Database::getConnection();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (:username,:email,:password)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                null;
            }
            $token = JWT::encode(
                [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'exp' => time() + 3600
                ],
                getenv('JWT_SECRET'),
                'HS256'
            );

            return ['token' => $token, 'user' => $user];
        } catch (PDOException $e) {
            error_log('Signup PDOException: ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log('Signup Exception: ' . $e->getMessage());
            return null;
        }
    }
    public static function login(string $email, string $password): ?array
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                return null;
            }

            unset($user['password']);

            $token = JWT::encode(
                [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'exp' => time() + 3600
                ],
                getenv('JWT_SECRET'),
                'HS256'
            );

            return ['token' => $token, 'user' => $user];
        } catch (PDOException $e) {
            error_log('login PDOException: ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log('login Exception: ' . $e->getMessage());
            return null;
        }
    }
}
