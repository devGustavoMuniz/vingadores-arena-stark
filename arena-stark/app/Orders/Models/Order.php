<?php

namespace App\Orders\Models;

use App\Inventory\Models\Event;
use App\Inventory\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_id',
        'status',
        'amount',
        'reservation_token',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Possible statuses: pending, confirmed, failed, cancelled
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
