<?php

namespace App\Domains\Booking\Exceptions;

use Exception;

class BookingUnavailableException extends Exception
{
    protected $message = 'The requested stylist or time slot is unavailable.';
}
