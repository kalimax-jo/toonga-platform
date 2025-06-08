<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class CurrencyHelper
{
    public static function convert($amount, $from = 'EUR', $to = 'RWF')
    {
        $response = Http::get('https://api.exchangerate.host/convert', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);

        if ($response->successful() && isset($response['result'])) {
            return $response['result'];
        }

        return null; // or throw exception if needed
    }
}
