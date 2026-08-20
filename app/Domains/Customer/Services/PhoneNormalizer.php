<?php

namespace App\Domains\Customer\Services;

class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        // Remove non-numeric characters
        $digits = preg_replace('/\D/', '', $phone);

        // If it starts with 0, replace with 62
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        // If it starts with 62, return as is
        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        // If it starts with 8 (local Indonesian shorthand e.g. 812...), prepend 62
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}
