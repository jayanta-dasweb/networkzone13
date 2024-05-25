<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
