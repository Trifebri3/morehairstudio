<?php

namespace App\Enums;

enum BookingSource: string
{
    case WEBSITE = 'website';
    case WALK_IN = 'walk_in';
    case QR = 'qr';
    case ADMIN = 'admin';
}
