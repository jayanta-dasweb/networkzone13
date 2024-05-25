<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_date',
        'event_time',
        'venue',
        'number_of_seats',
        'ticket_price'
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function getEventDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getEventTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getFormattedEventDateAttribute()
    {
        return Carbon::parse($this->attributes['event_date'])->format('d-m-Y');
    }

    public function getFormattedEventTimeAttribute()
    {
        return Carbon::parse($this->attributes['event_time'])->format('h:i A');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
