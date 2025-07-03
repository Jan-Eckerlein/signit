<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case COMPLETED = 'completed';
    case TEMPLATE = 'template';
} 