<?php

namespace App\config;

use App\config\Database;
use PDOException;
use Exception;


class ProductDB
{

    public static function initProductTable(): void
    {
        $productTableSQL = "
        CREATE TABLE IF NOT EXISTS products (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
";

        try {
            $db = Database::getConnection();
            $db->exec($productTableSQL);
        } catch (PDOException $e) {
            throw new Exception("Failed to initialized product table: " . $e->getMessage());
        }
    }
}
