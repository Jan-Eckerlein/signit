<?php

namespace App\Enums;

enum QueueEnum: string
{
    case PDF_PROCESSING = 'pdf_processing';
    case IMAGE_PROCESSING = 'image_processing';
	case DEFAULT = 'default';
	case EMAIL = 'email';
}