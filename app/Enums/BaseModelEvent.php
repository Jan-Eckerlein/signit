<?php

namespace App\Enums;

enum BaseModelEvent: string
{
    case RETRIEVED = 'retrieved';
    case CREATING = 'creating';
    case CREATED = 'created';
    case UPDATING = 'updating';
    case UPDATED = 'updated';
    case SAVING = 'saving';
    case SAVED = 'saved';
    case RESTORING = 'restoring';
    case RESTORED = 'restored';
    case REPLICATING = 'replicating';
    case TRASHED = 'trashed';
    case DELETING = 'deleting';
    case DELETED = 'deleted';
    case FORCE_DELETING = 'forceDeleting';
    case FORCE_DELETED = 'forceDeleted';
} 