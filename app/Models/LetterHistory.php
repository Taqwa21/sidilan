<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterHistory extends Model
{
    protected $fillable = [
        'letter_id',
        'old_status',
        'new_status',
        'description',
        'changed_by',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}