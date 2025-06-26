<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    // Мы будем использовать бесплатный API от ExchangeRate-API
    protected string $apiKey;
    protected string $baseUrl = 'https://v6.exchangerate-api.com/v6/';

    public function __construct()
    {
        // В идеале, ключ надо хранить в .env, но для простоты пока так
        $this->apiKey = '567784e252262d023515676f'; // <-- ПОЛУЧИ СВОЙ КЛЮЧ НА exchangerate-api.com
    }

    /**
     * Получить курс одной валюты к другой
     * @param string $from
     * @param string $to
     * @return float|null
     */
    public function getConversionRate(string $from, string $to): ?float
    {
        // Кэшируем курсы на 24 часа, чтобы не дергать API постоянно
        return Cache::remember("currency_rate_{$from}_{$to}", now()->addDay(), function () use ($from, $to) {
            
            // Если у тебя нет ключа, просто вернем 1, чтобы не было ошибки
            if ($this->apiKey === '567784e252262d023515676f') {
                return 1.0;
            }
            
            try {
                $response = Http::get($this->baseUrl . $this->apiKey . '/pair/' . $from . '/' . $to);
                
                if ($response->successful() && $response->json('result') === 'success') {
                    return (float) $response->json('conversion_rate');
                }

                return null;
            } catch (\Exception $e) {
                // Логируем ошибку, если что-то пошло не так
                report($e);
                return null;
            }
        });
    }
}