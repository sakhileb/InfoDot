<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;

trait ApiResourceHandler
{
    /**
     * Respond with a single resource
     */
    protected function respondWithResource(JsonResource $resource, string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $resource,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respond with a resource collection
     */
    protected function respondWithCollection(ResourceCollection $collection, string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $collection,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respond with success message and optional data
     */
    protected function respondWithSuccess(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respond with an error
     */
    protected function respondWithError(string $message, int $statusCode = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle resource class errors gracefully
     */
    protected function safeResourceTransform(string $resourceClass, mixed $data): mixed
    {
        try {
            if (!class_exists($resourceClass)) {
                throw new \Exception("Resource class {$resourceClass} not found");
            }
            
            return new $resourceClass($data);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Resource transformation error: " . $e->getMessage(), [
                'resource_class' => $resourceClass,
                'data_type' => gettype($data),
                'data_id' => is_object($data) && method_exists($data, 'getKey') ? $data->getKey() : null
            ]);
            
            // Return a fallback response
            return response()->json([
                'error' => 'Resource transformation failed',
                'message' => 'Unable to format response data properly',
                'data' => $this->getFallbackData($data)
            ], 500);
        }
    }

    /**
     * Handle resource collection errors gracefully
     */
    protected function safeResourceCollection(string $resourceClass, mixed $collection): mixed
    {
        try {
            if (!class_exists($resourceClass)) {
                throw new \Exception("Resource class {$resourceClass} not found");
            }
            
            return $resourceClass::collection($collection);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Resource collection transformation error: " . $e->getMessage(), [
                'resource_class' => $resourceClass,
                'collection_type' => gettype($collection),
                'collection_count' => is_countable($collection) ? count($collection) : 'unknown'
            ]);
            
            // Return a fallback response
            return response()->json([
                'error' => 'Resource collection transformation failed',
                'message' => 'Unable to format collection data properly',
                'data' => $this->getFallbackCollectionData($collection)
            ], 500);
        }
    }

    /**
     * Get fallback data for single resource transformation errors
     */
    private function getFallbackData(mixed $data): array
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        return ['raw_data' => $data];
    }

    /**
     * Get fallback data for collection transformation errors
     */
    private function getFallbackCollectionData(mixed $collection): array
    {
        if (is_object($collection) && method_exists($collection, 'toArray')) {
            return $collection->toArray();
        }
        
        if (is_array($collection)) {
            return $collection;
        }
        
        if (is_iterable($collection)) {
            return iterator_to_array($collection);
        }
        
        return ['raw_collection' => $collection];
    }

    /**
     * Create a consistent error response for API endpoints
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $additionalData = []): JsonResponse
    {
        $response = [
            'error' => true,
            'message' => $message,
            'status_code' => $statusCode
        ];

        if (!empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a consistent success response for API endpoints
     */
    protected function successResponse(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode
        ], $statusCode);
    }
}
