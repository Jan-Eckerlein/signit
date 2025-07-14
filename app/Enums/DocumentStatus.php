<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case TEMPLATE = 'template';
    case DRAFT = 'draft';
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
} 