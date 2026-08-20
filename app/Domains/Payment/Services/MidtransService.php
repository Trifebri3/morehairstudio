<?php

namespace App\Domains\Payment\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public static function init()
    {
        Config::$serverKey = config('services.midtrans.server_key', 'SB-Mid-server-yourkeyhere');
        Config::$clientKey = config('services.midtrans.client_key', 'SB-Mid-client-yourkeyhere');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create snap transaction and return Snap Token and Redirect URL.
     */
    public static function createTransaction(array $params)
    {
        self::init();
        return Snap::createTransaction($params);
    }
}
