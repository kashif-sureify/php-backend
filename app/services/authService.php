<?php

require_once dirname(__DIR__) . '/config/db.php';

use Firebase\JWT\JWT;

class AuthService
{
    public static function signup($username, $email, $password)
    {
        $pdo = Database::getConnection();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (:username,:email,:password)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return null;
        $token = JWT::encode(['id' => $user['id'], 'email' => $user['email'], 'exp' => time() + 3600], $_ENV['JWT_SECRET'], 'HS256');

        return ['token' => $token, 'user' => $user];
    }
    public static function login($email, $password)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        $token = JWT::encode(['id' => $user['id'], 'email' => $user['email'], 'exp' => time() + 3600], $_ENV['JWT_SECRET'], 'HS256');

        return ['token' => $token, 'user' => $user];
    }
}
