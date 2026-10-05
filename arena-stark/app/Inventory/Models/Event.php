<?php

namespace App\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'name',
        'description',
        'venue',
        'starts_at',
        'total_tickets',
        'available_tickets',
        'price',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'price' => 'decimal:2',
        'total_tickets' => 'integer',
        'available_tickets' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->available_tickets > 0;
    }
}
