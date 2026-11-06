<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DispositionTarget extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function dispositions(): BelongsToMany
    {
        return $this->belongsToMany(
            LetterDisposition::class,
            'disposition_target_letter_disposition'
        )->withTimestamps();
    }
}
