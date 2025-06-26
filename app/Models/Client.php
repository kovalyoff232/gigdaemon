<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'default_rate',
        'default_currency', // Добавили сюда
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function projects() { return $this->hasMany(Project::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function timeEntries() { return $this->hasManyThrough(TimeEntry::class, Project::class); }
}