<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    
    // Отключаем автоматические timestamps, если они не нужны для позиций
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'time_entry_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function timeEntry() { return $this->belongsTo(TimeEntry::class); }
}