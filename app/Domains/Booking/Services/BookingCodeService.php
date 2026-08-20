<?php

namespace App\Domains\Booking\Services;

use Illuminate\Support\Str;

class BookingCodeService
{
    /**
     * Generate booking code format: MOR-[yymmdd]-[RANDOM5]
     */
    public static function generateCode(string $dateString): string
    {
        $datePart = date('ymd', strtotime($dateString));
        // Ensure only alphanumeric characters for the random code
        $randomPart = strtoupper(Str::random(5));
        // Replace non-alphanumeric just in case
        $randomPart = preg_replace('/[^A-Z0-9]/', 'X', $randomPart);
        
        return "MOR-{$datePart}-{$randomPart}";
    }

    /**
     * Generate a unique secure token for URL lookups and QR scans.
     */
    public static function generateToken(): string
    {
        return Str::random(32);
    }
}
