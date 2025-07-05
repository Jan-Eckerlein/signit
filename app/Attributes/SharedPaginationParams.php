<?php
namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SharedPaginationParams
{
    // This attribute is used to mark methods that should have shared pagination parameters
    // The actual parameter extraction is handled by ExtractSharedPaginationParams strategy
}