<?php

namespace App\Enums;

enum DeletionStatus: string
{
    case SOFT_DELETED = 'soft_deleted';
    case PERMANENTLY_DELETED = 'permanently_deleted';
	case NOOP = 'noop';
} 