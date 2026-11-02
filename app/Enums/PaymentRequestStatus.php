<?php

namespace App\Enums;

enum PaymentRequestStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
