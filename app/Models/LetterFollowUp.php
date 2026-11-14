<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterFollowUp extends Model
{
    protected $fillable = [
        'letter_id',
        'user_id',
        'work_unit_id',
        'progress_status',
        'description',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class);
    }
}
