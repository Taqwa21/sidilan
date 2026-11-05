<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'classification_id',
        'sent_by',
        'sent_at',
        'received_by',
        'received_at',
        'received_note',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
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

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'classification_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LetterHistory::class)->latest();
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(LetterDisposition::class)->latest();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LetterFollowUp::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LetterComment::class)->latest();
    }
}
