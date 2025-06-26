<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    

    public function showInvoice(Invoice $invoice)
    {

        return view('invoices.show', ['invoiceId' => $invoice->id]);
    }
}