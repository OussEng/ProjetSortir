<?php

namespace App\Enum;

enum State: string
{
    case CREATED = 'created';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case ON_GOING = 'on going';
    case PAST = 'past';
    case CANCELED = 'canceled';
    case ARCHIVED = 'archived';
}
