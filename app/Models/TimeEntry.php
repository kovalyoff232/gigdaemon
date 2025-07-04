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
        'client_id',
        'user_id',
        'start_time',
        'end_time',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    protected $appends = ['duration'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
	
	public function invoiceItem()
	{

		return $this->hasOne(InvoiceItem::class);
	}
    
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->end_time) {
                    return 0; 
                }
                

                return $this->end_time->timestamp - $this->start_time->timestamp;
            },
        );
    }
}