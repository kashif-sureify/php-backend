<?php

namespace App\config;

use App\config\Database;
use PDOException;
use Exception;

class UserDB
{
    public static function initUserTable(): void
    {
        $userTableSQL = "
        CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
";

        try {
            $db = Database::getConnection();
            $db->exec($userTableSQL);
        } catch (PDOException $e) {
            throw new Exception("Failed to initialized user table: " . $e->getMessage());
        }
    }
}
