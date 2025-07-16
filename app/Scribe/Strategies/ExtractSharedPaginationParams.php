<?php

namespace App\Scribe\Strategies;

use App\Attributes\SharedPaginationParams;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamAttribute;
use ReflectionMethod;

class ExtractSharedPaginationParams extends GetFromQueryParamAttribute
{
    /**
     * @param array<string, mixed> $routeRules
     * @return array<string, array<string, mixed>>
     */
    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules = []): array
    {
        $method = $endpointData->method;
        $attributes = collect($method->getAttributes(SharedPaginationParams::class));

        if ($attributes->isEmpty()) {
            return [];
        }

        return [
            'per_page' => [
                'name' => 'per_page',
                'description' => 'The number of items to return per page.',
                'type' => 'integer',
                'required' => false,
                'example' => 20,
                'enumValues' => [],
                'nullable' => false,
            ],
            'all' => [
                'name' => 'all',
                'description' => 'Get all records without pagination.',
                'type' => 'boolean',
                'required' => false,
                'example' => false,
                'enumValues' => [],
                'nullable' => false,
            ],
        ];
    }
}