<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Services\CurrencyService; // Наше новое оружие
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }
    
    // ... методы index, show, getUnbilledEntries, downloadPDF, updateStatus без изменений ...
    // НО МЫ ПЕРЕПИШЕМ STORE
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'time_entry_ids' => 'required|array|min:1',
            'time_entry_ids.*' => 'required|integer|exists:time_entries,id',
            'base_rate' => 'required|numeric|min:0', // Это ставка в ВАШЕЙ основной валюте (RUB)
            'currency' => 'required|string|size:3', // Валюта, в которой будет счет (USD, EUR...)
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $client = Client::findOrFail($validated['client_id']);

            // Проверяем, что пользователь владеет клиентом
            if ($client->user_id !== $user->id) {
                abort(Response::HTTP_FORBIDDEN, 'Это не ваш клиент.');
            }

            // Получаем курс конвертации. Наша основная валюта - RUB.
            $rate = $this->currencyService->getConversionRate('RUB', $validated['currency']);
            if ($rate === null) {
                throw ValidationException::withMessages(['currency' => 'Не удалось получить курс для выбранной валюты.']);
            }

            $timeEntries = TimeEntry::whereIn('id', $validated['time_entry_ids'])->where('user_id', $user->id)->where('client_id', $validated['client_id'])->whereDoesntHave('invoiceItem')->get();
            if ($timeEntries->count() !== count($validated['time_entry_ids'])) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Одна или несколько записей времени неверны или уже оплачены.');
            }

            $invoice = $user->invoices()->create([
                'client_id' => $validated['client_id'],
                'invoice_number' => 'INV-' . (Invoice::max('id') + 1),
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
                'currency' => $validated['currency'], // Сохраняем валюту счета
            ]);

            $totalAmount = 0;
            $unitPriceInTargetCurrency = $validated['base_rate'] * $rate; // Цена за час в валюте счета

            foreach ($timeEntries as $entry) {
                $quantity = $entry->duration / 3600;
                $subtotal = $quantity * $unitPriceInTargetCurrency;
                $invoice->items()->create([
                    'time_entry_id' => $entry->id,
                    'description' => $entry->description ?: 'Работа по проекту: ' . $entry->project->title,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceInTargetCurrency, // Сохраняем цену в валюте счета
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }

            $invoice->update(['total_amount' => $totalAmount]);
            $this->clearDashboardCache();
            return response()->json($invoice->load('items', 'client'), Response::HTTP_CREATED);
        });
    }
    
    // ... остальные методы ...
    private function clearDashboardCache(): void { /* ... */ }
}