<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'start_time',
        'end_time',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Добавляем вычисляемое поле, чтобы фронтенду было удобнее
    protected $appends = ['duration'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Аксессор для вычисления продолжительности в секундах
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_time ? $this->end_time->diffInSeconds($this->start_time) : null,
        );
    }
}