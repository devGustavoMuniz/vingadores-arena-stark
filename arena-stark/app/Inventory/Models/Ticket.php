<?php

namespace App\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'event_id',
        'code',
        'status',
        'reserved_until',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved'
            && $this->reserved_until !== null
            && $this->reserved_until->isFuture();
    }
}
