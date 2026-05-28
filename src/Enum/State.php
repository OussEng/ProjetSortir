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


    public function color(): string
    {
        return match($this) {
            State::CREATED  => 'secondary',
            State::OPEN     => 'success',
            State::CLOSED   => 'danger',
            State::ON_GOING => 'warning',
            State::PAST     => 'dark',
            State::CANCELED => 'danger',
            State::ARCHIVED => 'secondary',
        };
    }

}


