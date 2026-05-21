<?php

namespace App\Enum;

enum State: string
{
    case CREATED = 'crée';
    case OPEN = 'ouverte';
    case CLOSED = 'fermée';
    case ON_GOING = 'en cours';
    case PAST = 'passée';
    case CANCELED = 'annulée';
    case ARCHIVED = 'archivée';
}
