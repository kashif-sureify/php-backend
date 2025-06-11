<?php

namespace App\controllers;

use App\services\ProductService;
use App\utils\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ProductController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getProducts(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 6;
        $offset = ($page - 1) * $limit;

        try {
            $products = ProductService::getProducts($limit, $offset);
            $total = ProductService::getTotalProducts();
            $totalPages = ceil($total / $limit);
            $this->logger->info("totalProducts:{totalProducts} totalPages:{totalPages} page:{page} limit:{limit}", [
                "totalProducts" => $total,
                "totalPages" => $totalPages,
                "page" => $page,
                "limit" => $limit,
            ]);

            return JsonResponse::okResponse([
                "page" => $page,
                "limit" => $limit,
                "totalProducts" => $total,
                "totalPages" => $totalPages,
                "data" => $products
            ]);
        } catch (Exception $e) {
            $this->logger->error("Get all products exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public function getProduct(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $clientIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';

        try {
            $product = ProductService::getProductById($id);
            if (!$product) {
                $this->logger->warning("Product with {id} ID not found", ["id" => $id]);
                return JsonResponse::notFound(["message" => "Product not found"]);
            }
            $this->logger->info("Product: {data}, ClientIP: {IP}", ["data" => json_encode($product), "IP" => $clientIp]);
            return JsonResponse::okResponse(["data" => $product]);
        } catch (Exception $e) {
            $this->logger->error("Get a product exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public function createProduct(ServerRequestInterface $request, array $data): ResponseInterface
    {

        if (
            !isset($data['name']) || trim($data['name']) === '' ||
            !isset($data['description']) || trim($data['description']) === '' ||
            !isset($data['price']) || !is_numeric($data['price']) ||
            !isset($data['stock']) || !is_numeric($data['stock']) ||
            !isset($data['image']) || trim($data['image']) === ''
        ) {
            $this->logger->warning("Some fields are not filled   {data}", ["data" => $data]);
            return JsonResponse::badRequest();
        }

        try {
            $newProduct = ProductService::createProduct($data);
            $this->logger->notice("New product added: {data}", ["data" => json_encode($newProduct)]);
            return JsonResponse::createdResponse(["message" => "Product created successfully", "data" => $newProduct]);
        } catch (Exception $e) {
            $this->logger->error("Create a product exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }

    public function updateProduct(ServerRequestInterface $request, int $id, array $data): ResponseInterface
    {
        try {
            if (!isset($data['image'])) {
                unset($data['image']);
            }

            $updatedProduct = ProductService::updateProduct($id, $data);

            if (!$updatedProduct) {
                $this->logger->warning("No product found with this {ID}", ["ID" => $id]);
                return JsonResponse::notFound(["message" => "Product not found"]);
            }

            $this->logger->info("Product updated: {data}", ["data" => json_encode($updatedProduct)]);
            return JsonResponse::okResponse(["data" => $updatedProduct]);
        } catch (Exception $e) {
            $this->logger->error("Update product exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }



    public function deleteProduct(ServerRequestInterface $request, int $id): ResponseInterface
    {
        try {
            $deleted = ProductService::deleteProduct((int)$id);
            if (!$deleted) {
                $this->logger->warning("No product found with this {ID}", ["ID" => $id]);
                return JsonResponse::notFound(["message" => "Product not found"]);
            }

            $this->logger->info("Product deleted with Id: {ID}", ["ID" => $id]);
            return JsonResponse::okResponse(["message" => "Product deleted successfully"]);
        } catch (Exception $e) {
            $this->logger->error("Delete product exception: {exception}", ["exception" => $e]);
            return JsonResponse::internalServerError(["error" => $e->getMessage()]);
        }
    }
}
