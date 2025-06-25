<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// === ВОТ ОНО. КАРТА К СОКРОВИЩНИЦЕ ===
use App\Models\Invoice;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the invoices page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function invoices()
    {
        return view('invoices.index');
    }
    
    /**
     * Показать страницу одного счета.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showInvoice(Invoice $invoice)
    {
        // Теперь, благодаря "use App\Models\Invoice;" вверху,
        // Laravel точно знает, где искать модель Invoice.
        return view('invoices.show', ['invoiceId' => $invoice->id]);
    }
}