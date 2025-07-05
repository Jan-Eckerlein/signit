<?php

namespace App\Models\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

trait HasFlexiblePagination
{
    /**
     * Paginate a query with optional bypassing for all records
     * 
     * @param Builder $query The query to paginate
     * @param Request $request The request object
     * @param string|null $resourceClass Custom resource class to use (optional)
     * @param int $defaultPerPage Default items per page
     * @param int $maxPerPage Maximum items per page (safety limit)
	 * 
     * IMPORTANT: For API documentation, add the #[\App\Attributes\SharedPaginationParams] attribute to controller methods using this method,
     * so that shared pagination query parameters are included in the generated docs.
     * @return ResourceCollection
     */
    public static function paginateOrGetAll(
        Builder $query, 
        Request $request, 
        ?string $resourceClass = null,
        int $defaultPerPage = 20, 
        int $maxPerPage = 1000
    ): ResourceCollection {
        // Allow bypassing pagination with ?all=true
        if ($request->boolean('all')) {
            return static::toResourceCollection($query->get(), $resourceClass);
        }
        
        // Use per_page from query parameter, default to provided value
        $perPage = $request->get('per_page', $defaultPerPage);
        
        // Safety limit to prevent abuse
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }
        
        return static::toResourceCollection($query->paginate($perPage), $resourceClass);
    }

    /**
     * Convert a collection to a resource collection
     * This method should be overridden in the model if needed
     */
    public static function toResourceCollection($collection, ?string $resourceClass = null): ResourceCollection
    {
        // Use provided resource class or fall back to default
        $resourceClass = $resourceClass ?? static::getResourceClass();
        
        if (is_string($resourceClass) && class_exists($resourceClass)) {
            return $resourceClass::collection($collection);
        }
        
        // Fallback to generic resource
        return static::makeFallbackResource()::collection($collection);
    }

    /**
     * Get the resource class for this model
     * This method should be overridden in the model 
	 * if it doesnt follow the default naming convention of "Resource"
	 * like App\Http\Resources\<ModelName>Resource
     */
    public static function getResourceClass(): string
    {
        $modelName = class_basename(static::class);
        $resourceClass = "App\\Http\\Resources\\{$modelName}Resource";

        if (class_exists($resourceClass)) {
            return $resourceClass;
        }

        // Return the fallback resource class name
        return static::makeFallbackResource()::class;
    }

    /**
     * Create a fallback resource class for when no specific resource exists
     */
    protected static function makeFallbackResource(): string
    {
        return new class extends JsonResource {
            public function toArray($request): array
            {
                return $this->resource instanceof \JsonSerializable
                    ? (array) $this->resource->jsonSerialize()
                    : (array) $this->resource;
            }

            public static function collection($resource)
            {
                return new ResourceCollection($resource, static::class);
            }
        }::class;
    }
} 