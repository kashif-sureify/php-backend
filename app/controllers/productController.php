<?php

require_once dirname(__DIR__) . '/services/productService.php';

class ProductController
{
    public static function getProducts()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;
        $offset = ($page - 1) * $limit;

        try {
            $products = ProductService::getProducts($limit, $offset);
            $total = ProductService::getTotalProducts();
            $totalPages = ceil($total / $limit);

            http_response_code(200);
            echo json_encode([
                "status" => 200,
                "success" => true,
                "page" => $page,
                "limit" => $limit,
                "totalProducts" => $total,
                "totalPages" => $totalPages,
                "data" => $products

            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }

    public static function getProduct($id)
    {
        try {
            $product = ProductService::getProductById((int)$id);
            if ($product) {
                echo json_encode(["status" => 200, "success" => true, "data" => $product]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => 404, "success" => false, "message" => "Product not found"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }

    public static function createProduct($data)
    {

        if (
            !isset($data['name']) || trim($data['name']) === '' ||
            !isset($data['description']) || trim($data['description']) === '' ||
            !isset($data['price']) || !is_numeric($data['price']) ||
            !isset($data['stock']) || !is_numeric($data['stock']) ||
            !isset($data['image']) || trim($data['image']) === ''
        ) {
            http_response_code(400);
            echo json_encode(["status" => 400, "success" => false, "message" => "All fields text are required"]);
            return;
        }

        try {
            $newProduct = ProductService::createProduct($data);
            http_response_code(201);
            echo json_encode(["status" => 201, "success" => true, "message" => "Product created successfully", "data" => $newProduct]);
        } catch (Exception $e) {
            error_log("Create product error " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }

    public static function updateProduct($id, $data, $imagePath = null)
    {
        try {
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
            $updateProduct = ProductService::updateProduct((int)$id, $data);

            if (!$updateProduct) {
                http_response_code(404);
                echo json_encode(["status" => 404, "success" => false, "message" => "Product not found"]);
                return;
            }

            http_response_code(200);
            echo json_encode(["status" => 200, "success" => true, "data" => $updateProduct]);
        } catch (Exception $e) {
            error_log("update product error " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }

    public static function deleteProduct($id)
    {
        try {
            $deleted = ProductService::deleteProduct((int)$id);
            if (!$deleted) {
                http_response_code(404);
                echo json_encode(["status" => 404, "success" => false, "message" => "Product not found"]);
                return;
            }
            http_response_code(200);
            echo json_encode(["status" => 200, "success" => true, "message" => "Product deleted successfully"]);
        } catch (Exception $e) {
            error_log("delete product error " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "success" => false,
                "message" => "Internal Server Error"
            ]);
        }
    }
}
