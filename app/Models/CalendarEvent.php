<?php

namespace App\Models;

use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    /** @use HasFactory<CalendarEventFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'title', 'starts_at', 'duration_minutes', 'location', 'category', 'description'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'duration_minutes' => 'integer',
        ];
    }
}
