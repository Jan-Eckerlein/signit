<?php

namespace App\Enums;

enum DocumentFieldType: string
{
    case SIGNATURE = 'signature';
    case INITIALS = 'initials';
    case TEXT = 'text';
    case CHECKBOX = 'checkbox';
    case DATE = 'date';
} 