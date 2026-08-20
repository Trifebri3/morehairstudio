<?php

namespace App\Domains\Booking\Exceptions;

use Exception;

class DoubleBookingException extends Exception
{
    protected $message = 'Stylist is already booked for this time range.';
}
