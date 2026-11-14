<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'unit_id',
        'work_unit_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Instansi induk pengguna.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Unit kerja pengguna, misalnya PLP atau OJLP.
     */
    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }

    public function createdLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'created_by');
    }

    public function sentLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'sent_by');
    }

    public function receivedLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'received_by');
    }

    public function letterHistories(): HasMany
    {
        return $this->hasMany(LetterHistory::class, 'changed_by');
    }

    public function createdDispositions(): HasMany
    {
        return $this->hasMany(LetterDisposition::class, 'created_by');
    }

    public function assignedDispositions(): HasMany
    {
        return $this->hasMany(LetterDisposition::class, 'assigned_to_user_id');
    }

    public function letterFollowUps(): HasMany
    {
        return $this->hasMany(LetterFollowUp::class);
    }

    public function letterComments(): HasMany
    {
        return $this->hasMany(LetterComment::class);
    }
}
