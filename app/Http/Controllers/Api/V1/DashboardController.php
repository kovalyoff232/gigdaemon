<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Импортируем магию кэша

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $cacheKey = "user:{$user->id}:dashboard-summary"; // Уникальный ключ для кэша этого пользователя

        // Пытаемся взять данные из кэша. Если их нет, выполняем функцию и сохраняем результат на 10 минут.
        $summaryData = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            
            $totalUnpaid = Invoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'overdue', 'draft'])
                ->sum('total_amount');
            
            $incomeThisMonth = Invoice::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('total_amount');
            
            $unbilledSeconds = TimeEntry::where('user_id', $user->id)
                ->whereDoesntHave('invoiceItem')
                ->get()
                ->sum('duration');

            $activeTimer = TimeEntry::where('user_id', $user->id)
                ->whereNull('end_time')
                ->with('project.client')
                ->first();

            // Эта часть выполнится только если данных в кэше нет
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