<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://v6.exchangerate-api.com/v6/';

    public function __construct()
    {
        
        $this->apiKey = '567784e252262d023515676f';
    }

   
    public function getConversionRate(string $from, string $to): ?float
    {
        
        if ($from === $to) {
            return 1.0;
        }

        return Cache::remember("currency_rate_{$from}_{$to}", now()->addDay(), function () use ($from, $to) {
            
            
            if ($this->apiKey === 'YOUR_API_KEY_HERE') {
                report("CurrencyService используется без API ключа!");
                return null;
            }
            
            try {
                $response = Http::get($this->baseUrl . $this->apiKey . '/pair/' . $from . '/' . $to);
                
                if ($response->successful() && $response->json('result') === 'success') {
                    return (float) $response->json('conversion_rate');
                }

                return null;
            } catch (\Exception $e) {
                report($e);
                return null;
            }
        });
    }
}