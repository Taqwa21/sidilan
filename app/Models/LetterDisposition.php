<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LetterDisposition extends Model
{
    protected $fillable = [
        'letter_id',
        'created_by',
        'assigned_to_unit_id',
        'assigned_to_user_id',
        'instruction',
        'target_units',
        'status',
        'disposed_at',
        'deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'disposed_at' => 'datetime',
            'deadline_at' => 'datetime',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'assigned_to_unit_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function targets(): BelongsToMany
    {
        return $this->belongsToMany(
            DispositionTarget::class,
            'disposition_target_letter_disposition'
        )->withTimestamps();
    }
}
