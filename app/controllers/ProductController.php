<?php

namespace App\controllers;

use App\services\ProductService;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Exception;

class ProductController
{
    public static function getProducts(): ResponseInterface
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;
        $offset = ($page - 1) * $limit;

        try {
            $products = ProductService::getProducts($limit, $offset);
            $total = ProductService::getTotalProducts();
            $totalPages = ceil($total / $limit);

            return JsonResponse::okResponse([
                "page" => $page,
                "limit" => $limit,
                "totalProducts" => $total,
                "totalPages" => $totalPages,
                "data" => $products
            ]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public static function getProduct(int $id): ResponseInterface
    {
        try {
            $product = ProductService::getProductById($id);
            if (!$product) {
                return JsonResponse::notFound(["message" => "Product not found"]);
            }
            return JsonResponse::okResponse(["data" => $product]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public static function createProduct(array $data): ResponseInterface
    {

        if (
            !isset($data['name']) || trim($data['name']) === '' ||
            !isset($data['description']) || trim($data['description']) === '' ||
            !isset($data['price']) || !is_numeric($data['price']) ||
            !isset($data['stock']) || !is_numeric($data['stock']) ||
            !isset($data['image']) || trim($data['image']) === ''
        ) {
            return JsonResponse::badRequest();
        }

        try {
            $newProduct = ProductService::createProduct($data);
            return JsonResponse::createdResponse(["message" => "Product created successfully", "data" => $newProduct]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public static function updateProduct(int $id, array $data): ResponseInterface
    {
        try {
            if (!isset($data['image'])) {
                unset($data['image']);
            }

            $updatedProduct = ProductService::updateProduct($id, $data);

            if (!$updatedProduct) {
                return JsonResponse::notFound(["message" => "Product not found"]);
            }

            return JsonResponse::okResponse(["data" => $updatedProduct]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }



    public static function deleteProduct(int $id): ResponseInterface
    {
        try {
            $deleted = ProductService::deleteProduct((int)$id);
            if (!$deleted) {
                return JsonResponse::notFound(["message" => "Product not found"]);
            }

            return JsonResponse::okResponse(["message" => "Product deleted successfully"]);
        } catch (Exception $e) {
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
