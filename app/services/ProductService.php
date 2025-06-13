<?php

namespace App\services;

use App\config\Database;
use PDO;
use Throwable;

class ProductService
{
    public static function getProducts(int $limit, int $offset): ?array
    {
        try {
            $pdo = Database::getConnection();
            $sql = "
            SELECT id, name, description, price, stock, image
            FROM products
            WHERE deleted_at IS NULL
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {
            return null;
        }
    }

    public static function getTotalProducts(): int
    {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT COUNT(*) as count FROM products";
            $stmt = $pdo->query($sql);
            $row = $stmt->fetch();
            return (int) ($row['count'] ?? 0);
        } catch (Throwable $th) {
            return 0;
        }
    }

    public static function getProductById(int $id): ?array
    {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT id, name, description, price, stock, image FROM products WHERE id=:id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            return $product ?: null;
        } catch (Throwable $th) {
            return null;
        }
    }

    public static function createProduct(array $data): ?array
    {
        try {
            $pdo = Database::getConnection();
            $sql = "INSERT INTO products (name,description,price,stock,image) 
                    VALUES (:name,:description,:price,:stock,:image)
                    RETURNING id, name, description, price, stock, image";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':image' => $data['image'],
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {
            return null;
        }
    }

    public static function updateProduct(int $id, array $data): ?array
    {

        try {
            $pdo = Database::getConnection();


            $sql = "
                    UPDATE products SET
                        name = COALESCE(:name, name),
                        description = COALESCE(:description, description),
                        price = COALESCE(:price, price),
                        stock = COALESCE(:stock, stock),
                        image = COALESCE(:image, image)
                    WHERE id = :id
                    RETURNING id, name, description, price, stock, image;
                ";

            $stmt = $pdo->prepare($sql);


            $stmt->bindValue(':name', $data['name'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':price', $data['price'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':stock', $data['stock'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':image', $data['image'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {
            return null;
        }
    }

    public static function deleteProduct(int $id): bool
    {
        try {
            $pdo = Database::getConnection();
            $sql = "UPDATE products SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Throwable $th) {
            return false;
        }
    }
}
