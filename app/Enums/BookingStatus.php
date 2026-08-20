<?php

namespace App\Enums;

enum BookingStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
    case EXPIRED = 'expired';
}
