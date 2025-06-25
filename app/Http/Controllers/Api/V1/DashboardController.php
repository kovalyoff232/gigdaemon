<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();

        $totalUnpaid = Invoice::where('user_id', $user->id)->whereIn('status', ['sent', 'overdue', 'draft'])->sum('total_amount');
        $incomeThisMonth = Invoice::where('user_id', $user->id)->where('status', 'paid')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('total_amount');
        
        // === ИЗМЕНЕНИЕ ЗДЕСЬ ===
        // Сначала получаем все записи, а ПОТОМ считаем сумму их виртуальных полей 'duration'
        $unbilledSeconds = TimeEntry::where('user_id', $user->id)->whereDoesntHave('invoiceItem')->get()->sum('duration');

        $activeTimer = TimeEntry::where('user_id', $user->id)->whereNull('end_time')->with('project.client')->first();

        return response()->json([
            'totalUnpaid' => (float) $totalUnpaid,
            'incomeThisMonth' => (float) $incomeThisMonth,
            'unbilledHours' => round($unbilledSeconds / 3600, 2),
            'activeTimer' => $activeTimer,
        ]);
    }
}