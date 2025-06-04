<?php

namespace App\services;

use App\config\Database;

class ProductService
{
    public static function getProducts($limit, $offset)
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        return $products;
    }

    public static function getTotalProducts()
    {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) as count FROM products";
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch();
        return $row['count'] ?? 0;
    }

    public static function getProductById($id)
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM products WHERE id=:id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, \PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public static function createProduct($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO products (name,description,price,stock,image) 
        VALUES (:name,:description,:price,:stock,:image)
        RETURNING *";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':image' => $data['image'],
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function updateProduct(int $id, array $data): ?array
    {
        $pdo = Database::getConnection();


        $sql = "
                UPDATE products SET
                    name = COALESCE(:name, name),
                    description = COALESCE(:description, description),
                    price = COALESCE(:price, price),
                    stock = COALESCE(:stock, stock),
                    image = COALESCE(:image, image)
                WHERE id = :id
                RETURNING *;
            ";

        $stmt = $pdo->prepare($sql);


        $stmt->bindValue(':name', $data['name'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':stock', $data['stock'] ?? null, \PDO::PARAM_INT);
        $stmt->bindValue(':image', $data['image'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);

        $stmt->execute();
        $updated = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $updated ?: null;
    }



    public static function deleteProduct($id)
    {
        $pdo = Database::getConnection();
        $sql = "DELETE FROM products WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
