<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $baseCurrency = 'RUB'; // Наша основная валюта
        $cacheKey = "user:{$user->id}:dashboard-summary-v3"; // Версия 3, чтобы гарантированно обойти старый кэш

        $summaryData = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $baseCurrency) {
            
            // 1. Считаем НЕОПЛАЧЕННУЮ сумму
            $unpaidInvoices = Invoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'overdue', 'draft'])
                ->get();

            $totalUnpaid = $unpaidInvoices->reduce(function ($carry, $invoice) use ($baseCurrency) {
                // --- ПРОТИВОЯДИЕ ---
                // Получаем курс
                $rate = $this->currencyService->getConversionRate($invoice->currency, $baseCurrency);
                // ЕСЛИ КУРС НЕ ПОЛУЧЕН (null), МЫ ПРИНУДИТЕЛЬНО СЧИТАЕМ ЕГО РАВНЫМ 1.
                // БОЛЬШЕ НИКАКИХ НУЛЕЙ.
                if (empty($rate)) {
                    $rate = 1.0;
                }
                return $carry + ($invoice->total_amount * $rate);
            }, 0);

            // 2. Считаем ДОХОД ЗА МЕСЯЦ с тем же противоядием
            $paidInvoicesThisMonth = Invoice::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->get();
            
            $incomeThisMonth = $paidInvoicesThisMonth->reduce(function ($carry, $invoice) use ($baseCurrency) {
                $rate = $this->currencyService->getConversionRate($invoice->currency, $baseCurrency);
                if (empty($rate)) {
                    $rate = 1.0;
                }
                return $carry + ($invoice->total_amount * $rate);
            }, 0);

            // 3. Остальная логика без изменений
            $unbilledSeconds = TimeEntry::where('user_id', $user->id)
                ->whereDoesntHave('invoiceItem')
                ->get()
                ->sum('duration');

            $activeTimer = TimeEntry::where('user_id', $user->id)
                ->whereNull('end_time')
                ->with('project.client')
                ->first();

            return [
                'totalUnpaid' => (float) $totalUnpaid,
                'incomeThisMonth' => (float) $incomeThisMonth,
                'unbilledHours' => round($unbilledSeconds / 3600, 2),
                'activeTimer' => $activeTimer,
            ];
        });

        return response()->json($summaryData);
    }
}