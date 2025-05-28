<?php

require_once 'db.php';

function initUserTable(): void
{
    $userTableSQL = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
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

initUserTable();
