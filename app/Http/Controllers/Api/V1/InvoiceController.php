<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Services\CurrencyService;
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

    private function clearDashboardCache(): void
    {
        if (auth()->check()) {
            Cache::forget("user:".auth()->id().":dashboard-summary");
        }
    }
    
    public function index(Request $request)
    {
        return $request->user()->invoices()->with('client')->latest('issue_date')->get();
    }
    
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return $invoice->load(['items', 'client', 'user']);
    }

    public function getUnbilledEntries(Client $client)
    {
        $this->authorize('view', $client);
        return $client->timeEntries()->with('project')->whereDoesntHave('invoiceItem')->orderBy('start_time', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'time_entry_ids' => 'required|array|min:1',
            'time_entry_ids.*' => 'required|integer|exists:time_entries,id',
            'base_rate' => 'required|numeric|min:0', 
            'currency' => 'required|string|size:3',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $client = Client::findOrFail($validated['client_id']);

            
            if ($client->user_id !== $user->id) {
                abort(Response::HTTP_FORBIDDEN, 'Это не ваш клиент.');
            }

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
                'currency' => $validated['currency'], 
            ]);

            $totalAmount = 0;
            $unitPriceInTargetCurrency = $validated['base_rate'] * $rate; 

            foreach ($timeEntries as $entry) {
                $quantity = $entry->duration / 3600;
                $subtotal = $quantity * $unitPriceInTargetCurrency;
                $invoice->items()->create([
                    'time_entry_id' => $entry->id,
                    'description' => $entry->description ?: 'Работа по проекту: ' . $entry->project->title,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceInTargetCurrency, 
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }

            $invoice->update(['total_amount' => $totalAmount]);
            $this->clearDashboardCache();
            return response()->json($invoice->load('items', 'client'), Response::HTTP_CREATED);
        });
    }

    public function downloadPDF(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items', 'client', 'user']);
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
	
	public function updateStatus(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(['draft', 'sent', 'paid', 'overdue'])],
        ]);
        $invoice->update(['status' => $validated['status']]);
        $this->clearDashboardCache();
        return response()->json($invoice);
    }
}