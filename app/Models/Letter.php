<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Letter extends Model
{
    protected $fillable = [
        'letter_number',
        'letter_date',
        'origin',
        'subject',
        'description',
        'status',
        'created_by',
        'sender_unit_id',
        'receiver_unit_id',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function senderUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sender_unit_id');
    }

    public function receiverUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'receiver_unit_id');
    }
    public function histories(): HasMany
    {
        return $this->hasMany(LetterHistory::class)->latest();
    }
}